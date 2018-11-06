<?php
declare(strict_types=1);

namespace SwipeStripe\Order\OrderItem;

use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\FormField;

/**
 * Interface OrderItemQuantityFieldInterface
 * @package SwipeStripe\Order\OrderItem
 * @mixin FormField
 */
interface OrderItemQuantityFieldInterface
{
    /**
     * @return OrderItem
     */
    public function getOrderItem(): OrderItem;

    /**
     * @param OrderItem $orderItem
     * @return $this
     */
    public function setOrderItem(OrderItem $orderItem): self;

    /**
     * @return null|FormAction
     */
    public function getRemoveAction(): ?FormAction;

    /**
     * @param FormAction $removeAction
     * @return $this
     */
    public function setRemoveAction(FormAction $removeAction): self;
}
