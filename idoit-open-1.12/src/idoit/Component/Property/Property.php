<?php

namespace idoit\Component\Property;

use idoit\Component\Property\Configuration\PropertyCheck;
use idoit\Component\Property\Configuration\PropertyData;
use idoit\Component\Property\Configuration\PropertyDependency;
use idoit\Component\Property\Configuration\PropertyFormat;
use idoit\Component\Property\Configuration\PropertyInfo;
use idoit\Component\Property\Configuration\PropertyProvides;
use idoit\Component\Property\Configuration\PropertyUi;

class Property implements \ArrayAccess, LegacyPropertyInterface, LegacyPropertyCreatorInterface
{
    const MAPPING = [
        Property::C__PROPERTY__FORMAT => [
            'property' => 'format',
            'type' => PropertyFormat::class
        ],
        Property::C__PROPERTY__INFO => [
            'property' => 'info',
            'type' => PropertyInfo::class
        ],
        Property::C__PROPERTY__DATA => [
            'property' => 'data',
            'type' => PropertyData::class
        ],
        Property::C__PROPERTY__CHECK => [
            'property' => 'check',
            'type' => PropertyCheck::class
        ],
        Property::C__PROPERTY__UI => [
            'property' => 'ui',
            'type' => PropertyUi::class
        ],
        Property::C__PROPERTY__PROVIDES => [
            'property' => 'provides',
            'type' => PropertyProvides::class
        ],
        Property::C__PROPERTY__DEPENDENCY => [
            'property' => 'dependency',
            'type' => PropertyDependency::class
        ]
    ];

    /**
     * @var PropertyFormat
     */
    protected $format;

    /**
     * @var PropertyInfo
     */
    protected $info;

    /**
     * @var PropertyData
     */
    protected $data;

    /**
     * @var PropertyCheck
     */
    protected $check;

    /**
     * @var PropertyUi
     */
    protected $ui;

    /**
     * @var PropertyProvides
     */
    protected $provides;

    /**
     * @var PropertyDependency
     */
    protected $dependency;

    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     *
     * @return Property
     */
    public static function createInstanceFromArray(array $propertyArray = [])
    {
        $property = new static();

        $property->info = PropertyInfo::createInstanceFromArray($propertyArray[self::C__PROPERTY__INFO] ?: []);
        $property->data = PropertyData::createInstanceFromArray($propertyArray[self::C__PROPERTY__DATA] ?: []);
        $property->check = PropertyCheck::createInstanceFromArray($propertyArray[self::C__PROPERTY__CHECK] ?: []);
        $property->format = PropertyFormat::createInstanceFromArray($propertyArray[self::C__PROPERTY__FORMAT] ?: []);
        $property->ui = PropertyUi::createInstanceFromArray($propertyArray[self::C__PROPERTY__UI] ?: []);
        $property->provides = PropertyProvides::createInstanceFromArray($propertyArray[self::C__PROPERTY__PROVIDES] ?: []);
        $property->dependency = PropertyDependency::createInstanceFromArray($propertyArray[self::C__PROPERTY__DEPENDENCY] ?: []);

        return $property;
    }

    /**
     * @return PropertyFormat
     */
    public function &getFormat()
    {
        return $this->format;
    }

    /**
     * @param PropertyFormat $format
     *
     * @return Property
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return PropertyInfo
     */
    public function &getInfo()
    {
        return $this->info;
    }

    /**
     * @param PropertyInfo $info
     *
     * @return Property
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * @return PropertyData
     */
    public function &getData()
    {
        return $this->data;
    }

    /**
     * @param PropertyData $data
     *
     * @return Property
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return PropertyCheck
     */
    public function &getCheck()
    {
        return $this->check;
    }

    /**
     * @param PropertyCheck $check
     *
     * @return Property
     */
    public function setCheck($check)
    {
        $this->check = $check;

        return $this;
    }

    /**
     * @return PropertyUi
     */
    public function &getUi()
    {
        return $this->ui;
    }

    /**
     * @param PropertyUi $ui
     *
     * @return Property
     */
    public function setUi($ui)
    {
        $this->ui = $ui;

        return $this;
    }

    /**
     * @return PropertyProvides
     */
    public function &getProvides()
    {
        return $this->provides;
    }

    /**
     * @param PropertyProvides $provides
     *
     * @return Property
     */
    public function setProvides($provides)
    {
        $this->provides = $provides;

        return $this;
    }

    /**
     * @return PropertyDependency
     */
    public function &getDependency(){
        return $this->dependency;
    }

    /**
     * @param PropertyDependency $dependency
     *
     * @return $this
     */
    public function setDependency($dependency) {
        $this->dependency = $dependency;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        if (property_exists($this, $offset)) {
            return (is_a($this->{static::MAPPING[$offset]['property']}, static::MAPPING[$offset]['type']));
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return (property_exists($this, static::MAPPING[$offset]['property']) ? $this->{static::MAPPING[$offset]['property']} : null);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($value instanceof $this->{static::MAPPING[$offset]['type']}) {
            $this->{static::MAPPING[$offset]['property']} = $value;
        } else {
            $this->{static::MAPPING[$offset]['property']} = call_user_func([
                static::MAPPING[$offset]['type'],
                'createInstanceFromArray'
            ], $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        if (property_exists($this, static::MAPPING[$offset]['property'])) {
            unset($this->{static::MAPPING[$offset]['property']});
        }
    }
}
