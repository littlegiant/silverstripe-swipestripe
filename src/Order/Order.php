<?php
declare(strict_types=1);

namespace SwipeStripe\Order;

use Money\Money;
use SilverStripe\Control\Director;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Omnipay\Model\Payment;
use SilverStripe\Omnipay\Service\ServiceResponse;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\Address\DBAddress;
use SwipeStripe\CMSHelper;
use SwipeStripe\Forms\Fields\AlwaysModifiableGridField;
use SwipeStripe\Order\Cart\ViewCartPage;
use SwipeStripe\Order\OrderItem\OrderItem;
use SwipeStripe\Order\OrderItem\OrderItemAddOn;
use SwipeStripe\Order\Status\OrderStatus;
use SwipeStripe\Order\Status\OrderStatusUpdate;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\Price\PriceField;
use SwipeStripe\Price\SupportedCurrencies\SupportedCurrenciesInterface;
use SwipeStripe\ShopPermissions;

/**
 * Class Order
 * @package SwipeStripe\Order
 * @property bool                              $IsCart
 * @property string                            $CartLockedAt
 * @property string                            $GuestToken
 * @property string                            $Hash
 * @property string                            $CustomerName
 * @property string                            $CustomerEmail
 * @property DBAddress                         $BillingAddress
 * @property string                            $ConfirmationTime
 * @property string                            $Status
 * @method HasManyList|OrderStatusUpdate[] OrderStatusUpdates()
 * @method HasManyList|OrderItem[] OrderItems()
 * @method HasManyList|OrderAddOn[] OrderAddOns()
 * @mixin Payable
 * @property-read SupportedCurrenciesInterface $supportedCurrencies
 */
class Order extends DataObject
{
    use CMSHelper;

    const GUEST_TOKEN_BYTES = 16;

    /**
     * @var string
     */
    private static $table_name = 'SwipeStripe_Order';

    /**
     * @var array
     */
    private static $db = [
        'IsCart'       => 'Boolean',
        'CartLockedAt' => 'Datetime',

        'GuestToken'       => 'Varchar',
        'ConfirmationTime' => 'Datetime',
        'Status'           => OrderStatus::ENUM,

        'CustomerName'   => 'Varchar',
        'CustomerEmail'  => 'Varchar',
        'BillingAddress' => 'Address',
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'OrderAddOns'        => OrderAddOn::class,
        'OrderItems'         => OrderItem::class,
        'OrderStatusUpdates' => OrderStatusUpdate::class,
    ];

    /**
     * @var array
     */
    private static $cascade_duplicates = [
        'OrderAddOns',
        'OrderItems',
    ];

    /**
     * @var array
     */
    private static $extensions = [
        'payable' => Payable::class,
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'ID'               => 'ID',
        'Title'            => 'Title',
        'Status'           => 'Status',
        'CustomerName'     => 'Customer Name',
        'CustomerEmail'    => 'Customer Email',
        'OrderItems.Count' => 'Items',
        'Total.Value'      => 'Total',
        'ConfirmationTime' => 'Confirmation Time',
    ];

    /**
     * @var array
     */
    private static $searchable_fields = [
        'CustomerName',
        'CustomerEmail',
        'Status',
    ];

    /**
     * @var string
     */
    private static $default_sort = '"ConfirmationTime" DESC, "LastEdited" DESC';

    /**
     * @var array
     */
    private static $dependencies = [
        'supportedCurrencies' => '%$' . SupportedCurrenciesInterface::class,
    ];

    /**
     * @var array
     */
    private static $defaults = [
        'IsCart' => true,
        'Status' => OrderStatus::PENDING,
    ];

    /**
     * @return Order
     */
    public function createCart(): self
    {
        $cart = Order::create();
        $cart->write();

        return static::get_by_id($cart->ID);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function populateDefaults()
    {
        parent::populateDefaults();

        $this->GuestToken = bin2hex(random_bytes(static::GUEST_TOKEN_BYTES));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return _t(self::class . '.Title', '{name} #{id}', [
            'name' => $this->i18n_singular_name(),
            'id'   => $this->ID,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $result = parent::validate();

        if (!$this->IsCart && empty($this->CartLockedAt)) {
            $result->addFieldError('CartLockedAt', _t(self::class . '.NONCART_NOT_LOCKED',
                'Non-cart order must be locked to a certain time.'));
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName([
                'IsCart',
                'CartLockedAt',
                'GuestToken',
                'ConfirmationTime',
                'Status',
            ]);

            $fields->insertBefore('CustomerName', FieldGroup::create([
                $this->dbObject('ConfirmationTime')->scaffoldFormField(),
                $this->dbObject('Status')->scaffoldFormField(),
            ]));

            $fields->insertAfter('BillingAddress',
                PriceField::create('SubTotalValue', 'Sub-Total')->setValue($this->SubTotal()));
            $fields->insertAfter('SubTotalValue', PriceField::create('TotalValue', 'Total')->setValue($this->Total()));

            $this->moveTabBefore($fields, 'Payments', 'Root.OrderItems');
            $this->moveTabBefore($fields, 'Payments', 'Root.OrderAddOns');

            $statusUpdates = $fields->dataFieldByName('OrderStatusUpdates');
            if ($statusUpdates instanceof GridField) {
                $statusUpdates->setConfig(GridFieldConfig_RecordEditor::create());
            }

            $this->addViewButtonToGridFields($fields, [
                'OrderItems',
                'OrderAddOns',
                'Payments',
            ]);
        });

        /** @see DataObject::getCMSFields() */
        $tabbedFields = $this->scaffoldFormFields(array(
            'includeRelations' => $this->isInDB(),
            'tabbed'           => true,
            'ajaxSafe'         => true,
            'fieldClasses'     => [
                'OrderStatusUpdates' => AlwaysModifiableGridField::class,
            ],
        ));

        $this->extend('updateCMSFields', $tabbedFields);

        return $tabbedFields;
    }

    /**
     * Check if a token is a well formed (potentially valid) order token. This should return true for any historic token
     * generation schemes - i.e. if it's possible $token was generated at any point as a GuestToken, this should return true.
     * @param null|string $token
     * @return bool
     */
    public function isWellFormedGuestToken(?string $token = null): bool
    {
        // bin2hex(GUEST_TOKEN_BYTES) will return 2 characters per byte.
        $wellFormed = strlen($token) === 2 * static::GUEST_TOKEN_BYTES;

        $this->extend('isWellFormedGuestToken', $token, $wellFormed);
        return $wellFormed;
    }

    /**
     * @return DBPrice
     */
    public function UnpaidTotal(): DBPrice
    {
        $cartTotalMoney = $this->Total()->getMoney();
        $dueMoney = $cartTotalMoney->subtract($this->TotalPaid()->getMoney());

        $this->extend('updateUnpaidTotal', $dueMoney);
        return DBPrice::create_field(DBPrice::INJECTOR_SPEC, $dueMoney);
    }

    /**
     * @param bool $applyOrderAddOns
     * @param bool $applyOrderItemAddOns
     * @return DBPrice
     */
    public function Total(bool $applyOrderAddOns = true, bool $applyOrderItemAddOns = true): DBPrice
    {
        $subTotal = $this->SubTotal($applyOrderItemAddOns)->getMoney();
        $runningTotal = $subTotal;

        if ($applyOrderAddOns && !$this->Empty()) {
            foreach ($this->OrderAddOns() as $addOn) {
                if ($addOn->isActive()) {
                    $runningTotal = $runningTotal->add($addOn->Amount->getMoney());
                }
            }
        }

        if ($runningTotal->isNegative()) {
            $runningTotal = new Money(0, $runningTotal->getCurrency());
        }

        $this->extend('updateTotal', $applyOrderAddOns, $applyOrderItemAddOns, $runningTotal);
        return DBPrice::create_field(DBPrice::INJECTOR_SPEC, $runningTotal);
    }

    /**
     * @param bool $applyItemAddOns
     * @return DBPrice
     */
    public function SubTotal(bool $applyItemAddOns = true): DBPrice
    {
        $money = new Money(0, $this->supportedCurrencies->getDefaultCurrency());

        foreach ($this->OrderItems() as $item) {
            $itemAmount = $applyItemAddOns
                ? $item->getTotal()->getMoney()
                : $item->getSubTotal()->getMoney();

            /*
             * If money is initial zero, we use item amount as base - this avoids assuming $itemAmount is in
             * default currency. $money(0)->add($itemAmount) would throw for non-default currency even if all items have
             * same currency.
             */
            $money = $money->isZero()
                ? $itemAmount
                : $money->add($itemAmount);
        }

        if ($money->isNegative()) {
            $money = new Money(0, $money->getCurrency());
        }

        $this->extend('updateSubTotal', $applyItemAddOns, $money);
        return DBPrice::create_field(DBPrice::INJECTOR_SPEC, $money);
    }

    /**
     * @return HasManyList|OrderStatusUpdate[]
     */
    public function CustomerVisibleOrderStatusUpdates(): HasManyList
    {
        return $this->OrderStatusUpdates()->filter('CustomerVisible', true);
    }

    /**
     * @param PurchasableInterface $item
     * @param int                  $quantity
     */
    public function setItemQuantity(PurchasableInterface $item, int $quantity = 1): void
    {
        if (!$this->IsMutable()) {
            throw new OrderLockedException($this);
        }

        $this->getOrderItem($item)->setQuantity($quantity);
    }

    /**
     * @return bool
     */
    public function IsMutable(): bool
    {
        return $this->IsCart && !$this->CartLockedAt;
    }

    /**
     * @param PurchasableInterface $item
     * @param bool                 $createIfMissing
     * @return null|OrderItem
     */
    public function getOrderItem(PurchasableInterface $item, bool $createIfMissing = true): ?OrderItem
    {
        $match = $this->OrderItems()
            ->filter(OrderItem::PURCHASABLE_CLASS, $item->ClassName)
            ->find(OrderItem::PURCHASABLE_ID, $item->ID);

        if ($match !== null || !$createIfMissing || !$this->IsMutable()) {
            return $match;
        }

        $orderItem = OrderItem::create();
        $orderItem->OrderID = $this->ID;
        $orderItem->setPurchasable($item)
            ->setQuantity(0, false);

        return $orderItem;
    }

    /**
     * @param PurchasableInterface $item
     * @param int                  $quantity
     * @return $this
     */
    public function addItem(PurchasableInterface $item, int $quantity = 1): self
    {
        if (!$this->IsMutable()) {
            throw new OrderLockedException($this);
        }

        $item = $this->getOrderItem($item);
        $item->setQuantity($item->getQuantity() + $quantity);
        return $this;
    }

    /**
     * @param int|OrderItem|PurchasableInterface $item OrderItem ID, OrderItem instance or PurchasableInterface instance.
     * @return $this
     */
    public function removeItem($item): self
    {
        if (!$this->IsMutable()) {
            throw new OrderLockedException($this);
        }

        if ($item instanceof PurchasableInterface) {
            $item = $this->getOrderItem($item, false);
            if ($item === null) {
                return $this;
            }
        }

        $itemID = is_int($item) ? $item : $item->ID;
        $this->OrderItems()->removeByID($itemID);

        return $this;
    }

    /**
     * @param PurchasableInterface $item
     * @param OrderItemAddOn       $addOn
     */
    public function attachPurchasableAddOn(PurchasableInterface $item, OrderItemAddOn $addOn): void
    {
        $orderItem = $this->getOrderItem($item, false);

        if ($orderItem !== null) {
            if (!$orderItem->IsMutable()) {
                throw new OrderLockedException($this);
            }

            $orderItem->OrderItemAddOns()->add($addOn);
        }
    }

    /**
     * @param PurchasableInterface $item
     * @param OrderItemAddOn       $addOn
     */
    public function detachPurchasableAddOn(PurchasableInterface $item, OrderItemAddOn $addOn): void
    {
        $orderItem = $this->getOrderItem($item, false);

        if ($orderItem !== null) {
            if (!$orderItem->IsMutable()) {
                throw new OrderLockedException($orderItem);
            }

            $orderItem->OrderItemAddOns()->remove($addOn);
        }
    }

    /**
     * @inheritDoc
     */
    public function canView($member = null)
    {
        return $this->extendedCan(__FUNCTION__, $member) ??
            Permission::checkMember($member, ShopPermissions::VIEW_ORDERS);
    }

    /**
     * @inheritDoc
     */
    public function canEdit($member = null)
    {
        return $this->extendedCan(__FUNCTION__, $member) ??
            ($this->IsMutable() && Permission::checkMember($member, ShopPermissions::EDIT_ORDERS));
    }

    /**
     * @inheritDoc
     */
    public function canDelete($member = null)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    /**
     * @param null|Member $member
     * @param string[]    $guestTokens
     * @return bool
     */
    public function canViewOrderPage(?Member $member = null, array $guestTokens = []): bool
    {
        if ($this->IsMutable()) {
            // No one should be able to view carts as an order
            return false;
        }

        $extendedCan = $this->extendedCan('canViewOrderPage', $member, [
            'guestTokens' => $guestTokens,
        ]);

        // Allow if extendedCan, admin or valid guest token
        return $extendedCan ?? (in_array($this->GuestToken, $guestTokens, true) ||
                Permission::checkMember($member, 'ADMIN'));
    }

    /**
     * Lock cart to prevent modifications.
     * @param bool $writeImmediately
     */
    public function Lock(bool $writeImmediately = true): void
    {
        if ($this->CartLockedAt) {
            return;
        }

        $this->CartLockedAt = DBDatetime::now()->getValue();
        if ($writeImmediately) {
            $this->write();
        }
    }

    /**
     * @return bool
     */
    public function Empty(): bool
    {
        return !$this->exists() || !boolval($this->OrderItems()->sum('Quantity'));
    }

    /**
     * @param Payment              $payment
     * @param null|ServiceResponse $response
     */
    public function paymentCaptured(Payment $payment, ?ServiceResponse $response = null): void
    {
        if (!$this->UnpaidTotal()->getMoney()->isPositive()) {
            $this->ConfirmationTime = DBDatetime::now()->getValue();
            $this->IsCart = false;
            $this->write();

            OrderConfirmationEmail::create($this)->send();
        }

        $this->extend('paymentCaptured', $payment, $response);
    }

    /**
     * @param null|Payment $payment
     */
    public function paymentCancelled(?Payment $payment): void
    {
        $this->Unlock();
        $this->extend('paymentCancelled', $payment);
    }

    /**
     * @param Payment              $payment
     * @param null|ServiceResponse $response
     */
    public function paymentError(Payment $payment, ?ServiceResponse $response = null): void
    {
        $this->Unlock();
        $this->extend('paymentError', $payment, $response);
    }

    /**
     * Unlock the cart to restore ability to modify.
     * @param bool $writeImmediately
     */
    public function Unlock(bool $writeImmediately = true): void
    {
        if (!$this->IsCart) {
            // If not cart, unlock should not be possible
            return;
        }

        $this->CartLockedAt = '';

        if ($writeImmediately) {
            $this->write();
        }
    }

    /**
     * @return string
     */
    public function Link(): string
    {
        if ($this->IsCart) {
            /** @var ViewCartPage $page */
            $page = ViewCartPage::get_one(ViewCartPage::class);
            return $page->Link();
        }

        /** @var ViewOrderPage $page */
        $page = ViewOrderPage::get_one(ViewOrderPage::class, ['ClassName' => ViewOrderPage::class]);
        $link = $page->LinkForOrder($this);

        $this->extend('updateLink', $link);
        return $link;
    }

    /**
     * @return string
     */
    public function AbsoluteLink(): string
    {
        return Director::absoluteURL($this->Link());
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        $data = [
            $this->ID,
        ];

        foreach ($this->OrderItems() as $item) {
            $data[] = [
                $item->ID,
                $item->Quantity,
                $item->BasePrice->getValue(),
                $item->Total->getValue(),
            ];

            foreach ($item->OrderItemAddOns() as $addOn) {
                $data[] = [
                    $addOn->ID,
                    $addOn->Priority,
                    $addOn->Amount->getValue(),
                    $addOn->isActive(),
                ];
            }
        }

        foreach ($this->OrderAddOns() as $addOn) {
            $data[] = [
                $addOn->ID,
                $addOn->Priority,
                $addOn->Amount->getValue(),
                $addOn->isActive(),
            ];
        }

        $this->extend('updateHashData', $data);
        return md5(json_encode($data));
    }

    /**
     * Convert to Omnipay payment data for a purchase.
     * @return array
     */
    public function toPaymentData(): array
    {
        $customerName = explode(' ', $this->CustomerName, 2);

        $data = [
            'firstName'       => $customerName[0],
            'lastName'        => $customerName[1] ?? '',
            'email'           => $this->CustomerEmail,
            'billingAddress1' => $this->BillingAddress->Unit,
            'billingAddress2' => $this->BillingAddress->Street,
            'billingCity'     => $this->BillingAddress->City,
            'billingPostcode' => $this->BillingAddress->Postcode,
            'billingState'    => $this->BillingAddress->Region,
            'billingCountry'  => $this->BillingAddress->Country,
        ];

        $this->extend('updatePaymentData', $data);
        return $data;
    }

    /**
     * If the order is locked, this forces queries to return relations as at the time the order was locked rather than
     * the current live items. For example, prices and coupons as at the time the user paid rather than potentially
     * changed live prices from now.
     * @return array
     */
    public function getVersionedQueryParams(): array
    {
        return $this->IsMutable()
            ? []
            : [
                'Versioned.mode'  => 'archive',
                'Versioned.date'  => $this->CartLockedAt,
                'Versioned.stage' => Versioned::get_stage() ?? Versioned::LIVE,
            ];
    }


    /**
     * Returns the real total quantity of the items in the cart
     *
     * @return int
     */
    public function getTotalQuantity()
    {
        return $this->OrderItems()->sum('Quantity') ?: 0;
    }
}
