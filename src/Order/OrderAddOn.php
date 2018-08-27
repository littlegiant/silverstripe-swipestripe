<?php

namespace SwipeStripe\Order;

use Money\Money;
use SilverStripe\ORM\DataObject;
use SwipeStripe\AddOn;

/**
 * Class OrderAddOn
 * @package SwipeStripe\Order
 */
class OrderAddOn extends DataObject
{
    use AddOn;

    const PRIORITY_EARLY = -1;
    const PRIORITY_NORMAL = 0;
    const PRIORITY_LATE = 1;

    /**
     * @param Order $order
     * @param Money $subtotal Base sub-total.
     * @param Money $runningTotal Running total with previous add-ons applied.
     * @return Money Amount value of this add-on.
     */
    public function getAmount(Order $order, Money $subtotal, Money $runningTotal): Money
    {
        return new Money(0, $subtotal->getCurrency());
    }

    /**
     * @param Order $order
     */
    public function registerUse(Order $order): void
    {
    }
}
