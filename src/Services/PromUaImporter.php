<?php

namespace Sorbing\PromUaImporter\Services;

use Sorbing\PromUaImporter\Collections\OrderPromCollection;
use Sorbing\PromUaImporter\Collections\ProductPromCollection;
use Sorbing\PromUaImporter\Models\OrderProm;
use Sorbing\PromUaImporter\Models\ProductProm;

class PromUaImporter
{
    protected $products_yml_url = '';
    protected $orders_xml_url = '';
    protected $products_csv_file = '';
    protected $orders_xls_file = '';
    protected $data_dir = '';
    protected $store_id = null;

    public function __construct()
    {
        $this->setProductsYmlUrl(config('prom_ua.products_yml_url'));
        $this->setOrdersXmlUrl(config('prom_ua.orders_xml_url'));
        $this->setDataDir(config('prom_ua.data_dir'));
    }

    public function setDataDir($path)
    {
        $this->data_dir = rtrim($path, DIRECTORY_SEPARATOR);

        return $this;
    }

    // @todo Убрать этот функционал
    public function setStoreId(int $store_id)
    {
        $this->store_id = trim($store_id);

        return $this;
    }

    public function setProductsYmlUrl($url)
    {
        // @todo Check a valid url
        $this->products_yml_url = $url;

        return $this;
    }

    public function setProductsCsvFile($path)
    {
        $this->products_csv_file = $path;

        return $this;
    }

    public function setOrdersXmlUrl($url)
    {
        $this->orders_xml_url = $url;

        return $this;
    }

    public function setOrdersXlsFile($path)
    {
        $this->orders_xls_file = $path;

        return $this;
    }

    public function importAll()
    {
        $this->importProductsFromCsv();
        $this->importProductsFromYml();
        $this->importOrdersFromXls();
        $this->importOrdersFromXml();
    }

    public function importOrdersFromXls()
    {
        $this->consoleInfo('* Orders importing from XLS started...');

        $orders = $this->getOrdersFromXls();

        foreach ($orders as $order) {
            $this->importOrder($order);
        }

        $this->consoleInfo(sprintf('%s orders were imported from XLS.', count($orders)));
    }

    public function getOrdersFromXls(): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($this->orders_xls_file);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $headers = array_map(function($item) {
            return str_slug($item, '_');
        }, array_shift($rows));

        array_walk($rows, function (&$row) use ($headers) {
            $row = array_combine($headers, $row);
        });

        $saneKeys = [
            'nomer' => 'code',
            'data' => 'date',
            'pib' => 'customer_name',
            'vid_kompanii' => 'seller_comment',
            'telefon' => 'customer_phone',
            'email' => 'customer_email',
            'adresa' => 'address',
            'sposib_dostavki' => 'delivery_type',
            'sposib_oplati' => 'payment_type',
            'status' => 'status',
            'prichina_skasuvannya' => 'cancellation_reason',
            'komentar_do_vidmini' => 'cancellation_comment',
            'artikul' => 'product_sku',
            'nazva_tovaru' => 'product_name',
            'kilkist' => 'product_qty',
            'dzherelo' => 'source',
            'mitki' => 'labels',
            //[17] => promokod
            //[18] => znizhka_po_promokodu
            'suma_uah' => 'cost',
            'tsina_uah' => 'product_price',
            'suma_zi_znizhkoyu_uah' => 'cost_with_discount',
            //[23] => suma_komisii_tovar
            //[24] => vidsotok_komisii_tovar
            //[25] => suma_komisii_zamovlennya
            //[26] => status_komisii_zamovlennya
            'nomer_deklaratsii' => 'ttn',
        ];

        $saneRows = array_map(function($row) use ($saneKeys) {
            $saneRow = [];
            foreach ($row as $key => $value) {
                $saneKey = array_get($saneKeys, $key, $key);
                $saneRow[$saneKey] = $value;
            }
            return $saneRow;
        }, $rows);

        unset($rows);

        $orders = [];
        foreach ($saneRows as $row) {
            $order = $row;

            $orderCode = $order['code'];
            $date = \Carbon\Carbon::createFromFormat('d.m.y H:i', $order['date'])->format('Y-m-d H:i:s');

            $order['date'] = $date;

            $item = [
                'code' => null,
                'sku'  => $order['product_sku'],
                'name'  => $order['product_name'],
                'qty'   => (int)$order['product_qty'],
                'price' => (float)$order['product_price'],
            ];

            unset($order['product_name'], $order['product_qty'], $order['product_price'], $order['product_sku']);

            if (empty($orders[$orderCode])) {
                $orders[$orderCode] = $order;
                $orders[$orderCode]['items'] = [$item];
            } else {
                $orders[$orderCode]['items'][] = $item;
            }
        }

        foreach ($orders as &$order) {
            $orderCode = $order['code'];

            foreach ($order['items'] as &$item) {
                $product = ProductProm::where('sku', '!=', '')->where(function($query) use ($item) {
                    $query->where('name', $item['name'])->orWhere('sku', $item['sku']);
                })->first();

                if (!$product) {
                    $this->consoleInfo('Broken: ' . $item['sku'] . str_pad('', 8-strlen($item['sku'])) . ' | ' . $item['name']);
                    continue;
                } else {
                    $item['code'] = $product->code;
                }
            }
        }

        return $orders;
    }

    public function importOrdersFromXml()
    {
        $this->consoleInfo('* Orders importing from XML started...');

        $orders = $this->getOrdersFromXml();

        foreach ($orders as $order) {
            $this->importOrder($order);
        }

        $this->consoleInfo(sprintf('%s orders were imported from XML.', count($orders)));
    }

    private function importOrder(array $order)
    {
        $orderCode = $order['code'];

        $items = $order['items'];
        unset($order['items']);

        $order['broken_items_json'] = null; // @note Reset a broken_items_json
        $order['store_id'] = $this->store_id;
        $orderModel = OrderProm::updateOrCreate(['code' => $orderCode], $order);

        foreach ($items as $item) {
            if (empty($item['code'])) {
                $orderModel->storeItemInJsonArray('broken_items_json', $item, 'sku');
                continue;
            }

            $pivotRow = [
                'order_code' => $orderCode,
                'product_code' => $item['code'],
                'qty' => (int)$item['qty'],
                'price' => (float)$item['price']
            ];

            \DB::table('orders_items_prom')
                ->updateOrInsert(['order_code' => $orderCode, 'product_code' => $item['code']], $pivotRow);
        }
    }

    public function getOrdersFromXml()
    {
        $xmlFile = $this->downloadOrdersXmlFile();

        /** @var \SimpleXMLElement[] $xmlOrders */
        $xmlOrders = new \SimpleXMLElement(file_get_contents($xmlFile));

        $orders = [];
        foreach ($xmlOrders as $xmlOrder) {
            $orderCode = (int)$xmlOrder->attributes()->id;

            $order = [
                'code' => $orderCode,
                'status' => (string)$xmlOrder->attributes()->state, // accepted, declined, closed
                'date'  => date('Y-m-d H:i:s', strtotime((string)$xmlOrder->date)),
                'cost'  => (float)$xmlOrder->priceUAH,
                'payment_type' => (string)$xmlOrder->paymentType,  // Карта Приватбанка | Наложенный платеж
                'source' => (string)$xmlOrder->source, // Сайт компании | Bigl.ua
                'seller_comment' => (string)$xmlOrder->salescomment,
                'buyer_comment'  => (string)$xmlOrder->payercomment,
                'labels' => (string)$xmlOrder->labels,
                'cancellation_reason' => (string)$xmlOrder->cancellationReason, // Оплата не поступила |
                'delivery_type' => (string)$xmlOrder->deliveryType, // Нова Пошта | Доставка "Укрпочта Экспресс"
                'ttn'  => (string)$xmlOrder->novaPoshtaTTN ?? (string)$xmlOrder->declarationId,
                'address' => (string)$xmlOrder->address,
                'postal_index' => (string)$xmlOrder->index,
                'customer_name' => (string)$xmlOrder->name,
                'customer_phone' => (string)$xmlOrder->phone,
                'customer_email' => (string)$xmlOrder->email,
            ];

            $items = [];
            foreach ($xmlOrder->items->item as $xmlProduct) {
                $productCode = (int)$xmlProduct->attributes()->id;
                $product = [
                    'code' => $productCode,
                    'name' => (string)$xmlProduct->name,
                    'qty' => (int)$xmlProduct->quantity,
                    'price' => (int)$xmlProduct->price,
                    // sku, image, url, currency
                ];

                $items[$productCode] = $product;
            }

            $order['items'] = $items;

            $orders[$orderCode] = $order;
        }

        return $orders;
    }

    public function importProductsFromCsv()
    {
        if (!$this->products_csv_file || !file_exists($this->products_csv_file)) {
            $this->consoleInfo('* Skip import products from CSV (file not specified)');
            return;
        }

        $this->consoleInfo('* Products importing from CSV started...');
        $products = $this->getProductsFromCsv();

        foreach ($products as $code => $data) {
            $data['store_id'] = $this->store_id;
            ProductProm::where('code', $code)->update($data);
        }
        $this->consoleInfo(sprintf('%s products were imported from CSV.', count($products)));
    }

    // @todo Return structs[]
    public function getProductsFromCsv(): array
    {
        if (!file_exists($this->products_csv_file)) {
            throw new \Exception('Products CSV file not specified!');
        }

        $rows = $this->parseCsvRows($this->products_csv_file);


        $products = [];
        foreach ($rows as $row) {
            $struct = new \Sorbing\PromUaImporter\Structs\PromUaProductCsvStruct($row);

            $productCode = $struct->code;
            $price = $struct->price;

            $common = [
                'sku' => $struct->sku,
                'name' => $struct->name,
                'price' => $price,
                'group_name' => $struct->group_name,
                'description' => $struct->description,
                'description_length' => mb_strlen($struct->description),
                'currency' => rtrim($struct->currency, ', '),
                'url' => $struct->url,
                'images' => trim(str_replace(' ', '', $struct->images_list), ','),
            ];

            $additional = [
                'code' => $productCode,
                'quantity' => $struct->quantity,
                'pre_order_days' => 0,
                //'type' => $struct->type, // Тип_товара
                'unit' => $struct->unit,
                'discount' => strpos($struct->discount, '%') ? (float)(trim($struct->discount, '%') / 100 * $price) : (float)$struct->discount,
                'pack_type' => $struct->pack_type,
                'tags' => $struct->tags,
                'labels' => $struct->labels,
                'category_url' => $struct->group_url, // @todo Rename category_url
            ];

            // Pre Order
            $availabilityIdentity = (string)$struct->available;
            if ($additional['quantity'] == 0 && $availabilityIdentity > 0) {
                $additional['pre_order_days'] = (int)$availabilityIdentity;
            }

            // Wholesale
            if ($struct->wholesale_price && $struct->wholesale_prices) {
                $additional['prices'] = json_encode($struct->wholesale_prices);
            }

            // Params (Specifications)
            if (count($struct->params)) {
                $additional['params'] = json_encode($struct->params);
            }

            $products[$productCode] = array_merge($common, $additional);
        }

        return $products;
    }

    protected function parseCsvRows($csvFile): array
    {
        $items = [];

        if (($handle = fopen($csvFile, 'r')) !== false) {
            while (($item = fgetcsv($handle, 0, ',')) !== false) {
                $items[] = $item;
            }

            fclose($handle);
        }

        array_shift($items); // @note Remove first csv header

        return $items;
    }

    public function importProductsFromYml()
    {
        $this->consoleInfo('* Products importing from Yml url started...');

        $products = $this->getProductsFromYml();

        foreach ($products as $product) {
            $product['store_id'] = $this->store_id;
            ProductProm::updateOrCreate(['code' => $product['code']], $product);
        }

        $this->consoleInfo(sprintf('%s products were imported from Yml url.', count($products)));
    }

    public function getProductsFromYml(): array
    {
        $ymlFile = $this->downloadProductsYmlFile();

        $parser = new \Sirian\YMLParser\Parser();

        /** @var \Sirian\YMLParser\Builder $yml */
        $yml = $parser->parse($ymlFile);

        $products = [];
        foreach ($yml->getOffers() as $offer) { /** @var \Sirian\YMLParser\Offer\VendorModelOffer $offer */
            $productCode = $offer->getId();

            $product = [];
            $product['sku'] = $offer->getVendorCode();
            $product['code'] = $productCode;
            $product['group_name'] = $offer->getCategory()->getName();
            $product['name'] = $offer->getName();
            $product['price'] = $offer->getPrice();
            $product['currency'] = $offer->getCurrency()->getId();
            $product['description_length'] = mb_strlen($offer->getDescription());
            $product['description'] = $offer->getDescription();
            $product['url'] = $offer->getUrl();
            $product['preview'] = preg_replace('/_w\d+_h\d+_/', '_w100_h100_', array_get($offer->getPictures(), '0'));
            $product['images'] = implode(',', $offer->getPictures());

            $offerParams = $offer->getParams();
            if (is_array($offerParams) && count($offerParams)) {
                $product['params'] = [];
                foreach ($offerParams as $param) {
                    $product['params'][] = [
                        'name' => $param->getName(),
                        'unit' => $param->getUnit(),
                        'value' => $param->getValue(),
                    ];
                }
                $product['params'] = json_encode($product['params']);
            }

            $products[$productCode] = $product;
        }

        return $products;
    }

    protected function downloadProductsYmlFile()
    {
        $ymlFile = $this->data_dir . DIRECTORY_SEPARATOR . 'products-yml-cache.xml';

        $this->createDataDirIfNotExists();

        $this->downloadIfExpired($this->products_yml_url, $ymlFile, '-10 min');

        return $ymlFile;
    }

    protected function downloadOrdersXmlFile()
    {
        $xmlFile = $this->data_dir . DIRECTORY_SEPARATOR . 'orders-cache.xml';

        $this->createDataDirIfNotExists();

        $this->downloadIfExpired($this->orders_xml_url, $xmlFile, '-10 min');

        return $xmlFile;
    }

    protected function createDataDirIfNotExists()
    {
        $dataDirPath = $this->data_dir;

        if (!file_exists($dataDirPath)) {
            mkdir($dataDirPath, 0777, true);
        }
    }

    protected function downloadIfExpired(string $sourceUrl, string $destination, string $expiration = '-60 min')
    {
        $this->createDataDirIfNotExists();

        if (!file_exists($destination) || filemtime($destination) < strtotime($expiration)) {
            if (!copy($sourceUrl, $destination)) {
                throw new \Exception("Error downloading the yml file from prom.ua!");
            }
        }
    }

    protected function consoleInfo($message)
    {
        if (\App::runningInConsole()) {
            $output = new \Symfony\Component\Console\Output\ConsoleOutput();
            $output->writeln("<info>$message</info>");
        }
    }
}
