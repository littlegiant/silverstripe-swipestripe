<?php
declare(strict_types=1);

namespace SwipeStripe\Pages;

use SilverStripe\Control\Controller;
use SwipeStripe\Order\Order;
use SwipeStripe\SessionData;

/**
 * Controller trait to expose active cart.
 * @property Order $ActiveCart
 * @package SwipeStripe\Pages
 * @mixin Controller
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

        $session->set(SessionData::CART_ID, $cartObj->ID)->save($this->getRequest());
        return $cartObj;
    }

    /**
     *
     */
    public function clearActiveCart(): void
    {
        $this->getRequest()->getSession()->clear(SessionData::CART_ID);
    }
}
