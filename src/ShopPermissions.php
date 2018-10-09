<?php
declare(strict_types=1);

namespace SwipeStripe;

use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;

/**
 * Class ShopPermissions
 * @package SwipeStripe
 */
class ShopPermissions implements PermissionProvider
{
    const VIEW_ORDERS = self::class . '.VIEW_ORDERS';
    const EDIT_ORDERS = self::class . '.EDIT_ORDERS';

    const MANAGE_ORDER_STATUS = self::class . '.MANAGE_ORDER_STATUSES';

    const VIEW_PRODUCTS = self::class . '.VIEW_PRODUCTS';
    const EDIT_PRODUCTS = self::class . '.EDIT_PRODUCTS';
    const CREATE_PRODUCTS = self::class . '.CREATE_PRODUCTS';
    const DELETE_PRODUCTS = self::class . '.DELETE_PRODUCTS';

    /**
     * @inheritDoc
     */
    public function providePermissions(): array
    {
        $permissionCategory = _t(Permission::class . '.SWIPESTRIPE_CATEGORY', 'SwipeStripe');

        return [
            self::VIEW_ORDERS => [
                'name'     => _t(self::VIEW_ORDERS, 'View orders'),
                'category' => $permissionCategory,
                'help'     => _t(self::VIEW_ORDERS . '_HELP', 'View orders in the CMS.'),
            ],
            self::EDIT_ORDERS => [
                'name'     => _t(self::EDIT_ORDERS, 'Edit orders'),
                'category' => $permissionCategory,
                'help'     => _t(self::EDIT_ORDERS . '_HELP', 'Edit orders in the CMS.'),
            ],

            self::MANAGE_ORDER_STATUS => [
                'name'     => _t(self::MANAGE_ORDER_STATUS, 'Manage order status'),
                'category' => $permissionCategory,
                'help'     => _t(self::MANAGE_ORDER_STATUS . '_HELP', 'Manage order status in the CMS.'),
            ],

            self::VIEW_PRODUCTS   => [
                'name'     => _t(self::VIEW_PRODUCTS, 'View products'),
                'category' => $permissionCategory,
                'help'     => _t(self::VIEW_PRODUCTS . '_HELP', 'View products in the CMS.'),
            ],
            self::EDIT_PRODUCTS   => [
                'name'     => _t(self::EDIT_PRODUCTS, 'Edit products'),
                'category' => $permissionCategory,
                'help'     => _t(self::EDIT_PRODUCTS . '_HELP', 'Edit products in the CMS.'),
            ],
            self::CREATE_PRODUCTS => [
                'name'     => _t(self::CREATE_PRODUCTS, 'Create products'),
                'category' => $permissionCategory,
                'help'     => _t(self::CREATE_PRODUCTS . '_HELP', 'Create new products in the CMS.'),
            ],
            self::DELETE_PRODUCTS => [
                'name'     => _t(self::DELETE_PRODUCTS, 'Delete products'),
                'category' => $permissionCategory,
                'help'     => _t(self::DELETE_PRODUCTS . '_HELP', 'Delete products in the CMS.'),
            ],
        ];
    }
}
