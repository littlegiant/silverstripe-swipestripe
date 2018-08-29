<?php
declare(strict_types=1);

namespace SwipeStripe;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\View\TemplateGlobalProvider;
use SilverStripe\View\ViewableData;
use SwipeStripe\Order\Order;

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
     * Expose a $SwipeStripe (or other configured name) template variable that exposes template data without polluting
     * the global scope.
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
     * @return Order
     */
    public function ActiveCart(): Order
    {
        /** @var Controller|HasActiveCart $controller */
        $controller = Controller::curr();
        return $controller->getActiveCart();
    }
}
