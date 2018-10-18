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
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\Relation;
use SilverStripe\ORM\UnsavedRelationList;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Versioned\Versioned;
use SwipeStripe\CMSHelper;
use SwipeStripe\Forms\Fields\AlwaysModifiableGridField;
use SwipeStripe\Order\Cart\ViewCartPage;
use SwipeStripe\Order\OrderItem\OrderItem;
use SwipeStripe\Order\OrderItem\OrderItemAddOn;
use SwipeStripe\Order\Status\OrderStatus;
use SwipeStripe\Order\Status\OrderStatusUpdate;
use SwipeStripe\ORM\FieldType\DBAddress;
use SwipeStripe\Price\DBPrice;
use SwipeStripe\Price\PriceField;
use SwipeStripe\Price\SupportedCurrencies\SupportedCurrenciesInterface;
use SwipeStripe\ShopPermissions;

/**
 * Class Order
 * @package SwipeStripe\Order
 * @property bool $IsCart
 * @property string $CartLockedAt
 * @property string $GuestToken
 * @property string $Hash
 * @property string $CustomerName
 * @property string $CustomerEmail
 * @property DBAddress $BillingAddress
 * @property string $ConfirmationTime
 * @property string $Status
 * @method HasManyList|OrderStatusUpdate[] OrderStatusUpdates()
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
            $result->addFieldError('CartLockedAt',
                'Non-cart order must be locked to a certain time.');
        }

        return $result;
    }

    /**
     * @inheritDoc
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
            'tabbed' => true,
            'ajaxSafe' => true,
            'fieldClasses' => [
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
        return strlen($token) === 2 * static::GUEST_TOKEN_BYTES;
    }

    /**
     * @return DBPrice
     */
    public function UnpaidTotal(): DBPrice
    {
        $cartTotalMoney = $this->Total()->getMoney();
        $dueMoney = $cartTotalMoney->subtract($this->TotalPaid()->getMoney());

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
            /** @var OrderAddOn $addOn */
            foreach ($this->OrderAddOns() as $addOn) {
                $runningTotal = $runningTotal->add($addOn->getAmount()->getMoney());
            }
        }

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
     * @param int $quantity
     */
    public function setItemQuantity(PurchasableInterface $item, int $quantity = 1): void
    {
        if (!$this->IsMutable()) {
            throw new \BadMethodCallException("Can't change items on locked Order {$this->ID}.");
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
     * @param bool $createIfMissing
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
     * @param int $quantity
     * @return $this
     */
    public function addItem(PurchasableInterface $item, int $quantity = 1): self
    {
        if (!$this->IsMutable()) {
            throw new \BadMethodCallException("Can't change items on locked Order {$this->ID}.");
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
            throw new \BadMethodCallException("Can't change items on locked Order {$this->ID}.");
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
     * @param OrderItemAddOn $addOn
     */
    public function attachPurchasableAddOn(PurchasableInterface $item, OrderItemAddOn $addOn): void
    {
        $orderItem = $this->getOrderItem($item, false);

        if ($orderItem !== null) {
            if (!$orderItem->IsMutable()) {
                throw new \BadMethodCallException("Can't change add-ons on locked OrderItem {$orderItem->ID}.");
            }

            $orderItem->OrderItemAddOns()->add($addOn);
        }
    }

    /**
     * @param PurchasableInterface $item
     * @param OrderItemAddOn $addOn
     */
    public function detachPurchasableAddOn(PurchasableInterface $item, OrderItemAddOn $addOn): void
    {
        $orderItem = $this->getOrderItem($item, false);

        if ($orderItem !== null) {
            if (!$orderItem->IsMutable()) {
                throw new \BadMethodCallException("Can't change add-ons on locked OrderItem {$orderItem->ID}.");
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
     * @param string[] $guestTokens
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
     */
    public function Lock(): void
    {
        if ($this->CartLockedAt) {
            return;
        }

        $this->CartLockedAt = DBDatetime::now()->getValue();
        $this->write();
    }

    /**
     * @return bool
     */
    public function Empty(): bool
    {
        return !$this->exists() || !boolval($this->OrderItems()->sum('Quantity'));
    }

    /**
     * @param Payment $payment
     * @param ServiceResponse $response
     */
    public function paymentCaptured(Payment $payment, ServiceResponse $response): void
    {
        $this->ConfirmationTime = DBDatetime::now();
        $this->IsCart = false;
        $this->write();

        if (!$this->UnpaidTotal()->getMoney()->isPositive()) {
            OrderConfirmationEmail::create($this)->send();
        }
    }

    /**
     * @param null|Payment $payment
     */
    public function paymentCancelled(?Payment $payment): void
    {
        $this->Unlock();
    }

    /**
     * Unlock the cart to restore ability to modify.
     */
    public function Unlock(): void
    {
        if (!$this->IsCart) {
            // If not cart, unlock should not be possible
            return;
        }

        $this->CartLockedAt = null;
        $this->write();
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
        return $page->LinkForOrder($this);
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
                $item->Price,
                $item->Total,
            ];

            foreach ($item->OrderItemAddOns() as $addOn) {
                $data[] = [
                    $addOn->ID,
                    $addOn->Priority,
                    $addOn->Amount,
                ];
            }
        }

        foreach ($this->OrderAddOns() as $addOn) {
            $data[] = [
                $addOn->ID,
                $addOn->Priority,
                $addOn->Amount,
            ];
        }

        return md5(json_encode($data));
    }

    /**
     * Convert to Omnipay payment data for a purchase.
     * @return array
     */
    public function toPaymentData(): array
    {
        $customerName = explode(' ', $this->CustomerName, 2);

        return [
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
     * @return UnsavedRelationList|HasManyList|OrderItem[]
     */
    public function OrderItems(): Relation
    {
        $orderItems = $this->getComponents('OrderItems');

        // Don't call on UnsavedRelationList
        if ($orderItems instanceof DataList) {
            $orderItems = $orderItems->setDataQueryParam($this->getVersionedQueryParams());
        }

        return $orderItems;
    }

    /**
     * @return UnsavedRelationList|HasManyList|OrderAddOn[]
     */
    public function OrderAddOns(): Relation
    {
        $orderAddOns = $this->getComponents('OrderAddOns');

        // Don't call on UnsavedRelationList
        if ($orderAddOns instanceof DataList) {
            $orderAddOns = $orderAddOns->setDataQueryParam($this->getVersionedQueryParams());
        }

        return $orderAddOns;
    }
}
