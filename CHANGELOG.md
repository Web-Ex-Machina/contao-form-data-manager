Bundle for manage the form data
======================

v2.0.0 - Unreleased
---
**Breaking release: requires PHP 8.4 and Contao 5.7.**

- Move to the Contao 5 bundle structure (`config/`, `contao/`, `public/`, `src/`) and an `AbstractBundle`
- Register DCA callbacks with `#[AsCallback]` and hooks with `#[AsHook]` instead of service ids in DCA/config files
- Replace the `key=export` / `key=export_all` back end module callbacks with a routed `ExportController` (`/contao/form-data-manager/export/...`) checking module permissions
- Replace the Notification Center 1 `sendNotificationMessage` hook with a Notification Center 2 listener (`##useful_data##`, `##useful_data_text##`, `##useful_data_filled##`, `##useful_data_filled_text##`); field names containing underscores are now resolved correctly
- Add the `FormStorageStatus` enum (replaces the `FormStorage::STATUS_*` constants)
- `tl_form.storeViaFormDataManager` is now a boolean column (a migration converts the existing values)
- Fix the error messages of the form configuration checks and of the uploaded files, which were read from non-existing language keys
- Fix the referer page detection and the completion percentage of upload fields
- Only load the front end script on forms managed by the Form Data Manager
- Remove `FormUtil::getPageFromForm()`, the `Form` model, the `Backend\BackendController` and the `Dca\Field\Callback\*Conditionnal` classes
- Add ECS, Rector and PHPStan configurations

v1.0.4 - 2026-04-07
---
- Adjust value field length to allow larger entries

v1.0.3 - 2025-12-03
---
- Adjust vars to avoid warnings in `SendNotificationMessageListener`

v1.0.2 - 2025-03-14
---
- Fixes in `SendNotificationMessageListener`

v1.0.1 - 2025-03-14
---
- Fixes here and there
