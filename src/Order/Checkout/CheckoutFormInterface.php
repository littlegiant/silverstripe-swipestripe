<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Checkout;

use SilverStripe\Forms\Form;
use SilverStripe\Omnipay\Model\Payment;
use SwipeStripe\Order\Order;

/**
 * Interface CheckoutFormInterface
 * @package SwipeStripe\Order\Checkout
 * @mixin Form
 */
interface CheckoutFormInterface
{
    /**
     * @return Order
     */
    public function getCart(): Order;

    /**
     * @param Order $cart
     * @return $this
     */
    public function setCart(Order $cart): self;

    /**
     * @return string[]
     */
    public function getAvailablePaymentMethods(): array;

    /**
     * @param Payment $payment
     * @return string
     */
    public function getSuccessUrl(Payment $payment): string;

    /**
     * @param Payment $payment
     * @return string
     */
    public function getFailureUrl(Payment $payment): string;
}
