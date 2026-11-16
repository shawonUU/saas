# Independent Configuration CRUD (12 modules)

- Each configuration has its own `app/Models/Configurations/<Model>.php`
- Each has its own `app/Http/Controllers/Public/Business/Admin/Configurations/<Controller>.php`
- Each has its own migration `database/migrations/2026_11_17_01*.php`
- Each has its own routes under `routes/Public/Business/Admin/configurations/*.php`
- Each has its own full Blade index + modal under `resources/views/public/business/admin/configurations/<slug>/index.blade.php`
- Shared BaseConfiguration and BaseConfigurationController hold only repeated access, filtering and validation mechanics.
- `_workspace_styles`, `_ui_styles`, `_ui_scripts` are shared presentation assets, **not** a shared CRUD page.
- Modal is appended to document.body to escape sidebar/header stacking context, with opaque white dialog and dark blur backdrop.
- Search, per-column filters, sorting, pagination execute in the database. Tenant isolation and permissions enforced server-side.
- Existing `business_customers` auth/login table is not modified; independent `config_customers` stores master customers.
- Existing `2026_11_17_000100_create_business_configuration_masters.php` is retained as an inert legacy marker, preserving migration history; 12 independent migrations adopt existing config tables without dropping data.
- Migration rollback deliberately does not auto-drop adopted tables. Use a separately reviewed data deletion migration if ever needed.
- Old dynamic ConfigurationCrudController, ConfigurationRecord, ConfigurationResources and generic index Blade have been archived by installer (if pristine) and retired.

## Install (from Laravel root, after database backup)
```
php install_separate_configurations.php --check
php install_separate_configurations.php --apply
php artisan migrate
php artisan optimize:clear
php artisan route:list --name=store.admin.configurations
php artisan test --filter=ConfigurationMastersTest
```
