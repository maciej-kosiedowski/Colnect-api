#!/usr/bin/env bash
#
# publish.sh — publish an allowlisted snapshot of this (private) repository to a
# public downstream repository as a single, squashed "release" commit authored
# by the maintainer.
#
# Why this design:
#   * AI-agent history (Claude, Jules, Cursor, …) never leaves the private repo,
#     because the public repo receives a *file snapshot*, not the source git
#     history. Each run produces exactly one commit, authored as the maintainer.
#   * Only paths in the allowlist ("wishlist") are published (default-deny).
#   * The public repo's own README is preserved — it is never overwritten.
#
# All configuration is supplied via environment variables (see below), which is
# the GitHub-recommended way to avoid shell-injection from workflow inputs.
#
set -euo pipefail

# --- configuration (all overridable via environment) ------------------------
SOURCE_DIR="${SOURCE_DIR:-$(pwd)}"
TARGET_REPO="${TARGET_REPO:?TARGET_REPO (owner/name) is required}"
TARGET_BRANCH="${TARGET_BRANCH:-master}"
ALLOWLIST_FILE="${ALLOWLIST_FILE:-.github/release/allowlist.txt}"
RELEASE_VERSION="${RELEASE_VERSION:-}"
COMMIT_MESSAGE="${COMMIT_MESSAGE:-release}"
GIT_AUTHOR_NAME="${GIT_AUTHOR_NAME:-Maciej Kosiedowski}"
GIT_AUTHOR_EMAIL="${GIT_AUTHOR_EMAIL:-maciej.kosiedowski@slimad.pl}"
PRESERVE_FILES="${PRESERVE_FILES:-README.md}"   # space-separated, kept as-is on target
DRY_RUN="${DRY_RUN:-0}"                          # 1 = build the commit but do not push
TARGET_CLONE_URL="${TARGET_CLONE_URL:-}"         # override (e.g. file:// for local tests)

log()  { printf '%s\n' "$*"; }
err()  { printf '::error::%s\n' "$*" >&2; }
warn() { printf '::warning::%s\n' "$*" >&2; }

# --- validation -------------------------------------------------------------
if [[ ! "$TARGET_REPO" =~ ^[A-Za-z0-9._-]+/[A-Za-z0-9._-]+$ ]]; then
  err "TARGET_REPO '$TARGET_REPO' is not a valid 'owner/name' value."
  exit 1
fi

SOURCE_DIR="$(cd "$SOURCE_DIR" && pwd)"
ALLOWLIST_PATH="$SOURCE_DIR/$ALLOWLIST_FILE"
if [[ ! -f "$ALLOWLIST_PATH" ]]; then
  err "Allowlist file not found: $ALLOWLIST_PATH"
  exit 1
fi

# Resolve how we reach the target repository.
if [[ -n "$TARGET_CLONE_URL" ]]; then
  clone_url="$TARGET_CLONE_URL"
else
  : "${UPSTREAM_TOKEN:?UPSTREAM_TOKEN (PAT with contents:write on the target repo) is required}"
  # The token is used only here and is never echoed; GitHub Actions masks it.
  clone_url="https://x-access-token:${UPSTREAM_TOKEN}@github.com/${TARGET_REPO}.git"
fi

# --- clone the target repo --------------------------------------------------
WORKDIR="$(mktemp -d)"
trap 'rm -rf "$WORKDIR"' EXIT
TARGET_DIR="$WORKDIR/target"

log "Cloning target ${TARGET_REPO} (branch ${TARGET_BRANCH})…"
git clone --quiet "$clone_url" "$TARGET_DIR"

if git -C "$TARGET_DIR" show-ref --verify --quiet "refs/remotes/origin/${TARGET_BRANCH}"; then
  git -C "$TARGET_DIR" checkout --quiet "$TARGET_BRANCH"
else
  warn "Branch '${TARGET_BRANCH}' does not exist on target; creating it."
  git -C "$TARGET_DIR" checkout --quiet -b "$TARGET_BRANCH"
fi

git -C "$TARGET_DIR" config user.name  "$GIT_AUTHOR_NAME"
git -C "$TARGET_DIR" config user.email "$GIT_AUTHOR_EMAIL"

# --- wipe the managed tree, preserving explicitly kept files ----------------
declare -A keep=()
for f in $PRESERVE_FILES; do keep["$f"]=1; done

while IFS= read -r -d '' tracked; do
  if [[ -z "${keep[$tracked]:-}" ]]; then
    rm -f "$TARGET_DIR/$tracked"
  else
    log "Preserving public file: $tracked"
  fi
done < <(git -C "$TARGET_DIR" ls-files -z)

# --- copy allowlisted paths from the source snapshot ------------------------
log "Applying allowlist:"
copied=0
while IFS= read -r line || [[ -n "$line" ]]; do
  path="${line%%#*}"                              # strip trailing comment
  path="${path#"${path%%[![:space:]]*}"}"         # ltrim
  path="${path%"${path##*[![:space:]]}"}"         # rtrim
  [[ -z "$path" ]] && continue

  if [[ "$path" = /* || "$path" == *".."* ]]; then
    err "Refusing unsafe allowlist entry: $path"
    exit 1
  fi
  if [[ ! -e "$SOURCE_DIR/$path" ]]; then
    warn "Allowlist entry not found in source, skipping: $path"
    continue
  fi

  ( cd "$SOURCE_DIR" && cp -a --parents "$path" "$TARGET_DIR/" )
  log "  + $path"
  copied=$((copied + 1))
done < "$ALLOWLIST_PATH"

if [[ "$copied" -eq 0 ]]; then
  err "Allowlist produced no files to publish."
  exit 1
fi

# --- stage, squash into a single commit -------------------------------------
git -C "$TARGET_DIR" add -A
if git -C "$TARGET_DIR" diff --cached --quiet; then
  log "No changes to publish; ${TARGET_REPO}@${TARGET_BRANCH} is already up to date."
  exit 0
fi

commit_args=(-m "$COMMIT_MESSAGE")
if [[ -n "$RELEASE_VERSION" ]]; then
  commit_args+=(-m "Snapshot published from the source repository. Version: ${RELEASE_VERSION}.")
fi
git -C "$TARGET_DIR" commit --quiet "${commit_args[@]}"
log "Prepared 'release' commit as ${GIT_AUTHOR_NAME} <${GIT_AUTHOR_EMAIL}>:"
git -C "$TARGET_DIR" --no-pager log -1 --format='  %H%n  %an <%ae>%n  %s'

# --- push -------------------------------------------------------------------
if [[ "$DRY_RUN" == "1" ]]; then
  log "DRY_RUN=1 → not pushing. Snapshot prepared locally at: $TARGET_DIR"
  exit 0
fi

git -C "$TARGET_DIR" push origin "$TARGET_BRANCH"
log "Published to ${TARGET_REPO}@${TARGET_BRANCH}."

# --- optional version tag (Packagist creates a release from each tag) -------
if [[ -n "$RELEASE_VERSION" ]]; then
  tag="$RELEASE_VERSION"
  [[ "$tag" =~ ^v ]] || tag="v$tag"
  if git -C "$TARGET_DIR" ls-remote --exit-code --tags origin "refs/tags/${tag}" >/dev/null 2>&1; then
    err "Tag ${tag} already exists on the target; refusing to overwrite a released version."
    exit 1
  fi
  git -C "$TARGET_DIR" tag -a "$tag" -m "release ${tag}"
  git -C "$TARGET_DIR" push origin "$tag"
  log "Pushed tag ${tag}."
fi
