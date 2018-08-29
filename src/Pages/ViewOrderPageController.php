<?php
declare(strict_types=1);

namespace SwipeStripe\Pages;

use SilverStripe\Control\HTTPRequest;
use SwipeStripe\Order\Order;

/**
 * Class ViewOrderPageController
 * @package SwipeStripe\Pages
 * @property ViewOrderPage $dataRecord
 * @method ViewOrderPage data()
 */
class ViewOrderPageController extends \PageController
{
    /**
     * @var array
     */
    private static $url_handlers = [
        '$OrderID!/$GuestToken' => 'ViewOrder',
    ];

    /**
     * @var array
     */
    private static $allowed_actions = [
        'ViewOrder',
    ];

    /**
     *
     */
    public function index()
    {
        $this->httpError(404);
    }

    /**
     * @param HTTPRequest $request
     * @return array
     */
    public function ViewOrder(HTTPRequest $request)
    {
        $orderId = $request->param('OrderID');
        if (!is_numeric($orderId) || intval($orderId) <= 0) {
            $this->httpError(404);
        }

        $order = Order::get_by_id(intval($orderId));

        if ($order === null || !$order->canViewOrderPage(null, $request->param('GuestToken'))) {
            // Can't view = 404, because a 403 Forbidden would leak information (order ID exists)
            $this->httpError(404);
        }

        return [
            'Order' => $order,
        ];
    }
}
