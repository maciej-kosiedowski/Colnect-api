# Release / publish pipeline

This **private** repository is the source of truth and is never published as-is.
Releases are pushed to a separate **public** repository
([`maciej-kosiedowski/Colnect-API-Client`](https://github.com/maciej-kosiedowski/Colnect-API-Client)),
which is what gets published on [Packagist](https://packagist.org).

The publish step is the [`Release (publish to public repo)`](../workflows/release.yml)
workflow, driven by [`publish.sh`](./publish.sh) and the
[`allowlist.txt`](./allowlist.txt) "wishlist".

## What it guarantees

| Requirement | How it is met |
| --- | --- |
| No AI-agent history in public | The public repo receives a **file snapshot**, not the source git history. Each release is a single commit. Claude/Jules/Cursor commits never leave this private repo. |
| History rewritten as the maintainer | Every published commit is authored **and** committed as `Maciej Kosiedowski <maciej.kosiedowski@slimad.pl>`. |
| Squashed commits | One squashed commit per run, regardless of how many private commits happened. |
| Commit message is always `release` | The commit subject is `release` (the version, if given, goes in the body). |
| The public repo keeps its own README | `README.md` is in `PRESERVE_FILES` and is **not** in the allowlist, so it is never overwritten. |
| Allowlisted files only | Default-deny: only paths in `allowlist.txt` are published. |
| Security / Packagist compliance | `composer validate --strict` runs before publishing; only allowlisted files leave the repo; the workflow uses least-privilege `permissions` and passes inputs via env to avoid injection. |

## One-time setup

1. **Create a token** with write access to the public repo. A
   [fine-grained PAT](https://github.com/settings/tokens?type=beta) is recommended:
   - **Repository access:** only `maciej-kosiedowski/Colnect-API-Client`
   - **Permissions:** `Contents` → **Read and write**
2. **Add it as a secret** on *this* (private) repo:
   `Settings → Secrets and variables → Actions → New repository secret`
   - **Name:** `UPSTREAM_TOKEN`
   - **Value:** the token from step 1
3. Make sure the target branch (`master`) allows the token to push directly
   (no branch-protection rule blocking it).

## Releasing

`Actions → Release (publish to public repo) → Run workflow`, then set:

- **target_repo** — `maciej-kosiedowski/Colnect-API-Client` (default)
- **target_branch** — `master` (default)
- **version** — optional, e.g. `1.0.0`. When provided, an annotated tag
  (`v1.0.0`) is pushed to the target — this is what Packagist turns into a
  release version.

## Editing the "wishlist"

Add or remove paths in [`allowlist.txt`](./allowlist.txt). Anything not listed
stays private. `README.md`, `composer.lock`, and the private CI/publishing files
are intentionally excluded.

## Previewing locally (no push)

```bash
DRY_RUN=1 \
TARGET_REPO=maciej-kosiedowski/Colnect-API-Client \
TARGET_CLONE_URL="https://github.com/maciej-kosiedowski/Colnect-API-Client.git" \
bash .github/release/publish.sh
```

`DRY_RUN=1` builds the squashed commit in a temp clone and prints it, but does
not push. `TARGET_CLONE_URL` lets you point at any clone source (handy for tests).

## Publishing on Packagist (when you are ready)

Packagist is intentionally **not** wired in yet. Two ways to enable it later:

1. **Webhook (recommended):** submit the public repo once at
   <https://packagist.org/packages/submit>; Packagist auto-updates on each push/tag.
2. **Ping from CI:** add a step to `release.yml` (after the publish step) and the
   `PACKAGIST_USERNAME` / `PACKAGIST_TOKEN` secrets:

   ```yaml
   - name: Notify Packagist
     if: ${{ secrets.PACKAGIST_TOKEN != '' }}
     run: |
       curl -sSf -XPOST -H'content-type:application/json' \
         "https://packagist.org/api/update-package?username=${{ secrets.PACKAGIST_USERNAME }}&apiToken=${{ secrets.PACKAGIST_TOKEN }}" \
         -d '{"repository":{"url":"https://github.com/${{ inputs.target_repo }}"}}'
   ```

The package name on Packagist is `slimad/colnect-api` (see `composer.json`).
