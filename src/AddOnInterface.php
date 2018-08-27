<?php

namespace SwipeStripe;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;

/**
 * Interface AddOnInterface
 * @package SwipeStripe
 * @mixin DataObject
 */
interface AddOnInterface extends DataObjectInterface
{
    const PRIORITY_EARLY = -1;
    const PRIORITY_NORMAL = 0;
    const PRIORITY_LATE = 1;

    /**
     * Comparator callable to pass to usort() for sorting add ons by priority. Call as usort(AddOnInterface[], COMPARATOR_FUNCTION)
     * @see addOnInterfaceComparator()
     * @see usort()
     */
    const COMPARATOR_FUNCTION = __NAMESPACE__ . '\\addOnInterfaceComparator';

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return int
     */
    public function getPriority(): int;
}

/**
 * @param AddOnInterface $a
 * @param AddOnInterface $b
 * @return int
 */
function addOnInterfaceComparator(AddOnInterface $a, AddOnInterface $b) {
    return $a->getPriority() <=> $b->getPriority();
}
