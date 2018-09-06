<?php
declare(strict_types=1);

namespace SwipeStripe\Constants;

use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SwipeStripe\Order\Order;

/**
 * Class ShopPermissions
 * @package SwipeStripe\Constants
 */
class ShopPermissions implements PermissionProvider
{
    const VIEW_ORDERS = self::class . '.VIEW_ORDERS';
    const EDIT_ORDERS = self::class . '.EDIT_ORDERS';

    /**
     * @inheritDoc
     */
    public function providePermissions(): array
    {
        $permissionCategory = _t(Permission::class . '.SWIPESTRIPE_CATEGORY', 'SwipeStripe');

        return [
            self::VIEW_ORDERS => [
                'name'     => _t(self::VIEW_ORDERS, 'View {orders}', ['orders' => Order::singleton()->i18n_plural_name()]),
                'category' => $permissionCategory,
                'help'     => _t(self::VIEW_ORDERS . '_HELP', 'View orders in the CMS.'),
            ],
            self::EDIT_ORDERS => [
                'name'     => _t(self::EDIT_ORDERS, 'Edit {orders}', ['orders' => Order::singleton()->i18n_plural_name()]),
                'category' => $permissionCategory,
                'help'     => _t(self::EDIT_ORDERS . '_HELP', 'Edit orders in the CMS.'),
            ],
        ];
    }
}
