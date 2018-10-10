<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Status;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SwipeStripe\Order\Order;
use SwipeStripe\ShopPermissions;
use UncleCheese\DisplayLogic\Extensions\DisplayLogic;

/**
 * Class OrderStatusUpdate
 * @package SwipeStripe\Order\Status
 * @property string $Status
 * @property string $Message
 * @property bool $CustomerVisible
 * @property bool $NotifyCustomer
 * @property int $OrderID
 * @method Order Order()
 */
class OrderStatusUpdate extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'SwipeStripe_Order_OrderStatusUpdate';

    /**
     * @var array
     */
    private static $db = [
        'Status'          => OrderStatus::ENUM,
        'Message'         => 'Text',
        'CustomerVisible' => 'Boolean',
        'NotifyCustomer'  => 'Boolean',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Order' => Order::class,
    ];

    /**
     * @var string
     */
    private static $default_sort = '"Created" DESC';

    /**
     * @var array
     */
    private static $summary_fields = [
        'Created.Nice'         => 'Created',
        'Status'               => 'Status',
        'Message.Summary'      => 'Message',
        'CustomerVisible.Nice' => 'Customer Visible',
    ];

    /**
     * @var array
     */
    private static $searchable_fields = [
        'Status',
        'Message',
        'CustomerVisible',
    ];

    /**
     * @var array
     */
    private static $defaults = [
        'CustomerVisible' => true,
    ];

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return _t(self::class . '.Title', '{order_title} - {status}', [
            'order_title' => $this->Order()->Title,
            'status'      => _t(OrderStatus::class . ".{$this->Status}", $this->Status),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function canView($member = null)
    {
        return parent::canView($member) || $this->Order()->canView($member) ||
            Permission::checkMember($member, ShopPermissions::MANAGE_ORDER_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function canEdit($member = null)
    {
        return parent::canEdit($member) || Permission::checkMember($member, ShopPermissions::MANAGE_ORDER_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function canDelete($member = null)
    {
        return parent::canEdit($member) || Permission::checkMember($member, ShopPermissions::MANAGE_ORDER_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function canCreate($member = null, $context = [])
    {
        return parent::canEdit($member) || Permission::checkMember($member, ShopPermissions::MANAGE_ORDER_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName('OrderID');

            /** @var CheckboxField|DisplayLogic $notify */
            $notify = $fields->dataFieldByName('NotifyCustomer');
            $notify->setDescription(_t(self::class . '.NOTIFY_HELP', 'Send a notification email about ' .
                'this update to the customer. A notification will not be sent if the customer has disabled ' .
                'notifications. Only applies for new, customer visible updates.'));

            if ($this->isInDB()) {
                $notify->setTitle(_t(self::class . '.NOTIFIED_TITLE', 'Customer Notified'))
                    ->setReadonly(true)
                    ->setDisabled(true);
            } else {
                $notify->hideIf('CustomerVisible')->isNotChecked();
            }
        });

        return parent::getCMSFields();
    }

    /**
     * @inheritDoc
     */
    protected function onAfterWrite()
    {
        parent::onAfterWrite();

        $latestOrderUpdate = $this->Order()->OrderStatusUpdates()->sort('ID', 'DESC')->first();
        if ($latestOrderUpdate && $latestOrderUpdate->ID === $this->ID) {
            // Update Order status if this update is newest
            $this->Order()->Status = $this->Status;
            $this->Order()->write();
        }

        if ($this->shouldSendNotification()) {
            // TODO - send email to customer
        }
    }

    /**
     * @return bool
     */
    protected function shouldSendNotification(): bool
    {
        $shouldSend = $this->isChanged('ID') &&
            $this->CustomerVisible && $this->NotifyCustomer;

        $this->extend('shouldSendNotification', $shouldSend);
        return $shouldSend;
    }
}
