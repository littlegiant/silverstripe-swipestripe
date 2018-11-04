<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Cart;

use SilverStripe\Forms\Validator;

/**
 * Class CartFormValidator
 * @package SwipeStripe\Order\Cart
 * @property CartFormInterface $form
 */
class CartFormValidator extends Validator
{
    /**
     * @inheritDoc
     */
    public function php($data)
    {
        if (!$this->form->getCart()->IsMutable()) {
            $this->result->addError(_t(self::class . '.CART_LOCKED',
                'Your cart is currently locked because there is a checkout in progress. Please complete or cancel the checkout process to modify your cart.'));
        }

        $this->extend('validate', $this->form, $data);
        return $this->result->isValid();
    }

    /**
     * @inheritDoc
     * @param CartFormInterface $form
     */
    public function setForm($form)
    {
        if (!$form instanceof CartFormInterface) {
            throw new \InvalidArgumentException(__CLASS__ . ' can only be used for ' . CartFormInterface::class);
        }

        return parent::setForm($form);
    }
}
