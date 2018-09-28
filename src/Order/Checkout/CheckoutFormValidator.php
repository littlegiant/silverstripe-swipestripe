<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Checkout;

use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\SingleSelectField;
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

        if ($cart->Empty()) {
            $this->result->addError(_t(self::class . '.CART_EMPTY', 'It looks like your cart is empty. Please add some items before attempting to checkout.'));
        }

        if ($data[CheckoutForm::ORDER_HASH_FIELD] !== $cart->Hash) {
            $this->result->addError(_t(self::class . '.ORDER_HASH_CHANGED',
                'It looks like your cart has changed since you last loaded the checkout page. Please refresh, re-check your cart and try again.'));
        }

        $this->validatePaymentMethod($data);


        return $valid && $this->result->isValid();
    }

    /**
     * @param array $data
     * @throws \SilverStripe\Omnipay\Exception\InvalidConfigurationException
     */
    protected function validatePaymentMethod(array $data): void
    {
        if ($this->form->Fields()->dataFieldByName(CheckoutForm::PAYMENT_METHOD_FIELD) instanceof SingleSelectField) {
            // Single select field will validate option is allowed
            return;
        }

        $gateways = GatewayInfo::getSupportedGateways(false);
        $selectedGateway = $data[CheckoutForm::PAYMENT_METHOD_FIELD];

        if (!isset($gateways[$selectedGateway])) {
            // Could be hidden field for single available payment method, so we must make it a form-level message
            $this->result->addError(_t(self::class . '.INVALID_PAYMENT_METHOD', 'The requested payment method is not available.'));
        }
    }
}
