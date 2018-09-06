<?php
declare(strict_types=1);

namespace SwipeStripe\Pages;

use SwipeStripe\Order\Order;

/**
 * Class OrderPage
 * @package SwipeStripe\Pages
 */
class ViewOrderPage extends \Page
{
    use RequiredSinglePage;

    /**
     * @param Order $order
     * @param bool $forceGuestToken Force include guest token.
     * @return string
     */
    public function LinkForOrder(Order $order, bool $forceGuestToken = false): string
    {
        $link = $this->Link($order->ID);

        if ($forceGuestToken || $order->Customer()->IsGuest()) {
            $link .= "/{$order->GuestToken}";
        }

        return $link;
    }
}
