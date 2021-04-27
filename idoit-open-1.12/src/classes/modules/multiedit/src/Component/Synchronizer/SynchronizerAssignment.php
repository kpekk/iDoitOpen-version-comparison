<?php

namespace idoit\Module\Multiedit\Component\Synchronizer;

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * @package     Modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class SynchronizerAssignment extends AbstractSynchronizer implements SynchronizerInterface
{
    /**
     * @return $this|mixed
     * @throws \idoit\Exception\JsonException
     */
    public function mapSyncData()
    {
        $this->syncData[self::ENTRY__DATA__ID] = null;
        $ids = current($this->entryData);
        $this->entryKey = key($this->entryData);

        $this->entryData[$this->entryKey] = (\isys_format_json::is_json($ids) ? \isys_format_json::decode($ids) : $ids);

        return $this;
    }

    /**
     * @return mixed|void
     * @throws \isys_exception_database
     * @throws \isys_exception_validation
     */
    public function synchronize()
    {
        if ($this->categoryDao instanceof ObjectBrowserReceiver) {
            $key = str_replace('__', '::', $this->entryKey);
            if (empty($this->entryChanges[$key]['to']) && count($this->entryData[$this->entryKey])) {
                $language = \isys_application::instance()->container->get('language');
                $changes = [];
                foreach ($this->entryData[$this->entryKey] as $objId) {
                    $objectData = $this->categoryDao->get_object($objId)->get_row();
                    $changes[] = $language->get($objectData['isys_obj_type__title']) . ' >> ' . $objectData['isys_obj__title'];
                }
                $this->entryChanges[$key]['to'] = implode(',', $changes);
            }

            $this->categoryDao->attachObjects($this->objectId, $this->entryData[$this->entryKey]);
            $this->synchronizeSuccess = true;
        } else {
            $propertyKey = substr($this->entryKey, strpos($this->entryKey, '__') + 2);
            foreach ($this->entryData[$this->entryKey] as $objectId) {
                $this->syncData[self::ENTRY__PROPERTIES][$propertyKey] = $objectId;

                $this->categoryDao->sync($this->syncData, $this->getObjectId(), isys_import_handler_cmdb::C__CREATE);
            }
        }
    }
}
