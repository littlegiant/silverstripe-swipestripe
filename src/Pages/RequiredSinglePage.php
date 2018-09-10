<?php
declare(strict_types=1);

namespace SwipeStripe\Pages;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Member;
use SilverStripe\Versioned\Versioned;

/**
 * Trait RequiredSinglePage
 * @package SwipeStripe\Pages
 * @mixin \Page
 */
trait RequiredSinglePage
{
    /**
     * No type hints as signature must be compatible with parent definition in DataObject.
     * @see DataObject::canCreate()
     * @param Member|null $member
     * @param array $context
     * @return bool
     */
    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    /**
     * No type hints as signature must be compatible with parent definition in DataObject.
     * @see DataObject::canDelete()
     * @param Member|null $member
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return false;
    }

    /**
     * @see Versioned::canUnpublish()
     * @param Member|null $member
     * @return bool
     */
    public function canUnpublish(?Member $member = null): bool
    {
        return false;
    }

    /**
     * @see DataObject::requireDefaultRecords()
     * @see SiteTree::requireDefaultRecords()
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        if (DataObject::get_one(static::class, ['ClassName' => static::class]) === null) {
            $page = $this->getRequiredSinglePage();
            $page->write();

            DB::alteration_message(ClassInfo::shortName($page) . ' created', 'created');
        }
    }

    /**
     * Create and return the default record for this page.
     * @return SiteTree
     */
    protected function getRequiredSinglePage(): SiteTree
    {
        $defaultTitle = ClassInfo::shortName(static::class);

        // Strip page / holder from end
        if (substr($defaultTitle, -4) === 'Page') {
            $defaultTitle = substr($defaultTitle, 0, -4);
        } elseif (substr($defaultTitle, -6) === 'Holder') {
            $defaultTitle = substr($defaultTitle, 0, -6);
        }

        $page = static::create();
        $page->Title = $page->Title ?? $defaultTitle;

        return $page;
    }
}
