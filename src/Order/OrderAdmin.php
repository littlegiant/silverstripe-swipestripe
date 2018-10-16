<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use SilverStripe\Admin\ModelAdmin;
use SwipeStripe\ShopPermissions;

/**
 * Class OrderAdmin
 * @package SwipeStripe\Order
 */
class OrderAdmin extends ModelAdmin
{
    /**
     * @var string
     */
    private static $menu_title = 'Orders';

    /**
     * @var string
     */
    private static $url_segment = 'swipestripe/orders';

    /**
     * @var array
     */
    private static $required_permission_codes = [
        ShopPermissions::VIEW_ORDERS,
    ];

    /**
     * @var array
     */
    private static $managed_models = [
        Order::class,
    ];

    /**
     * @inheritDoc
     */
    public function getList()
    {
        $list = parent::getList();

        if ($this->modelClass === Order::class) {
            $list = $list->filter('IsCart', false);
        }

        return $list;
    }
}
