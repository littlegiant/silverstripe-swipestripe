<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Cart;

use SilverStripe\Control\HTTPResponse;
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
    const QUANTITY_FIELD_PATTERN = 'Qty_%d';
    const REMOVE_ITEM_ACTION = 'RemoveOrderItem';
    const REMOVE_ITEM_ARG = 'OrderItemID';

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
     * @return HTTPResponse
     */
    public function UpdateCart(): HTTPResponse
    {
        $this->saveInto($this->cart);
        return $this->getController()->redirectBack();
    }

    /**
     * @param array $data
     * @return HTTPResponse
     */
    public function RemoveOrderItem(array $data): HTTPResponse
    {
        $orderItemID = intval($data[static::REMOVE_ITEM_ARG] ?? 0);
        $this->cart->removeItem($orderItemID);
        return $this->getController()->redirectBack();
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

        foreach ($this->cart->OrderItems() as $item) {
            $fields[] = OrderItemQuantityField::create($item, sprintf(static::QUANTITY_FIELD_PATTERN, $item->ID),
                _t(self::class . '.QUANTITY_LABEL', 'Quantity'))
                ->setRemoveAction($this->getRemoveActionFor($item));
        }

        return FieldList::create($fields);
    }

    /**
     * @param OrderItem $item
     * @return FormAction
     */
    protected function getRemoveActionFor(OrderItem $item): FormAction
    {
        return FormAction::create(static::REMOVE_ITEM_ACTION . '?' . static::REMOVE_ITEM_ARG . "={$item->ID}",
            _t(self::class . '.REMOVE_ITEM', 'Remove'))
            // Disable if item is immutable
            ->setDisabled(!$item->IsMutable());
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
}
