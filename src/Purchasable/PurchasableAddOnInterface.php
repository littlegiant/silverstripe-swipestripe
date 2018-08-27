<?php

namespace SwipeStripe\Purchasable;

use Money\Money;
use SwipeStripe\AddOnInterface;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderItem\OrderItem;

/**
 * Interface PurchasableAddOnInterface
 * @package SwipeStripe\Purchasable
 */
interface PurchasableAddOnInterface extends AddOnInterface
{
    /**
     * @param PurchasableInterface $item Item being purchased.
     * @param int $quantity Quantity of item in cart.
     * @param Money $basePrice Base price of the item.
     * @param Money $runningPrice Price of the item with earlier add-ons applied.
     * @return Money Amount value of the add-on.
     */
    public function getAmount(PurchasableInterface $item, int $quantity, Money $basePrice, Money $runningPrice): Money;

    /**
     * @param Order $order
     * @param OrderItem $item
     */
    public function registerUse(Order $order, OrderItem $item): void;
}
