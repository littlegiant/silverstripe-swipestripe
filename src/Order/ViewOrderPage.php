<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use SilverStripe\Control\Controller;
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
     * @return string
     */
    public function LinkForOrder(Order $order): string
    {
        $link = Controller::join_links(
            $this->Link($order->ID),
            $order->GuestToken
        );

        $this->extend('updateLinkForOrder', $order, $link);

        return $link;
    }
}
