<?php
declare(strict_types=1);

namespace SwipeStripe\Forms;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\ValidationResult;
use SwipeStripe\Forms\Fields\OrderItemQuantityField;
use SwipeStripe\Order\Order;

/**
 * Class CartForm
 * @package SwipeStripe\Forms
 */
class CartForm extends BaseForm
{
    const QUANTITY_FIELD_PATTERN = 'Qty_%d';

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
        parent::__construct($controller, $name);
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
     * @inheritDoc
     */
    public function validationResult()
    {
        $result = parent::validationResult();

        if (!$this->cart->IsMutable()) {
            $result->addError(_t(self::class . '.CART_LOCKED',
                'Your cart is currently locked because there is a checkout in progress. Please complete or cancel the checkout process to modify your cart.'));
        }

        return $result;
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
                _t(self::class . '.QUANTITY_LABEL', 'Quantity'));
        }

        return FieldList::create($fields);
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
