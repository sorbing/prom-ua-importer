## Prom.ua importer

**Laravel 5.5 package for import (or parse/fetch) products and orders from https://prom.ua store place.**

This package provides the migrations, services and commands for parse, fetch and optional store prom.ua data.

Data source is a:

- **Products**: Yml (Yandex Market Language) URL or exported CSV local file.
- **Orders**: Xml URL (contains a latest ~130 orders) or exported XLS local file.

### Requirements

This package requires a following packages for parse Yml and Xls sources:

    "sirian/yandex-market-language-parser": "~3.0.2",
    "maatwebsite/excel": "3.0.x-dev"

### Install

Just run:

    composer require sorbing/prom-ua-importer

### Setup

1. Specify the sources URLs in your `.env` config:

        PROM_UA_PRODUCTS_YML_URL=
        PROM_UA_ORDERS_XML_URL= 

    **Or** publish `prom_ua.php` config file and configure him:
    
        vendor:publish --provider="Sorbing\PromUaImporter\PromUaServiceProvider" --tag="config"
    
    It's a copied file `vendor/sorbing/prom-ua-importer/src/config/prom_ua.php` to `config/prom_ua.php`.
    Then configure params in a `config/prom_ua.php`.

### Usage

#### Import Products

1. Generate the CSV export products file, download CSV from email and unzip.
    **Note:** *CSV file contains additional fields as `quantity`, `unit`, `discount`, `prices`, `pre_order_days`, `tags`, `category_url`..*
2. Run import all products from Yml and CSV:
    ```
    php artisan prom-ua-import:products --csv=storage/data/prom_ua/export-products-31-01-18_14-25-53.csv
    ```
    
#### Import Orders

    php artisan prom-ua-import:orders --xls=storage/data/prom_ua/export-orders-31-01-18_16-04-00.xls


