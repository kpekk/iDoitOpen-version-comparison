<?php

namespace idoit\Component\Property\Configuration;

use idoit\Component\Property\Exception\UnknownTypeException;
use idoit\Component\Property\LegacyPropertyCreatorInterface;
use idoit\Component\Property\Property;
use Symfony\Component\HttpFoundation\ParameterBag;

class PropertyUi implements \ArrayAccess, LegacyPropertyCreatorInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * One of C__PROPERTY__UI__TYPE__*
     *
     * @var string
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @var string
     */
    protected $placeholder;

    /**
     * @var string
     */
    protected $emptyMessage;

    // C__PROPERTY__UI__PARAMS START
    /*protected $table;
    protected $data;
    protected $identifier;
    protected $fieldNotNullable;
    protected $categoryFilter;
    protected $cssClass;
    protected $disableInputGroup;
    protected $infoIconSpacer;
    protected $paramPlaceholder;
    protected $paramDefault;
    protected $jsOnChange;
    protected $paramEmptyMessage;
    protected $multiselection;
    protected $inputGroupMarginClass;
    protected $popupType;
    protected $retrieveDataFunction;*/
    // C__PROPERTY__UI__PARAMS END

    protected $params;

    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array  $propertyArray
     *
     * @return PropertyUi
     *
     * @throws UnknownTypeException
     */
    public static function createInstanceFromArray(array $propertyArray = [])
    {
        if (
            !defined('C__PROPERTY__UI__TYPE__' . strtoupper(ltrim($propertyArray[Property::C__PROPERTY__UI__TYPE], 'f_')))
        ) {
            throw new UnknownTypeException('Unknown type: ' . 'C__PROPERTY__UI__TYPE__' . strtoupper($propertyArray[Property::C__PROPERTY__UI__TYPE]));
        }

        $propertyUi = new static();

        $propertyUi->id = $propertyArray[Property::C__PROPERTY__UI__ID];
        $propertyUi->default = $propertyArray[Property::C__PROPERTY__UI__DEFAULT];
        $propertyUi->placeholder = $propertyArray[Property::C__PROPERTY__UI__PLACEHOLDER];
        $propertyUi->emptyMessage = $propertyArray[Property::C__PROPERTY__UI__EMPTYMESSAGE];
        $propertyUi->type = $propertyArray[Property::C__PROPERTY__UI__TYPE];
        $propertyUi->params = $propertyArray[Property::C__PROPERTY__UI__PARAMS];

        /*
        $propertyUi->table = $propertyArray[C__PROPERTY__UI__PARAMS]['p_strTable'];
        $propertyUi->data = $propertyArray[C__PROPERTY__UI__PARAMS]['p_arData'];
        $propertyUi->identifier = $propertyArray[C__PROPERTY__UI__PARAMS]['identifier'];
        $propertyUi->fieldNotNullable = $propertyArray[C__PROPERTY__UI__PARAMS]['p_bDbFieldNN'];
        $propertyUi->categoryFilter = $propertyArray[C__PROPERTY__UI__PARAMS]['catFilter'];
        $propertyUi->cssClass = $propertyArray[C__PROPERTY__UI__PARAMS]['p_strClass'];
        $propertyUi->disableInputGroup = $propertyArray[C__PROPERTY__UI__PARAMS]['disableInputGroup'];
        $propertyUi->infoIconSpacer = $propertyArray[C__PROPERTY__UI__PARAMS]['p_bInfoIconSpacer'];
        $propertyUi->paramPlaceholder = $propertyArray[C__PROPERTY__UI__PARAMS]['p_strPlaceholder'];
        $propertyUi->paramDefault = $propertyArray[C__PROPERTY__UI__PARAMS]['default'];
        $propertyUi->jsOnChange = $propertyArray[C__PROPERTY__UI__PARAMS]['p_onChange'];
        $propertyUi->paramEmptyMessage = $propertyArray[C__PROPERTY__UI__PARAMS]['emptyMessage'];
        $propertyUi->multiselection = $propertyArray[C__PROPERTY__UI__PARAMS]['multiselection'];
        $propertyUi->inputGroupMarginClass = $propertyArray[C__PROPERTY__UI__PARAMS]['inputGroupMarginClass'];
        $propertyUi->popupType = $propertyArray[C__PROPERTY__UI__PARAMS]['p_strPopupType'];
        $propertyUi->retrieveDataFunction = $propertyArray[C__PROPERTY__UI__PARAMS]['dataretrieval'];
        */

        return $propertyUi;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return PropertyUi
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return PropertyUi
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     *
     * @return PropertyUi
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @param string $placeholder
     *
     * @return PropertyUi
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmptyMessage()
    {
        return $this->emptyMessage;
    }

    /**
     * @param string $emptyMessage
     *
     * @return PropertyUi
     */
    public function setEmptyMessage($emptyMessage)
    {
        $this->emptyMessage = $emptyMessage;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     *
     * @return PropertyUi
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        if ($offset === Property::C__PROPERTY__UI__ID) {
            return $this->id !== null;
        }

        if ($offset === Property::C__PROPERTY__UI__DEFAULT) {
            return $this->default !== null;
        }

        if ($offset === Property::C__PROPERTY__UI__PLACEHOLDER) {
            return $this->placeholder !== null;
        }

        if ($offset === Property::C__PROPERTY__UI__EMPTYMESSAGE) {
            return $this->emptyMessage !== null;
        }

        if ($offset === Property::C__PROPERTY__UI__TYPE) {
            return $this->type !== null;
        }

        if ($offset === Property::C__PROPERTY__UI__PARAMS) {
            return $this->params !== null;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if ($offset === Property::C__PROPERTY__UI__ID) {
            return $this->id;
        }

        if ($offset === Property::C__PROPERTY__UI__DEFAULT) {
            return $this->default;
        }

        if ($offset === Property::C__PROPERTY__UI__PLACEHOLDER) {
            return $this->placeholder;
        }

        if ($offset === Property::C__PROPERTY__UI__EMPTYMESSAGE) {
            return $this->emptyMessage;
        }

        if ($offset === Property::C__PROPERTY__UI__TYPE) {
            return $this->type;
        }

        if ($offset === Property::C__PROPERTY__UI__PARAMS) {
            return $this->params;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === Property::C__PROPERTY__UI__ID) {
            $this->id = $value;
        }

        if ($offset === Property::C__PROPERTY__UI__DEFAULT) {
            $this->default = $value;
        }

        if ($offset === Property::C__PROPERTY__UI__PLACEHOLDER) {
            $this->placeholder = $value;
        }

        if ($offset === Property::C__PROPERTY__UI__EMPTYMESSAGE) {
            $this->emptyMessage = $value;
        }

        if ($offset === Property::C__PROPERTY__UI__TYPE) {
            $this->type = $value;
        }

        if ($offset === Property::C__PROPERTY__UI__PARAMS) {
            $this->params = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        if ($offset === Property::C__PROPERTY__UI__ID) {
            unset($this->id);
        }

        if ($offset === Property::C__PROPERTY__UI__DEFAULT) {
            unset($this->default);
        }

        if ($offset === Property::C__PROPERTY__UI__PLACEHOLDER) {
            unset($this->placeholder);
        }

        if ($offset === Property::C__PROPERTY__UI__EMPTYMESSAGE) {
            unset($this->emptyMessage);
        }

        if ($offset === Property::C__PROPERTY__UI__TYPE) {
            unset($this->type);
        }

        if ($offset === Property::C__PROPERTY__UI__PARAMS) {
            unset($this->params);
        }
    }
}
