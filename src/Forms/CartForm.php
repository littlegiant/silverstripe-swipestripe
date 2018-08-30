<?php
declare(strict_types=1);

namespace SwipeStripe\Forms;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
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
    const DEFAULT_NAME = 'CartForm';

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
