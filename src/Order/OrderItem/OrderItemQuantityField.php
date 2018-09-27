<?php
declare(strict_types=1);

namespace SwipeStripe\Order\OrderItem;

use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DataObjectInterface;
use SwipeStripe\Order\Order;

/**
 * Class OrderItemQuantityField
 * @package SwipeStripe\Order\OrderItem
 */
class OrderItemQuantityField extends NumericField
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
     * @param OrderItem $item
     */
    public function __construct(OrderItem $item, string $name, ?string $title = null, ?int $value = null, ?int $maxLength = null, ?Form $form = null)
    {
        parent::__construct($name, $title, $value ?? $item->getQuantity(), $maxLength, $form);
        $this->orderItem = $item;

        if (!$item->IsMutable()) {
            $this->setReadonly(true);
        }
    }

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
                throw new \InvalidArgumentException("Order passed to " . __CLASS__ . "::saveInto doesn't contain Order item '{$this->getOrderItem()->ID}'.");
            }
        }

        if ($record instanceof OrderItem) {
            $record->setQuantity($this->dataValue());
        } else {
            parent::saveInto($record);
        }
    }

    /**
     * @return OrderItem
     */
    public function getOrderItem(): OrderItem
    {
        return $this->orderItem;
    }

    /**
     * @return null|FormAction
     */
    public function getRemoveAction(): ?FormAction
    {
        return $this->removeAction;
    }

    /**
     * @param FormAction $removeAction
     * @return $this
     */
    public function setRemoveAction(FormAction $removeAction): self
    {
        $this->removeAction = $removeAction;
        return $this;
    }
}
