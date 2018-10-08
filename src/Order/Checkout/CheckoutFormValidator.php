<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Checkout;

use SilverStripe\Forms\RequiredFields;

/**
 * Class CheckoutFormValidator
 * @package SwipeStripe\Order\Checkout
 * @property CheckoutForm $form
 */
class CheckoutFormValidator extends RequiredFields
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct([
            'CustomerName',
            'CustomerEmail',
            'BillingAddressStreet',
            'BillingAddressCity',
            'BillingAddressPostCode',
            'BillingAddressCountry',
        ]);
    }

    /**
     * @inheritDoc
     * @param CheckoutForm $form
     */
    public function setForm($form)
    {
        if (!$form instanceof CheckoutForm) {
            throw new \InvalidArgumentException(__CLASS__ . ' can only be used for ' . CheckoutForm::class);
        }

        return parent::setForm($form);
    }

    /**
     * @inheritdoc
     */
    public function php($data)
    {
        if (!$this->form->getCart()->IsMutable()) {
            $this->result->addError(_t(self::class . '.CART_LOCKED', 'There is already another checkout ' .
                'in progress. Please complete or cancel that to checkout again.'));

            return $this->result->isValid();
        }

        $this->extend('beforeRequiredFields', $this->form, $data);
        $parentValid = parent::php($data);
        $this->extend('afterRequiredFields', $this->form, $data);

        $cart = $this->form->getCart();

        if ($cart->Empty()) {
            $this->result->addError(_t(self::class . '.CART_EMPTY',
                'It looks like your cart is empty. Please add some items before attempting to checkout.'));
        }

        if ($data[CheckoutForm::ORDER_HASH_FIELD] !== $cart->Hash) {
            $this->result->addError(_t(self::class . '.ORDER_HASH_CHANGED',
                'It looks like your cart has changed since you last loaded the checkout page. Please refresh, re-check your cart and try again.'));
        }

        $this->extend('validate', $this->form, $data);
        return $parentValid && $this->result->isValid();
    }
}
