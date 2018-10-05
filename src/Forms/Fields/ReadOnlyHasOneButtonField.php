<?php
declare(strict_types=1);

namespace SwipeStripe\Forms\Fields;

use SilverShop\HasOneField\GridFieldHasOneUnlinkButton;
use SilverShop\HasOneField\HasOneButtonField;
use SilverStripe\ORM\ArrayList;

/**
 * Class ReadOnlyHasOneButtonField
 * @package SwipeStripe\Forms\Fields
 */
class ReadOnlyHasOneButtonField extends HasOneButtonField
{
    /**
     * ReadOnlyHasOneButtonField constructor.
     * @param HasOneButtonField $original
     */
    public function __construct(HasOneButtonField $original)
    {
        parent::__construct($original->getParent(), $original->getRelation(), $original->getName(), $original->Title());
    }

    /**
     * @inheritDoc
     */
    public function performReadonlyTransformation()
    {
        $this->getConfig()->removeComponentsByType([
            GridFieldHasOneUnlinkButton::class,
        ]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getList()
    {
        return ArrayList::create([
            $this->record,
        ]);
    }
}
