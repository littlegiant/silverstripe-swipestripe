<?php
declare(strict_types=1);

namespace SwipeStripe;

use SilverStripe\Control\HTTPRequest;
use SwipeStripe\Order\Order;

/**
 * Controller trait to expose active cart.
 * @property Order $ActiveCart
 * @package SwipeStripe
 */
trait HasActiveCart
{
    /**
     * @return Order
     */
    public function getActiveCart(): Order
    {
        $session = $this->getRequest()->getSession();
        $cartId = intval($session->get(SessionData::CART_ID));

        if ($cartId > 0) {
            $cartObj = Order::get_by_id($cartId);

            if ($cartObj !== null && $cartObj->IsCart) {
                return $cartObj;
            }
        }

        $cartObj = Order::singleton()->createCart();
        $this->setActiveCart($cartObj);

        return $cartObj;
    }

    /**
     * @param null|Order $cart
     */
    public function setActiveCart(?Order $cart): void
    {
        if ($cart === null) {
            $this->clearActiveCart();
            return;
        }

        if (!$cart->IsMutable()) {
            throw new \InvalidArgumentException('Order passed to ' . __METHOD__ . ' must be mutable.');
        }

        if (!$cart->isInDB()) {
            $cart->write();
        }

        $this->getRequest()->getSession()->set(SessionData::CART_ID, $cart->ID);
    }

    /**
     *
     */
    public function clearActiveCart(): void
    {
        $this->getRequest()->getSession()->clear(SessionData::CART_ID);
    }

    /**
     * @return HTTPRequest
     */
    abstract public function getRequest();
}
