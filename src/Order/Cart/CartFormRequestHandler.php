<?php
declare(strict_types=1);

namespace SwipeStripe\Order\Cart;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FormRequestHandler;

/**
 * Class CartFormRequestHandler
 * @package SwipeStripe\Order\Cart
 */
class CartFormRequestHandler extends FormRequestHandler
{
    const REMOVE_ITEM_ACTION = 'RemoveOrderItem';
    const REMOVE_ITEM_ARG = 'OrderItemID';

    /**
     * @var array
     */
    private static $allowed_actions = [
        'RemoveOrderItem',
    ];

    /**
     * @param array $data
     * @param CartFormInterface $form
     * @return HTTPResponse
     */
    public function UpdateCart(array $data, CartFormInterface $form): HTTPResponse
    {
        $form->saveInto($form->getCart());

        $this->extend('UpdateCart', $form, $data);
        return $form->getController()->redirectBack();
    }

    /**
     * @param array $data
     * @param CartFormInterface $form
     * @return HTTPResponse
     */
    public function RemoveOrderItem(array $data, CartFormInterface $form): HTTPResponse
    {
        $orderItemID = intval($data[static::REMOVE_ITEM_ARG] ?? 0);
        $form->getCart()->removeItem($orderItemID);

        $this->extend('RemoveOrderItem', $form, $data);
        return $form->getController()->redirectBack();
    }
}
