<?php

namespace SwipeStripe;

use SwipeStripe\Order\OrderAddOn;
use SwipeStripe\Purchasable\PurchasableAddOn;
use SwipeStripe\Purchasable\PurchasableInterface;

/**
 * Interface CartInterface
 * @package SwipeStripe
 */
interface CartInterface
{
    /**
     * @return CartInterface
     */
    public function getActiveCart(): CartInterface;

    /**
     *
     */
    public function clearActiveCart(): void;

    /**
     * @param PurchasableInterface $item
     * @param int $quantity
     */
    public function setPurchasableQuantity(PurchasableInterface $item, int $quantity = 1): void;

    /**
     * @param OrderAddOn $addOn
     */
    public function attachOrderAddOn(OrderAddOn $addOn): void;

    /**
     * @param OrderAddOn $addOn
     */
    public function detachOrderAddOn(OrderAddOn $addOn): void;

    /**
     * @param PurchasableInterface $item
     * @param PurchasableAddOn $addOn
     */
    public function attachPurchasableAddOn(PurchasableInterface $item, PurchasableAddOn $addOn): void;

    /**
     * @param PurchasableInterface $item
     * @param PurchasableAddOn $addOn
     */
    public function detachPurchasableAddOn(PurchasableInterface $item, PurchasableAddOn $addOn): void;
}
