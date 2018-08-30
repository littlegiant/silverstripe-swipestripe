<?php
declare(strict_types=1);

namespace SwipeStripe\Forms\Fields;

use SilverStripe\Forms\Form;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DataObjectInterface;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderItem\OrderItem;

/**
 * Class OrderItemQuantityField
 * @package SwipeStripe\Forms\Fields
 */
class OrderItemQuantityField extends NumericField
{
    /**
     * @var OrderItem
     */
    protected $orderItem;

    /**
     * @inheritDoc
     * @param OrderItem $item
     */
    public function __construct(OrderItem $item, string $name, ?string $title = null, ?int $value = null, ?int $maxLength = null, ?Form $form = null)
    {
        parent::__construct($name, $title, $value ?? $item->getQuantity(), $maxLength, $form);
        $this->orderItem = $item;
    }

    /**
     * @return OrderItem
     */
    public function getOrderItem(): OrderItem
    {
        return $this->orderItem;
    }

    /**
     * @inheritDoc
     */
    public function saveInto(DataObjectInterface $record)
    {
        if ($record instanceof Order) {
            $orderItemOrderID = intval($this->orderItem->OrderID);
            if ($orderItemOrderID > 0 && $orderItemOrderID === intval($record->ID)) {
                $record = $this->orderItem;
            } else {
                // Order that doesn't contain this order item, can't determine what OrderItem to save to.
                throw new \InvalidArgumentException("Order passed to " . __CLASS__ . "::saveInto doesn't contain Order item '{$this->orderItem->ID}'.");
            }
        }

        if ($record instanceof OrderItem) {
            $record->setQuantity($this->dataValue());
        } else {
            parent::saveInto($record);
        }
    }
}
