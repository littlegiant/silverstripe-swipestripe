<?php
declare(strict_types=1);

namespace SwipeStripe\Order\OrderItem;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBBoolean;
use SwipeStripe\AddOn;
use SwipeStripe\Price\DBPrice;

/**
 * Add on applied to on order item on a purchase. The add-on is applied once to the item's subtotal (unit price x quantity),
 * not to the unit price (i.e. add-on is not applied $quantity times).
 * @package SwipeStripe\Order\OrderItem
 * @property bool $ApplyPerUnit
 * @property int $OrderItemID
 * @method null|OrderItem OrderItem()
 */
class OrderItemAddOn extends DataObject
{
    use AddOn;

    /**
     * @var string
     */
    private static $table_name = 'SwipeStripe_Order_OrderItemAddOn';

    /**
     * @var array
     */
    private static $db = [
        'ApplyPerUnit' => DBBoolean::class,
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'OrderItem' => OrderItem::class,
    ];

    /**
     * @inheritDoc
     */
    public function getAmount(): DBPrice
    {
        $baseAmount = $this->BaseAmount;

        return $this->ApplyPerUnit
            ? DBPrice::create_field(DBPrice::class, $baseAmount->getMoney()->multiply($this->OrderItem()->getQuantity()))
            : $baseAmount;
    }
}
