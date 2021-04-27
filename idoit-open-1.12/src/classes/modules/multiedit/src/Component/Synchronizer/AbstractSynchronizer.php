<?php

namespace idoit\Module\Multiedit\Component\Synchronizer;

use idoit\Module\Multiedit\Component\Synchronizer\Category\ConvertInterface;

abstract class AbstractSynchronizer
{
    const ENTRY__DATA__ID   = 'data_id';
    const ENTRY__PROPERTIES = 'properties';

    /**
     * @var int
     */
    protected $objectId;

    /**
     * @var int|string
     */
    protected $entryId;

    /**
     * @var string
     */
    protected $entryKey;

    /**
     * @var array
     */
    protected $entryData;

    /**
     * @var array
     */
    protected $entryChanges;

    /**
     * @var bool
     */
    protected $synchronizeSuccess = false;

    /**
     * @var array
     */
    protected $validationErrors = [];

    /**
     * @var Merger
     */
    protected $merger;

    /**
     * @var \isys_cmdb_dao_category
     */
    protected $categoryDao;

    /**
     * @var array
     */
    protected $syncData = [];

    /**
     * @var ConvertInterface[]
     */
    protected $valueConverters = [];

    /**
     * @return mixed
     */
    public function getEntryChanges()
    {
        return $this->entryChanges;
    }

    /**
     * @param mixed $entryChanges
     *
     * @return AbstractSynchronizer
     */
    public function setEntryChanges($entryChanges)
    {
        $this->entryChanges = $entryChanges;

        return $this;
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    /**
     * @return bool
     */
    public function isSynchronizeSuccess()
    {
        return $this->synchronizeSuccess;
    }

    /**
     * @param \isys_cmdb_dao_category $dao
     *
     * @return AbstractSynchronizer
     */
    public function setCategoryDao($dao)
    {
        $this->categoryDao = $dao;

        return $this;
    }

    /**
     * @param Merger $merger
     *
     * @return AbstractSynchronizer
     */
    public function setMerger($merger)
    {
        $this->merger = $merger;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSyncData()
    {
        return $this->syncData;
    }

    /**
     * @param mixed $syncData
     *
     * @return AbstractSynchronizer
     */
    public function setSyncData($syncData)
    {
        $this->syncData = $syncData;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     *
     * @return AbstractSynchronizer
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * @return int|string
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * @param int|string $entryId
     *
     * @return AbstractSynchronizer
     */
    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntryKey()
    {
        return $this->entryKey;
    }

    /**
     * @param string $entryKey
     *
     * @return AbstractSynchronizer
     */
    public function setEntryKey($entryKey)
    {
        $this->entryKey = $entryKey;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntryData()
    {
        return $this->entryData;
    }

    /**
     * @param $entryData
     *
     * @return AbstractSynchronizer
     */
    public function setEntryData($entryData)
    {
        $this->entryData = $entryData;

        return $this;
    }

    /**
     * @return AbstractSynchronizer
     */
    public function reset()
    {
        $this->syncData = [];

        return $this;
    }

    /**
     * @return $this
     */
    public function setConverter()
    {
        $category = ucfirst($this->categoryDao->get_category());
        $properties = array_keys($this->categoryDao->get_properties());

        switch ($this->categoryDao->get_category_type()) {
            case defined_or_default('C__CMDB__CATEGORY__TYPE_GLOBAL'):
                $categoryType = 'G';
                break;
            case defined_or_default('C__CMDB__CATEGORY__TYPE_SPECIFIC'):
                $categoryType = 'S';
                break;
            case defined_or_default('C__CMDB__CATEGORY__TYPE_CUSTOM'):
                $categoryType = 'Custom';
                $category = str_replace(' ', '', ucwords(str_replace('_', ' ', $category)));
                break;
            default:
                $categoryType = null;
                break;
        }

        if ($categoryType === null) {
            return $this;
        }

        foreach ($properties as $propKey) {
            if ($categoryType === 'Custom') {
                $className = ucfirst(substr($propKey, 2, strpos($propKey, '_c_') - 2));
            } else {
                $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $propKey)));
            }
            $class = 'idoit\\Module\\Multiedit\\Component\\Synchronizer\\Category\\' . $categoryType . '\\' . $category . '\\' . $className;

            if (class_exists($class)) {
                $this->valueConverters[$propKey] = new $class;
            }
        }

        return $this;
    }
}
