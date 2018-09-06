<?php
declare(strict_types=1);

namespace SwipeStripe\Pages;

use SilverStripe\Forms\Form;
use SwipeStripe\Forms\CheckoutForm;

/**
 * Class CheckoutPageController
 * @package SwipeStripe\Pages
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
