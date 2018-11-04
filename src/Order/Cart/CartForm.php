<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Cart;

use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SwipeStripe\Order\Order;
use SwipeStripe\Order\OrderItem\OrderItem;
use SwipeStripe\Order\OrderItem\OrderItemQuantityField;

/**
 * Class CartForm
 * @package SwipeStripe\Order\Cart
 */
class CartForm extends Form implements CartFormInterface
{
    /**
     * @var Order
     */
    protected $cart;

    /**
     * CartForm constructor.
     * @param null|RequestHandler $controller
     * @param null|string $name
     */
    public function __construct(?RequestHandler $controller = null, ?string $name = null)
    {
        parent::__construct(
            $controller,
            $name ?? static::DEFAULT_NAME,
            FieldList::create(),
            $this->buildActions(),
            CartFormValidator::create()
        );
    }

    /**
     * @inheritdoc
     */
    public function getCart(): Order
    {
        return $this->cart;
    }

    /**
     * @inheritDoc
     */
    public function setCart(Order $order): CartFormInterface
    {
        $this->cart = $order;
        $this->setFields($this->buildFields());

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function buildFields(): FieldList
    {
        $fields = [];

        foreach ($this->getCart()->OrderItems() as $item) {
            $fields[] = OrderItemQuantityField::create($item, "Qty_{$item->ID}",
                _t(self::class . '.QUANTITY_LABEL', 'Quantity')
            )->setRemoveAction($this->getRemoveActionFor($item));
        }

        $fields = FieldList::create($fields);
        $this->extend('updateFields', $fields);
        return $fields;
    }

    /**
     * @param OrderItem $item
     * @return FormAction
     */
    protected function getRemoveActionFor(OrderItem $item): FormAction
    {
        return FormAction::create(
            sprintf('%1$s?%2$s=%3$d', CartFormRequestHandler::REMOVE_ITEM_ACTION,
                CartFormRequestHandler::REMOVE_ITEM_ARG, $item->ID),
            _t(self::class . '.REMOVE_ITEM', 'Remove')
        )->setDisabled(!$item->IsMutable()); // Disable if item is immutable
    }

    /**
     * @inheritDoc
     */
    protected function buildActions(): FieldList
    {
        $actions = FieldList::create(
            FormAction::create('UpdateCart', _t(self::class . '.UPDATE_CART', 'Update Cart'))
        );

        $this->extend('updateActions', $actions);
        return $actions;
    }

    /**
     * @inheritDoc
     */
    protected function buildRequestHandler()
    {
        return CartFormRequestHandler::create($this);
    }
}
