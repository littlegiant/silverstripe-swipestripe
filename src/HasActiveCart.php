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

        $cartObj = Order::create();
        $cartObj->IsCart = true;
        $cartObj->write();

        $session->set(SessionData::CART_ID, $cartObj->ID);
        return $cartObj;
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
