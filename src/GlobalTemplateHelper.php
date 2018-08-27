<?php

namespace SwipeStripe;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\View\TemplateGlobalProvider;
use SilverStripe\View\ViewableData;

/**
 * $SwipeStripe global template helper.
 * @package SwipeStripe
 */
class GlobalTemplateHelper extends ViewableData implements TemplateGlobalProvider
{
    use Configurable;
    use Injectable;

    /**
     * @config
     * @var string
     */
    private static $template_helper_name = 'SwipeStripe';

    /**
     * @var array
     */
    private static $dependencies = [
        'cart' => CartInterface::class,
    ];

    /**
     * @var CartInterface
     */
    public $cart;

    /**
     * @inheritDoc
     */
    public static function get_template_global_variables(): array
    {
        $name = static::config()->get('template_helper_name');

        return [
            /** @see GlobalTemplateHelper::singleton() */
            $name => 'singleton',
        ];
    }

    /**
     * @return CartInterface
     */
    public function ActiveCart(): CartInterface
    {
        return $this->cart->getActiveCart();
    }
}
