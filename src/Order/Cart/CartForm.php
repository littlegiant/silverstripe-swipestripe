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
class CartForm extends Form
{
    /**
     * @var Order
     */
    protected $cart;

    /**
     * CartForm constructor.
     * @param Order $cart
     * @param null|RequestHandler $controller
     * @param null|string $name
     */
    public function __construct(Order $cart, ?RequestHandler $controller = null, ?string $name = null)
    {
        $this->cart = $cart;

        parent::__construct(
            $controller,
            $name ?? static::DEFAULT_NAME,
            $this->buildFields(),
            $this->buildActions(),
            CartFormValidator::create()
        );
    }

    /**
     * @return Order
     */
    public function getCart(): Order
    {
        return $this->cart;
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

        return FieldList::create($fields);
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
        return FieldList::create(
            FormAction::create('UpdateCart', _t(self::class . '.UPDATE_CART', 'Update Cart'))
        );
    }

    /**
     * @inheritDoc
     */
    protected function buildRequestHandler()
    {
        return CartFormRequestHandler::create($this);
    }
}
