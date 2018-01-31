<?php

namespace Sorbing\PromUaImporter\Models;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Builder;
use Sorbing\PromUaImporter\Collections\ProductPromCollection;

/**
 * \Sorbing\PromUaImporter\Models\ProductProm
 *
 * @property int $id
 * @property string|null $sku
 * @property int $code
 * @property string|null $group_name
 * @property string $name
 * @property float $price
 * @property mixed|null $prices
 * @property float $discount
 * @property string $currency
 * @property int $quantity
 * @property int $pre_order_days
 * @property string|null $unit
 * @property int $sales_num
 * @property int $description_length
 * @property string|null $description
 * @property string|null $pack_type
 * @property string $url
 * @property string|null $category_url
 * @property string|null $preview
 * @property string|null $images
 * @property string|null $tags
 * @property string|null $labels
 * @property mixed|null $params
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Sorbing\PromUaImporter\Collections\OrderPromCollection|\Sorbing\PromUaImporter\Models\OrderProm[] $orders
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereCategoryUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereDescriptionLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereGroupName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereLabels($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm wherePackType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereParams($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm wherePreOrderDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm wherePreview($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm wherePrices($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereSalesNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\ProductProm whereUrl($value)
 * @mixin \Eloquent
 *
 * --- My Extend DocBlock ---
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static all($columns = ['*'])
 */
class ProductProm extends Model
{
    protected $table = 'products_prom';

    protected $guarded = ['id' , 'created_at', 'updated_at'];

    public function newCollection(array $models = [])
    {
        return new ProductPromCollection($models);
    }

    public function orders()
    {
        return $this->belongsToMany(
            '\Sorbing\PromUaImporter\Models\OrderProm',
            'orders_items_prom',
            'product_code',
            'order_code',
            'code',
            'code'
        )->withPivot('qty', 'price');
    }

}