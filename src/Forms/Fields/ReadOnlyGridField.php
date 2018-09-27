<?php
declare(strict_types=1);

namespace SwipeStripe\Forms\Fields;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldImportButton;

/**
 * Class ReadOnlyGridField
 * @package SwipeStripe\Forms\Fields
 */
class ReadOnlyGridField extends GridField
{
    /**
     * ReadOnlyGridField constructor.
     * @param GridField $original
     */
    public function __construct(GridField $original)
    {
        parent::__construct($original->getName(),
            $original->Title(),
            $original->getList(),
            $original->getConfig());
    }

    /**
     * @inheritDoc
     */
    public function performReadonlyTransformation()
    {
        $this->getConfig()->removeComponentsByType([
            GridFieldAddExistingAutocompleter::class,
            GridFieldAddNewButton::class,
            GridFieldDeleteAction::class,
            GridFieldImportButton::class,
        ]);

        return $this;
    }
}
