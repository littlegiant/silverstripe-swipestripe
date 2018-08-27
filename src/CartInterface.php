<?php

namespace SwipeStripe;

use SwipeStripe\Order\OrderAddOnInterface;
use SwipeStripe\Purchasable\PurchasableAddOnInterface;
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
     * @param OrderAddOnInterface $addOn
     */
    public function attachOrderAddOn(OrderAddOnInterface $addOn): void;

    /**
     * @param OrderAddOnInterface $addOn
     */
    public function detachOrderAddOn(OrderAddOnInterface $addOn): void;

    /**
     * @param PurchasableInterface $item
     * @param PurchasableAddOnInterface $addOn
     */
    public function attachPurchasableAddOn(PurchasableInterface $item, PurchasableAddOnInterface $addOn): void;

    /**
     * @param PurchasableInterface $item
     * @param PurchasableAddOnInterface $addOn
     */
    public function detachPurchasableAddOn(PurchasableInterface $item, PurchasableAddOnInterface $addOn): void;
}
