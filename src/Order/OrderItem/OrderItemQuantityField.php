<?php
declare(strict_types=1);

namespace SwipeStripe\Order\OrderItem;

use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DataObjectInterface;
use SwipeStripe\Order\Order;

/**
 * Class OrderItemQuantityField
 * @package SwipeStripe\Order\OrderItem
 */
class OrderItemQuantityField extends NumericField implements OrderItemQuantityFieldInterface
{
    /**
     * @var OrderItem
     */
    protected $orderItem;

    /**
     * @var FormAction
     */
    protected $removeAction;

    /**
     * @inheritDoc
     */
    public function validate($validator)
    {
        if (!parent::validate($validator)) {
            return false;
        }

        $valid = true;
        $this->extend('validate', $valid);
        return $valid;
    }

    /**
     * @inheritDoc
     */
    public function saveInto(DataObjectInterface $record)
    {
        if ($record instanceof Order) {
            $orderItemOrderID = intval($this->getOrderItem()->OrderID);
            if ($orderItemOrderID > 0 && $orderItemOrderID === intval($record->ID)) {
                $record = $this->getOrderItem();
            } else {
                // Order that doesn't contain this order item, can't determine what OrderItem to save to.
                throw new \InvalidArgumentException("Order passed to " . __METHOD__ .
                    " doesn't contain Order item '{$this->getOrderItem()->ID}'.");
            }
        }

        if ($record instanceof OrderItem) {
            $record->setQuantity($this->dataValue());
        } else {
            parent::saveInto($record);
        }
    }

    /**
     * @inheritdoc
     */
    public function setOrderItem(OrderItem $orderItem): OrderItemQuantityFieldInterface
    {
        $this->orderItem = $orderItem;
        $this->setReadonly(!$orderItem->IsMutable());
        $this->setValue($orderItem->getQuantity());

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrderItem(): OrderItem
    {
        return $this->orderItem;
    }

    /**
     * @inheritdoc
     */
    public function getRemoveAction(): ?FormAction
    {
        return $this->removeAction;
    }

    /**
     * @inheritdoc
     */
    public function setRemoveAction(FormAction $removeAction): OrderItemQuantityFieldInterface
    {
        $this->removeAction = $removeAction;
        return $this;
    }
}
