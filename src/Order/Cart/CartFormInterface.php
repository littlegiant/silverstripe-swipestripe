<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Cart;

use SilverStripe\Forms\Form;
use SwipeStripe\Order\Order;

/**
 * Interface CartFormInterface
 * @package SwipeStripe\Order\Cart
 * @mixin Form
 */
interface CartFormInterface
{
    /**
     * @return Order
     */
    public function getCart(): Order;

    /**
     * @param Order $order
     * @return $this
     */
    public function setCart(Order $order): self;
}
