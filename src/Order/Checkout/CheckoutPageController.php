<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Checkout;

use SilverStripe\Core\Injector\Injector;
use SwipeStripe\HasActiveCart;
use SwipeStripe\Order\Cart\ViewCartPage;

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
     * @return CheckoutFormInterface
     */
    public function CheckoutForm(): CheckoutFormInterface
    {
        return Injector::inst()->create(CheckoutFormInterface::class, $this->ActiveCart, $this, __FUNCTION__);
    }
}
