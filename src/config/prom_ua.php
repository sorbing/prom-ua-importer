<?php

return [
    'products_yml_url' => env('PROM_UA_PRODUCTS_YML_URL', ''),
    'orders_xml_url' => env('PROM_UA_ORDERS_XML_URL', ''),
    'data_dir' => env('PROM_UA_DATA_DIR', storage_path('data/prom_ua'))
];