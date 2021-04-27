<?php

/**
 * i-doit
 *
 * DAO: specific category for monitors.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_monitor extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'monitor';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Dynamic property handling for getting the formatted CPU data.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_size($p_row)
    {
        global $g_comp_database;

        $l_monitor_row = isys_cmdb_dao_category_s_monitor::instance($g_comp_database)
            ->get_data(null, $p_row['isys_obj__id'])
            ->get_row();

        return isys_convert::measure($l_monitor_row['isys_cats_monitor_list__display'], $l_monitor_row['isys_depth_unit__const'], C__CONVERT_DIRECTION__BACKWARD) . ' ' .
            isys_application::instance()->container->get('language')
                ->get($l_monitor_row['isys_depth_unit__title']);
    }

    /**
     * Creates new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  mixed  Returns created entity's identifier (int) or false (bool).
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function create_data($p_data)
    {
        $p_data['size'] = isys_convert::measure($p_data['size'], $p_data['size_unit']);

        return parent::create_data($p_data);
    }

    /**
     * Abstract method for retrieving the dynamic properties of this category dao.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_properties()
    {
        return [
            '_size' => [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__MONITOR_DISPLAY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Display'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__id'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        $this,
                        'dynamic_property_callback_size'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ]
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'size'        => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__MONITOR_DISPLAY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Display'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_monitor_list__display',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(ROUND(isys_cats_monitor_list__display / isys_depth_unit__factor), \' \', isys_depth_unit__title)
                            FROM isys_cats_monitor_list
                            INNER JOIN isys_depth_unit ON isys_depth_unit__id = isys_cats_monitor_list__isys_depth_unit__id', 'isys_cats_monitor_list',
                        'isys_cats_monitor_list__id', 'isys_cats_monitor_list__isys_obj__id'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_monitor_list', 'LEFT', 'isys_cats_monitor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_depth_unit', 'LEFT', 'isys_cats_monitor_list__isys_depth_unit__id',
                            'isys_depth_unit__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__MONITOR_DISPLAY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium',
                    ],
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['measure']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'size_unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'size_unit'   => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__MONITOR_UNIT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Unit'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_monitor_list__isys_depth_unit__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_depth_unit',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_depth_unit',
                        'isys_depth_unit__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('isys_depth_unit__title', 'isys_depth_unit'),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_monitor_list', 'LEFT', 'isys_cats_monitor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_depth_unit', 'LEFT', 'isys_cats_monitor_list__isys_depth_unit__id',
                            'isys_depth_unit__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__MONITOR_UNIT',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_depth_unit',
                        'p_strClass' => 'input-mini',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'type'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__MONITOR_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_monitor_list__isys_monitor_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_monitor_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_monitor_type',
                        'isys_monitor_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('isys_monitor_type__title', 'isys_monitor_type'),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_monitor_list', 'LEFT', 'isys_cats_monitor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_monitor_type', 'LEFT', 'isys_cats_monitor_list__isys_monitor_type__id',
                            'isys_monitor_type__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__MONITOR_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_monitor_type'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'resolution'  => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__MONITOR_RESOLUTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Resolution'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_monitor_list__isys_monitor_resolution__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_monitor_resolution',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_monitor_resolution',
                        'isys_monitor_resolution__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('isys_monitor_resolution__title',
                        'isys_monitor_resolution'),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_monitor_list', 'LEFT', 'isys_cats_monitor_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_monitor_resolution', 'LEFT', 'isys_cats_monitor_list__isys_monitor_resolution__id',
                            'isys_monitor_resolution__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__MONITOR_RESOLUTION',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_monitor_resolution'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'pivot'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__MONITOR_PIVOT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Pivot?'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_monitor_list__pivot',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('(CASE WHEN isys_cats_monitor_list__pivot = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                                    WHEN isys_cats_monitor_list__pivot = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)', 'isys_cats_monitor_list'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_monitor_list', 'LEFT', 'isys_cats_monitor_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATS__MONITOR_PIVOT',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_arData'     => get_smarty_arr_YES_NO(),
                        'p_bDbFieldNN' => 1
                    ],
                    C__PROPERTY__UI__DEFAULT => 0
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
            'speaker'     => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__MONITOR_SPEAKER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Speaker?'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_monitor_list__speaker',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('(CASE WHEN isys_cats_monitor_list__speaker = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                                    WHEN isys_cats_monitor_list__speaker = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)', 'isys_cats_monitor_list'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_monitor_list', 'LEFT', 'isys_cats_monitor_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATS__MONITOR_SPEAKER',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_arData'     => get_smarty_arr_YES_NO(),
                        'p_bDbFieldNN' => 1
                    ],
                    C__PROPERTY__UI__DEFAULT => 0
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
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_monitor_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__MONITOR', 'C__CATS__MONITOR')
                ]
            ])
        ];
    }

    /**
     * Updates existing entity.
     *
     * @param   integer $p_category_data_id Entity's identifier
     * @param   array   $p_data             Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  boolean
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        $p_data['size'] = isys_convert::measure($p_data['size'], $p_data['size_unit']);

        return parent::save_data($p_category_data_id, $p_data);
    }
}
