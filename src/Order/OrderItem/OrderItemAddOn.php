<?php
declare(strict_types=1);

namespace SwipeStripe\Order\OrderItem;

use SilverStripe\ORM\DataObject;
use SwipeStripe\AddOn;

/**
 * Add on applied to on order item on a purchase. The add-on is applied once to the item's subtotal (unit price x quantity),
 * not to the unit price (i.e. add-on is not applied $quantity times).
 * @package SwipeStripe\Order\OrderItem
 * @property int $OrderItemID
 * @method null|OrderItem OrderItem()
 */
class OrderItemAddOn extends DataObject
{
    use AddOn;

    const PRIORITY_EARLY = -1;
    const PRIORITY_NORMAL = 0;
    const PRIORITY_LATE = 1;

    /**
     * @var string
     */
    private static $table_name = 'SwipeStripe_Order_OrderItemAddOn';

    /**
     * @var array
     */
    private static $has_one = [
        'OrderItem' => OrderItem::class,
    ];
}
