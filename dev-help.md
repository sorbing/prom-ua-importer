
    php artisan make:migration --create="products" --path=packages/sorbing/prom-ua-importer/src/database/migrations create_products_prom_table
    php artisan ide-helper:models --write "\Sorbing\PromUaImporter\Models\ProductProm"