<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Checkout;

use SilverStripe\Forms\Form;
use SwipeStripe\HasActiveCart;
use SwipeStripe\Pages\ViewCartPage;

/**
 * Class CheckoutPageController
 * @package SwipeStripe\Order\Checkout
 */
class CheckoutPageController extends \PageController
{
    use HasActiveCart;

    /**
     * @var array
     */
    private static $allowed_actions = [
        'CheckoutForm',
    ];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        if ($this->ActiveCart->Empty()) {
            /** @var ViewCartPage $cartPage */
            $cartPage = ViewCartPage::get_one(ViewCartPage::class);
            $this->redirect($cartPage->Link());
        }

        parent::init();
    }

    /**
     * @return Form
     */
    public function CheckoutForm(): Form
    {
        return CheckoutForm::create($this->ActiveCart, $this, __FUNCTION__);
    }
}
