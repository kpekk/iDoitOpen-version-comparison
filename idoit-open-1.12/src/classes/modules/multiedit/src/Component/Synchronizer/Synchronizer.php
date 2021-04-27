<?php

namespace idoit\Module\Multiedit\Component\Synchronizer;

use idoit\Component\Property\Property;
use idoit\Exception\Exception;
use isys_import_handler_cmdb;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * @package     Modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Synchronizer
{
    const ENTRY__DATA__ID = 'data_id';
    const ENTRY__PROPERTIES = 'properties';

    /**
     * @var []
     */
    protected $data = [];

    /**
     * @var []
     */
    protected $dataChanges = [];

    /**
     * @var Property[]
     */
    protected $properties;

    /**
     * @var \isys_cmdb_dao_category
     */
    protected $categoryDao;

    /**
     * @var bool
     */
    protected $multivalued = false;

    /**
     * @param mixed $data
     *
     * @return Synchronizer
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param Property[] $properties
     *
     * @return Synchronizer
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @param \isys_cmdb_dao_category $categoryDao
     *
     * @return Synchronizer
     */
    public function setCategoryDao($categoryDao)
    {
        $this->categoryDao = $categoryDao;

        return $this;
    }

    /**
     * @param bool $multivalued
     *
     * @return Synchronizer
     */
    public function setMultivalued($multivalued)
    {
        $this->multivalued = $multivalued;

        return $this;
    }

    /**
     * @param mixed $dataChanges
     *
     * @return Synchronizer
     */
    public function setDataChanges($dataChanges)
    {
        $this->dataChanges = $dataChanges;

        return $this;
    }

    /**
     *
     */
    public function handle()
    {
        $merger = (new Merger())->setCategoryDao($this->categoryDao)
            ->setProperties($this->properties)
            ->mapPropertiesToMerge();

        /**
         * @var $synchronizer AbstractSynchronizer
         */
        $synchronizer = null;
        $changes = [];
        $properties = $this->properties;
        unset($properties['description']);

        \idoit\Module\Cmdb\Search\Index\Signals::instance()
            ->disconnectOnAfterCategoryEntrySave();
        $starttime = microtime(true);

        foreach ($this->data as $dataKey => $dataValue) {
            list($objectId, $entryId, $entryKey) = explode('-', $dataKey);

            if ($synchronizer === null) {
                if ((!$entryId && !$entryKey) || ($this->categoryDao instanceof ObjectBrowserReceiver && count($properties) === 1)) {
                    // Assignment Category
                    $synchronizer = new SynchronizerAssignment();
                } else {
                    $synchronizer = new SynchronizerEntry();
                }

                $synchronizer
                    ->setCategoryDao($this->categoryDao)
                    ->setConverter()
                    ->setMerger($merger);
            }

            try {
                $synchronizer
                    ->reset()
                    ->setObjectId($objectId)
                    ->setEntryId($entryId)
                    ->setEntryKey($entryKey)
                    ->setEntryData($dataValue)
                    ->setEntryChanges($this->dataChanges[$dataKey])
                    ->mapSyncData()
                    ->synchronize();

                if ($synchronizer->isSynchronizeSuccess()) {
                    if ($synchronizer->getEntryChanges() !== $this->dataChanges[$dataKey]) {
                        $this->dataChanges[$dataKey] = $synchronizer->getEntryChanges();
                    }

                    $changes[$objectId][] = $this->dataChanges[$dataKey];
                }
            } catch (\Exception $e) {
                // Do nothing
            }
        }

        if (count($changes)) {
            $logbookEventConstant = 'C__LOGBOOK_EVENT__CATEGORY_CHANGED';
            $language = \isys_application::instance()->container->get('language');

            foreach ($changes as $objectId => $change) {
                if (!$this->multivalued) {
                    $change = current($change);
                }

                if ((bool)\isys_tenantsettings::get('logbook.changes', '1')) {
                    $compressedChanges = serialize($change);
                } else {
                    $compressedChanges = '';
                }

                /* Create the logbook entry after object change */
                \isys_event_manager::getInstance()->triggerCMDBEvent(
                    $logbookEventConstant,
                    '',
                    $objectId,
                    $this->categoryDao->get_objTypeID($objectId),
                    $language->get($this->categoryDao->getCategoryTitle()),
                    $compressedChanges,
                    ''
                );

                // Update object
                $this->categoryDao->update_object(
                    $objectId,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    microtime(true)
                );
            }

            \idoit\Module\Cmdb\Search\Index\Signals::instance()->onMultiEditSaved($this->categoryDao, [], $changes);
        }
    }
}
