<?php

namespace SwipeStripe\Order\OrderItem;

use SilverStripe\ORM\DataObject;
use SwipeStripe\Purchasable\PurchasableAddOnInterface;

/**
 * Class OrderItemPurchasableAddOnMapping
 * @package SwipeStripe\Order\OrderItem
 * @property int OrderItemID
 * @property int PurchasableAddOnID
 * @property string PurchasableAddOnClass
 * @method OrderItem OrderItem()
 * @method DataObject|PurchasableAddOnInterface PurchasableAddOn()
 */
class OrderItemPurchasableAddOnMapping extends DataObject
{
    const ORDER_ITEM = 'OrderItem';
    const PURCHASABLE_ADD_ON = 'PurchasableAddOn';

    /**
     * @var string
     */
    private static $table_name = 'SwipeStripe_OrderItem_PurchasableAddOns';

    /**
     * @var array
     */
    private static $has_one = [
        self::ORDER_ITEM         => OrderItem::class,
        self::PURCHASABLE_ADD_ON => DataObject::class,
    ];
}
