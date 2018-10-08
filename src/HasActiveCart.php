<?php
declare(strict_types=1);

namespace SwipeStripe;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
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

    public function setActiveCart(?Order $cart): void
    {
        $session = $this->getRequest()->getSession();

        if ($cart !== null) {
            $session->set(SessionData::CART_ID, $cart->ID);
        } else {
            $session->clear(SessionData::CART_ID);
        }
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
