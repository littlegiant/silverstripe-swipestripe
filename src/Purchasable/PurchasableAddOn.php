<?php

namespace SwipeStripe\Purchasable;

use Money\Money;
use SilverStripe\ORM\DataObject;
use SwipeStripe\AddOn;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderItem\OrderItem;

/**
 * Class PurchasableAddOn
 * @package SwipeStripe\Purchasable
 */
class PurchasableAddOn extends DataObject
{
    use AddOn;

    const PRIORITY_EARLY = -1;
    const PRIORITY_NORMAL = 0;
    const PRIORITY_LATE = 1;

    /**
     * @param PurchasableInterface $item Item being purchased.
     * @param int $quantity Quantity of item in cart.
     * @param Money $basePrice Base price of the item.
     * @param Money $runningPrice Price of the item with earlier add-ons applied.
     * @return Money Amount value of the add-on.
     */
    public function getAmount(PurchasableInterface $item, int $quantity, Money $basePrice, Money $runningPrice): Money
    {
        return new Money(0, $basePrice->getCurrency());
    }

    /**
     * @param Order $order
     * @param OrderItem $item
     */
    public function registerUse(Order $order, OrderItem $item): void
    {
    }
}
