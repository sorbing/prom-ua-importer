<?php

namespace Sorbing\PromUaImporter\Structs;

class PromUaProductCsvStruct
{
    public $d;
    public $url;
    public $labels;
    public $discount;
    public $vendor_country;
    public $vendor_name;
    public $subgroup_code;
    public $c;
    public $code;
    public $pack_type;
    public $b;
    public $a;
    public $group_url;
    public $group_name;
    public $group_code;
    public $quantity;
    public $available;
    public $images_list;
    public $wholesale_qty;
    public $wholesale_price;
    public $min_qty;
    public $unit;
    public $currency;
    public $price;
    public $type;
    public $description;
    public $tags;
    public $name;
    public $sku;
    public $params;
    public $wholesale_prices;

    public function __construct(array $row)
    {
        //echo "<pre>"; print_r($row); echo "</pre>"; exit;

        $this->sku = (string)$row[0];   // [0] => Код_товара
        $this->name = (string)$row[1];  // [1] => Название_позиции
        $this->tags = $row[2];  // [2] => Ключевые_слова (поиск в каталоге)
        $this->description = (string)$row[3];  // [3] => Описание
        $this->type = $row[4];  // [4] => Тип_товара: r - розница, ? - опт и розн, w - только опт, ? - услуга
        $this->price = (float)$row[5];  // [5] => Цена
        $this->currency = $row[6];  // [6] => Валюта: UAH
        $this->unit = $row[7];  // [7] => Единица_измерения: шт.
        $this->min_qty = $row[8];  // [8] => Минимальный_объем_заказа // @todo
        $this->wholesale_price = $row[9];  // [9] => Оптовая_цена // @todo
        $this->wholesale_qty = $row[10];  // [10] => Минимальный_заказ_опт // @todo
        $this->images_list = $row[11];  // [11] => Ссылка_изображения: ..., ...,
        $this->available = $row[12];  // [12] => Наличие: -, (статус наличия)
        $this->quantity = abs((int)$row[13]);  // [13] => Количество
        $this->group_code = $row[14];  // [14] => Номер_группы: 16445633
        $this->group_name = (string)$row[15];  // [15] => Название_группы: Кожаные ремни
        $this->group_url = $row[16];  // [16] => Адрес_подраздела (ссылка на prom.ua каталог)
        $this->a = $row[17];  // [17] => Возможность_поставки // @todo
        $this->b = $row[18];  // [18] => Срок_поставки // @todo
        $this->pack_type = (string)$row[19];  // [19] => Способ_упаковки: Картонная коробка
        $this->code = (int)$row[20];  // [20] => Уникальный_идентификатор: 408901500
        $this->c = $row[21];  // [21] => Идентификатор_товара // @todo
        $this->subgroup_code = $row[22];  // [22] => Идентификатор_подраздела: 32801 // @todo
        $this->d = $row[23];  // [23] => Идентификатор_группы // @todo
        $this->vendor_name = $row[24];  // [24] => Производитель
        $this->vendor_country = $row[25];  // [25] => Страна_производитель
        $this->discount = $row[26];  // [26] => Скидка // @todo
        $this->d = $row[27];  // [27] => ID_группы_разновидностей
        $this->labels = $row[28];  // [28] => Метки // @todo
        $this->url = (string)$row[29];  // [29] => Продукт_на_сайте

        //$startParamsKey = array_search('Название_Характеристики', $row);
        $startParamsKey = 30;
        $paramsSlice = array_slice($row, $startParamsKey);
        $paramsBatch = array_chunk($paramsSlice, 3);

        $this->params = [];
        foreach ($paramsBatch as $param) {
            if (strlen($param[0])) {
                $this->params[] = ['name' => $param[0], 'unit' => $param[1], 'value' => $param[2]];
            }
        }

        if (strlen($this->wholesale_price)) {
            $this->wholesale_prices = [];

            $optPrices = explode(';', $this->wholesale_price);
            $optPieces = explode(';', $this->wholesale_qty);
            foreach ($optPrices as $i => $optPrice) {
                $this->wholesale_prices[] = ['price' => (float)$optPrice, 'pieces' => (int)array_get($optPieces, $i)];
            }
        }
        
        /* NEW:
        [25] => Страна_производитель
        [26] => Скидка
        [27] => ID_группы_разновидностей
        [28] => Метки
        [29] => Продукт_на_сайте

        [30] => Название_Характеристики
        [31] => Измерение_Характеристики
        [32] => Значение_Характеристики
        [37] => ...
        [95] => Значение_Характеристики
         */

        /* OLD:
        [25] => Гарантийный_срок
        [26] => Страна_производитель
        [27] => Скидка
        [28] => ID_группы_разновидностей
        [29] => Название_производителя
        [30] => Адрес_производителя
        [31] => Метки
        [32] => Характеристики
        [33] => Пользовательские_Характеристики
        [34] => Продукт_на_сайте

        [35] => Название_Характеристики
        [36] => Измерение_Характеристики
        [37] => Значение_Характеристики
        [38] => ...
        [??] => Значение_Характеристики
        */
    }
}