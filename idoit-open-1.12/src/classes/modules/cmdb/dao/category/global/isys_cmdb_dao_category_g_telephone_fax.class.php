<?php

/**
 * i-doit
 *
 * DAO: global category for telephone/fax
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_telephone_fax extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'telephone_fax';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function properties()
    {
        return [
            'type'             => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TELEPHONE_FAX__TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Type'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_telephone_fax_list__isys_telephone_fax_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_telephone_fax_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_telephone_fax_type',
                        'isys_telephone_fax_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('isys_telephone_fax_type__title',
                        'isys_telephone_fax_type'),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_telephone_fax_list', 'LEFT', 'isys_catg_telephone_fax_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_telephone_fax_type', 'LEFT',
                            'isys_catg_telephone_fax_list__isys_telephone_fax_type__id', 'isys_telephone_fax_type__id')
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__TELEPHONE_FAX__TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_telephone_fax_type'
                    ]
                ]
            ]),
            'telephone_number' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TELEPHONE_FAX__TELEPHONE_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Telephone number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_telephone_fax_list__telephone_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__TELEPHONE_FAX__TELEPHONE_NUMBER'
                ]
            ]),
            'fax_number'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TELEPHONE_FAX__FAX_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Fax number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_telephone_fax_list__fax_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__TELEPHONE_FAX__FAX_NUMBER'
                ]
            ]),
            'extension'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TELEPHONE_FAX__EXTENSION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Extension'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_telephone_fax_list__extension'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__TELEPHONE_FAX__EXTENSION'
                ]
            ]),
            'pincode'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TELEPHONE_FAX__PINCODE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Pin-Code'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_telephone_fax_list__pincode'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__TELEPHONE_FAX__PINCODE'
                ]
            ]),
            'imei'             => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TELEPHONE_FAX__IMEI',
                    C__PROPERTY__INFO__DESCRIPTION => 'Pin-Code'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_telephone_fax_list__imei'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__TELEPHONE_FAX__IMEI'
                ]
            ]),
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_telephone_fax_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__TELEPHONE_FAX', 'C__CATG__TELEPHONE_FAX')
                ]
            ])
        ];
    }
}
