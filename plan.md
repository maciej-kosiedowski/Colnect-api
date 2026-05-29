1. **Update `composer.json`**
   - Change `"ext-pcov": "*"` to `"ext-pcov": "^1.0"`.

2. **Update `infection.json.dist`**
   - Add `"excludes": ["Enums"]` to the `source` configuration.
   - Add `"minMsi": 100` and `"minCoveredMsi": 100`.

3. **Update `ColnectConnector.php`**
   - Fix `parse_url` usage to avoid type casting errors and escape mutants by explicitly checking `is_string($path)` and throwing an exception or setting default if false.

4. **Add Missing Tests for Mutant Kills**
   - In `ColnectConnectorTest.php`, add `test_it_rejects_app_id_with_invalid_suffix`, `test_it_rejects_language_with_invalid_prefix`, and `test_it_rejects_language_with_invalid_suffix`.
   - In `GetCountriesRequestTest.php`, add `test_it_rejects_string_category_with_invalid_suffix`.
   - In `GetItemsListRequestTest.php`, add `test_it_rejects_string_category_with_invalid_suffix`.
   - In `GlobalSearchRequestTest.php`, add `test_it_rejects_invalid_collectable_type_suffix` and `test_it_accepts_page_1`.

5. **Ensure PHPStan Level 9 Passes**
   - Ensure explicit type checking handles `parse_url` properly.

6. **Complete pre commit steps**
   - Complete pre commit steps to make sure proper testing, verifications, reviews and reflections are done.

7. **Submit the changes**
   - Submit code via tool.
