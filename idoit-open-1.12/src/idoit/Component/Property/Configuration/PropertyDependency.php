<?php

namespace idoit\Component\Property\Configuration;

use idoit\Component\Property\Property;
use idoit\Component\Property\LegacyPropertyCreatorInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;

class PropertyDependency implements \ArrayAccess, LegacyPropertyCreatorInterface
{
    /**
     * @var string
     */
    protected $propkey;

    /**
     * @var array
     */
    protected $smartyParams;

    /**
     * @var string
     */
    protected $condition;

    /**
     * @var string
     */
    protected $conditionValue;

    /**
     * @var SelectSubSelect
     */
    protected $select;

    /**
     * @return string
     */
    public function getPropkey()
    {
        return $this->propkey;
    }

    /**
     * @param string $propkey
     */
    public function setPropkey($propkey)
    {
        $this->propkey = $propkey;
        return $this;
    }

    /**
     * @return array
     */
    public function getSmartyParams()
    {
        return $this->smartyParams;
    }

    /**
     * @param array $smartyParams
     */
    public function setSmartyParams($smartyParams)
    {
        $this->smartyParams = $smartyParams;
        return $this;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * @return string
     */
    public function getConditionValue()
    {
        return $this->conditionValue;
    }

    /**
     * @param string $conditionValue
     */
    public function setConditionValue($conditionValue)
    {
        $this->conditionValue = $conditionValue;
        return $this;
    }

    /**
     * @return SelectSubSelect
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @param SelectSubSelect $select
     */
    public function setSelect($select)
    {
        $this->select = $select;
        return $this;
    }

    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     *
     * @return PropertyDependency
     */
    public static function createInstanceFromArray(array $propertyArray = [])
    {
        $propertyData = new static();

        $propertyData->propkey = $propertyArray[Property::C__PROPERTY__DEPENDENCY__PROPKEY];
        $propertyData->smartyParams = $propertyArray[Property::C__PROPERTY__DEPENDENCY__SMARTYPARAMS];
        $propertyData->condition = $propertyArray[Property::C__PROPERTY__DEPENDENCY__CONDITION];
        $propertyData->conditionValue = $propertyArray[Property::C__PROPERTY__DEPENDENCY__CONDITION_VALUE];
        $propertyData->select = $propertyArray[Property::C__PROPERTY__DEPENDENCY__SELECT];

        return $propertyData;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        if ($offset === Property::C__PROPERTY__DEPENDENCY__SMARTYPARAMS) {
            return $this->smartyParams !== null;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION) {
            return $this->condition !== null;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__PROPKEY) {
            return $this->propkey !== null;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION_VALUE) {
            return $this->conditionValue !== null;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__SELECT) {
            return $this->select !== null;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if ($offset === Property::C__PROPERTY__DEPENDENCY__SMARTYPARAMS) {
            return $this->smartyParams;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION) {
            return $this->condition;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__PROPKEY) {
            return $this->propkey;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION_VALUE) {
            return $this->conditionValue;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__SELECT) {
            return $this->select;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === Property::C__PROPERTY__DEPENDENCY__SMARTYPARAMS) {
            $this->smartyParams = $value;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION) {
            $this->condition = $value;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__PROPKEY) {
            $this->propkey = $value;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION_VALUE) {
            $this->conditionValue = $value;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__SELECT) {
            $this->select = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        if ($offset === Property::C__PROPERTY__DEPENDENCY__SMARTYPARAMS) {
            unset($this->smartyParams);
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION) {
            unset($this->condition);
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__PROPKEY) {
            unset($this->propkey);
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION_VALUE) {
            unset($this->conditionValue);
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__SELECT) {
            unset($this->select);
        }
    }
}