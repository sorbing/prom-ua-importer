<?php

namespace Sorbing\PromUaImporter\Models;

use Illuminate\Database\Eloquent\Model;
use Sorbing\PromUaImporter\Collections\OrderPromCollection;

/**
 * \Sorbing\PromUaImporter\Models\OrderProm
 *
 * @property int $id
 * @property int $code
 * @property string|null $status
 * @property string $date
 * @property float $cost
 * @property string|null $payment_type
 * @property string|null $source
 * @property string|null $buyer_comment
 * @property string|null $seller_comment
 * @property string|null $labels
 * @property string|null $cancellation_reason
 * @property string|null $cancellation_comment
 * @property string|null $delivery_type
 * @property string|null $ttn
 * @property string|null $address
 * @property string|null $postal_index
 * @property string|null $customer_name
 * @property string|null $customer_phone
 * @property string|null $customer_email
 * @property mixed|null $broken_items_json
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Sorbing\PromUaImporter\Collections\ProductPromCollection|\Sorbing\PromUaImporter\Models\ProductProm[] $items
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereBrokenItemsJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereBuyerComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereCancellationComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereCustomerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereDeliveryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereLabels($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm wherePostalIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereSellerComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereTtn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * --- My Extend DocBlock ---
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Sorbing\PromUaImporter\Models\OrderProm updateOrCreate(array $attributes, array $values = [])
 */
class OrderProm extends Model
{
    protected $table = 'orders_prom';

    protected $guarded = ['id' , 'created_at', 'updated_at'];

    public function newCollection(array $models = [])
    {
        return new OrderPromCollection($models);
    }

    public function items()
    {
        return $this->belongsToMany(
            '\Sorbing\PromUaImporter\Models\ProductProm',
            'orders_items_prom',
            'order_code',
            'product_code',
            'code',
            'code'
        )->withPivot('qty', 'price');
    }

    // @todo Re-use as trait
    public function storeItemInJsonArray(string $field, array $item, string $uniqueItemKey = null)
    {
        $storedItems = collect(json_decode($this->{$field}, true));

        if ($uniqueItemKey) {
            $replaceableIndex = $storedItems->search(function ($storedItem, $i) use ($uniqueItemKey, $item) {
                return $storedItem[$uniqueItemKey] == $item[$uniqueItemKey];
            });

            if ($replaceableIndex !== false) {
                $storedItems->splice($replaceableIndex); // replace item
            }
        }

        $storedItems->push($item);

        //$this->{$field} = $storedItems->toJson();
        $this->update([$field => $storedItems->toJson()]);
    }
}