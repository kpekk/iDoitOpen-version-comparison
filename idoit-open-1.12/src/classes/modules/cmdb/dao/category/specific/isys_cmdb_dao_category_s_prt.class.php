<?php

/**
 * i-doit
 *
 * DAO: specific category for printers.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_prt extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'prt';

    /**
     * Category entry is purgable.
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'type'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PRT_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Type'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_prt_list__isys_cats_prt_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_cats_prt_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_cats_prt_type',
                        'isys_cats_prt_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('isys_cats_prt_type__title', 'isys_cats_prt_type'),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_prt_list', 'LEFT', 'isys_cats_prt_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_prt_type', 'LEFT', 'isys_cats_prt_list__isys_cats_prt_type__id',
                            'isys_cats_prt_type__id')
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID      => 'C__CATS__PRT_TYPE',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_strTable'   => 'isys_cats_prt_type',
                        'p_bDbFieldNN' => '1',
                    ],
                    C__PROPERTY__UI__DEFAULT => defined_or_default('C__CATS_PRT_TYPE__OTHER')
                ]
            ]),
            'is_color'     => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PRT_ISCOLOR',
                    C__PROPERTY__INFO__DESCRIPTION => 'Color'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_prt_list__iscolor',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('(CASE WHEN isys_cats_prt_list__iscolor = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                                    WHEN isys_cats_prt_list__iscolor = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)', 'isys_cats_prt_list'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_prt_list', 'LEFT', 'isys_cats_prt_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATS__PRT_ISCOLOR',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_arData'     => get_smarty_arr_YES_NO(),
                        'p_bDbFieldNN' => '1',
                    ],
                    C__PROPERTY__UI__DEFAULT => '0'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'is_duplex'    => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PRT_ISDUPLEX',
                    C__PROPERTY__INFO__DESCRIPTION => 'Duplex'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_prt_list__isduplex',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('(CASE
                                    WHEN isys_cats_prt_list__isduplex = \'1\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                                    WHEN isys_cats_prt_list__isduplex = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . '
                                END)', 'isys_cats_prt_list'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_prt_list', 'LEFT', 'isys_cats_prt_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATS__PRT_ISDUPLEX',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_arData'     => get_smarty_arr_YES_NO(),
                        'p_bDbFieldNN' => '1',
                    ],
                    C__PROPERTY__UI__DEFAULT => '0'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'emulation'    => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PRT_EMULATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Emulation'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_prt_list__isys_cats_prt_emulation__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_cats_prt_emulation',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_cats_prt_emulation',
                        'isys_cats_prt_emulation__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('isys_cats_prt_emulation__title',
                        'isys_cats_prt_emulation'),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_prt_list', 'LEFT', 'isys_cats_prt_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_prt_emulation', 'LEFT', 'isys_cats_prt_list__isys_cats_prt_emulation__id',
                            'isys_cats_prt_emulation__id')
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID      => 'C__CATS__PRT_EMULATION',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_strTable'   => 'isys_cats_prt_emulation',
                        'p_bDbFieldNN' => '1',
                    ],
                    C__PROPERTY__UI__DEFAULT => defined_or_default('C__CATS_PRT_EMULATION__OTHER')
                ]
            ]),
            'paper_format' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PRT_PAPER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Paper format'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_prt_list__isys_cats_prt_paper__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_cats_prt_paper',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_cats_prt_paper',
                        'isys_cats_prt_paper__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('isys_cats_prt_paper__title', 'isys_cats_prt_paper'),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_prt_list', 'LEFT', 'isys_cats_prt_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_prt_paper', 'LEFT', 'isys_cats_prt_list__isys_cats_prt_paper__id',
                            'isys_cats_prt_paper__id')
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATS__PRT_PAPER',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_cats_prt_paper',
                        'p_bDbFieldNN' => '0',
                    ]
                ]
            ]),
            'description'  => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_prt_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__PRT', 'C__CATS__PRT')
                ]
            ])
        ];
    }
}
