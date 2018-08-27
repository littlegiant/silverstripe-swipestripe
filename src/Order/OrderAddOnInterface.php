<?php

namespace SwipeStripe\Order;

use Money\Money;
use SwipeStripe\AddOnInterface;

/**
 * Interface OrderAddOnInterface
 * @package SwipeStripe\Order
 */
interface OrderAddOnInterface extends AddOnInterface
{
    /**
     * @param Order $order
     * @param Money $subtotal Base sub-total.
     * @param Money $runningTotal Running total with previous add-ons applied.
     * @return Money Amount value of this add-on.
     */
    public function getAmount(Order $order, Money $subtotal, Money $runningTotal): Money;

    /**
     * @param Order $order
     */
    public function registerUse(Order $order): void;
}
