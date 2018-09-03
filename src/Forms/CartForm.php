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
    /**
     *
     */
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

        if (empty($this->getMessage()) && !$this->cart->IsMutable()) {
            $this->setMessage(_t(self::class . '.CART_LOCKED',
                'Your cart is currently locked because there is a checkout in progress. Please complete or cancel the checkout process to modify your cart.'),
                ValidationResult::TYPE_WARNING);
        }
    }

    /**
     * @param array $data
     * @param self $form
     * @return HTTPResponse
     */
    public function UpdateCart(array $data, self $form): HTTPResponse
    {
        $form->saveInto($this->cart);
        return $this->getController()->redirectBack();
    }

    /**
     * @inheritDoc
     */
    protected function buildFields(): FieldList
    {
        $fields = [];

        foreach ($this->cart->OrderItems() as $item) {
            $qtyField = OrderItemQuantityField::create($item, sprintf(static::QUANTITY_FIELD_PATTERN, $item->ID),
                _t(self::class . '.QUANTITY_LABEL', 'Quantity'));

            if (!$item->IsMutable()) {
                $qtyField->setReadonly(true);
            }

            $fields[] = $qtyField;
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
