<?php
namespace idoit\Module\Multiedit\Component\Synchronizer;

use idoit\Exception\Exception;
use isys_import_handler_cmdb;

/**
 * @package     Modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class SynchronizerEntry extends AbstractSynchronizer implements SynchronizerInterface
{
    /**
     * @return $this|mixed
     */
    public function mapSyncData()
    {
        if (is_numeric($this->entryId)) {
            $this->syncData[self::ENTRY__DATA__ID] = $this->entryId;
        }

        foreach ($this->entryData as $attributeKey => $attributeValue) {
            $propertyKey = substr($attributeKey, strpos($attributeKey, '__') + 2);

            if (is_string($propertyKey) && isset($this->valueConverters[$propertyKey])) {
                $attributeValue = $this->valueConverters[$propertyKey]->convertValue($attributeValue);
            }

            $this->syncData[self::ENTRY__PROPERTIES][$propertyKey] = [C__DATA__VALUE => $attributeValue];
        }

        return $this;
    }

    /**
     * @return mixed|void
     * @throws \Exception
     */
    public function synchronize()
    {
        $this->validationErrors = [];

        if (is_numeric($this->entryId)) {
            // Update
            $type = isys_import_handler_cmdb::C__UPDATE;
        }

        if ($this->entryId === 'new') {
            // Create
            $type = isys_import_handler_cmdb::C__CREATE;
        }
        $this->merger->merge($this);

        $syncData = $this->getSyncData();

        $validation = $this->categoryDao->validate($syncData['properties']);

        try {
            // @see  ID-4910 ID-4867  Moved up to only validate properties from the UI.
            // $l_validation = $p_cat_dao->validate($l_category_data['properties']);

            if ($validation !== true) {
                throw new \isys_exception_validation(\isys_application::instance()->container->get('language')
                    ->get('LC__VALIDATION_ERROR'), $validation, $syncData['data_id']);
            }

            $syncValue = $this->categoryDao->sync($syncData, $this->getObjectId(), $type);
            $this->synchronizeSuccess = true;

            \isys_component_signalcollection::get_instance()
                ->emit('mod.cmdb.afterCategoryEntrySave', $this->categoryDao, $syncValue, true, $this->objectId, $syncData, []);
        } catch (\isys_exception_validation $validationException) {
            $this->synchronizeSuccess = false;

            $properties = $this->merger->getProperties();

            foreach ($validationException->get_validation_errors() as $key => $message) {
                if (isset($properties[$key])) {
                    $this->validationErrors[] = [
                        'obj_id'       => $this->getObjectId(),
                        'value'        => $syncData[Synchronizer::ENTRY__PROPERTIES][$key][C__DATA__VALUE],
                        'prop_ui_id'   => get_class($this->categoryDao) . '__' . $key . '[' . $this->getObjectId() . '-' . $this->getEntryId() . '-' . $this->getEntryKey() . ']',
                        'message'      => $message,
                        'cat_entry_id' => $validationException->get_cat_entry_id()
                    ];
                }
            }
        }
    }
}
