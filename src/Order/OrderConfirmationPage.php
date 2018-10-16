<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use SwipeStripe\RequiredSinglePage;

/**
 * Class OrderConfirmationPage
 * @package SwipeStripe\Order
 */
class OrderConfirmationPage extends ViewOrderPage
{
    use RequiredSinglePage;
}
