<?php
declare(strict_types=1);

namespace SwipeStripe\Pages;

use SilverStripe\Control\Controller;
use SwipeStripe\Order\Order;

/**
 * Controller trait to expose active cart.
 * @package SwipeStripe\Pages
 * @mixin Controller
 */
trait HasActiveCart
{
    /**
     * Session variable used to store ID of the active cart. Should be a constant, but traits can't have constants.
     * TODO - refactor to a constant somewhere else?
     * @var string
     */
    private static $SESSION_CART_ID = __TRAIT__ . '.ActiveCartID';

    /**
     * @return Order
     */
    public function getActiveCart(): Order
    {
        $session = $this->getRequest()->getSession();
        $cartId = intval($session->get(static::$SESSION_CART_ID));

        if ($cartId > 0) {
            $cartObj = Order::get_by_id($cartId);

            if ($cartObj !== null && $cartObj->IsCart) {
                return $cartObj;
            }
        }

        $cartObj = Order::create();
        $cartObj->IsCart = true;
        $cartObj->write();

        $session->set(static::$SESSION_CART_ID, $cartObj->ID)->save($this->getRequest());
        return $cartObj;
    }

    /**
     *
     */
    public function clearActiveCart(): void
    {
        $this->getRequest()->getSession()->clear(static::$SESSION_CART_ID);
    }
}
