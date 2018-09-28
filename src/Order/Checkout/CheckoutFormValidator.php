<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Checkout;

use SilverStripe\Forms\RequiredFields;
use SilverStripe\Omnipay\GatewayInfo;

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
            CheckoutForm::PAYMENT_METHOD_FIELD,
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
     * @throws \SilverStripe\Omnipay\Exception\InvalidConfigurationException
     */
    public function php($data)
    {
        $valid = parent::php($data);
        $cart = $this->form->getCart();
        $fields = $this->form->Fields();

        if ($cart->Empty()) {
            $this->result->addError(_t(self::class . '.CART_EMPTY', 'It looks like your cart is empty. Please add some items before attempting to checkout.'));
        }

        if ($fields->dataFieldByName(CheckoutForm::ORDER_HASH_FIELD)->dataValue() !== $cart->Hash) {
            $this->result->addError(_t(self::class . '.ORDER_HASH_CHANGED',
                'It looks like your cart has changed since you last loaded the checkout page. Please refresh, re-check your cart and try again.'));
        }

        $paymentMethodField = $fields->dataFieldByName(CheckoutForm::PAYMENT_METHOD_FIELD);
        if ($paymentMethodField->validate($this)) {
            $gateways = GatewayInfo::getSupportedGateways(false);
            $selectedGateway = $paymentMethodField->dataValue();

            if (!isset($gateways[$selectedGateway])) {
                $this->result->addError(_t(self::class . '.INVALID_PAYMENT_METHOD', 'The requested payment method is not available.'));
            }
        }

        return $valid && $this->result->isValid();
    }
}
