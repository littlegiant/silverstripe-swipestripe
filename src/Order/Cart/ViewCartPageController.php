<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Cart;

use SilverStripe\Forms\Form;
use SilverStripe\ORM\DataObject;
use SwipeStripe\HasActiveCart;
use SwipeStripe\Order\Checkout\CheckoutPage;

/**
 * Class ViewCartPageController
 * @package SwipeStripe\Order\Cart
 * @property ViewCartPage $dataRecord
 * @method ViewCartPage data()
 */
class ViewCartPageController extends \PageController
{
    use HasActiveCart;

    /**
     * @var array
     */
    private static $allowed_actions = [
        'CartForm',
    ];

    /**
     * @return Form
     */
    public function CartForm(): Form
    {
        return CartForm::create($this->ActiveCart, $this, __FUNCTION__);
    }

    /**
     * @return string
     */
    public function getCheckoutLink(): string
    {
        /** @var CheckoutPage $page */
        $page = DataObject::get_one(CheckoutPage::class);
        return $page->Link();
    }
}
