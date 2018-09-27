<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use SwipeStripe\RequiredSinglePage;

/**
 * Class ViewOrderPage
 * @package SwipeStripe\Order
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

        if ($forceGuestToken || !$order->Member()->exists()) {
            $link .= "/{$order->GuestToken}";
        }

        return $link;
    }
}
