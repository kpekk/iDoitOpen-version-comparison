<?php

use League\Csv\Writer;

/**
 * i-doit Report Manager View
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   Copyright 2012 - synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_report_view_devices_in_location_detailed extends isys_report_view
{
    /**
     * Class cache
     *
     * @var  array
     */
    private $m_class_cache = [];

    /**
     * File directory
     *
     * @var  string
     */
    private $m_filename = '';

    /**
     * All helpers for dynamic fields
     *
     * @var  array
     */
    private $m_dynamic_headers = [];

    /**
     * Table header
     *
     * @var  array
     */
    private $m_headers = [];

    /**
     * All helpers from properties
     *
     * @var  array
     */
    private $m_helpers = [];

    /**
     * Main SQL
     *
     * @var  string
     */
    private $m_sql = '';

    /**
     * Properties.
     *
     * @var  array
     */
    private $m_view_properties = [];

    /**
     * @return string
     */
    public static function name()
    {
        return 'LC__REPORT__VIEW__DEVICES_IN_LOCATION_AS_LIST';
    }

    /**
     * @return string
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__DEVICES_IN_LOCATION_DESCRIPTION__DETAILLED';
    }

    /**
     * @return string
     */
    public function template()
    {
        return isys_module_report::getPath() . 'templates/view_devices_in_location_detailed.tpl';
    }

    /**
     * @return string
     */
    public static function viewtype()
    {
        return 'LC__CMDB__OBJTYPE__CATG';
    }

    /**
     * Helper method for location path.
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_location_path($p_obj_id)
    {
        $l_loc_popup = new isys_popup_browser_location();

        return $l_loc_popup->format_selection($p_obj_id);
    }

    /**
     * Helper method for translations.
     *
     * @param   $p_lc
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function translate_value($p_lc)
    {
        return isys_application::instance()->container->get('language')
            ->get($p_lc);
    }

    /**
     * Helper method for dialog fields
     *
     * @param $p_params
     * @param $p_id
     *
     * @return string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function dialog($p_params, $p_id)
    {
        $l_return = '';

        if ($p_id > 0) {
            $l_table = $p_params[0][0];
            $l_query = 'SELECT ' . $l_table . '__title FROM ' . $l_table . ' WHERE ' . $l_table . '__id = ' . $p_id;
            $l_result = $this->database->query($l_query);
            $l_return = isys_application::instance()->container->get('language')
                ->get(array_shift($this->database->fetch_row_assoc($l_result)));
        }

        return $l_return;
    }

    /**
     * @throws Exception
     */
    public function start()
    {
        // Preparing some variables.
        $l_arr = [];
        $l_objtypes = [];

        $l_dao = isys_cmdb_dao::instance($this->database);
        $l_objtype_res = $l_dao->get_object_types_by_properties();

        if ($l_objtype_res->num_rows() > 0) {
            while ($l_objtype_row = $l_objtype_res->get_row()) {
                $l_objtypes[$l_objtype_row['isys_obj_type__id']] = $this->language->get($l_objtype_row['isys_obj_type__title']);
            }
        }

        asort($l_objtypes);

        $l_obj_types = $l_dao->get_object_types();
        while ($l_row = $l_obj_types->get_row()) {
            $l_arr[] = [
                'id'  => $l_row['isys_obj_type__id'],
                'val' => $this->language->get($l_row['isys_obj_type__title'])
            ];
        }

        $l_rules = [
            'C__OBJECT_TYPES'               => ['p_arData' => $l_objtypes],
            'C__VIEW__OBJTYPE__DIALOG_LIST' => ['p_arData' => $l_arr]
        ];

        // Finally assign the data to the template.
        $this->template
            ->activate_editmode()
            ->assign('ajax_url', isys_glob_add_to_query('ajax', 1))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->assign('download_link', 'temp/' . $this->m_filename);
    }

    /**
     * @throws \idoit\Exception\JsonException
     */
    public function ajax_request()
    {
        $l_return = [];
        $l_type_filter = [];
        $l_object_type_filter = $_POST['objTypeFilter'] ?: $_GET['objTypeFilter'];
        $l_location_object_id = $_POST['obj_id'] ?: $_GET['obj_id'];

        $this->build_properties();

        $l_extended_properties = isys_component_signalcollection::get_instance()
            ->emit("mod.report.views.view_devices_in_location_detailed.extend_report_properties");

        if (isys_format_json::is_json($l_object_type_filter)) {
            $l_type_filter = isys_format_json::decode($l_object_type_filter);
        }

        if (count($l_extended_properties) > 0) {
            foreach ($l_extended_properties as $l_property_type_item) {
                foreach ($l_property_type_item as $l_category_type_id => $l_categories) {
                    foreach ($l_categories as $l_category_id => $l_properties) {
                        if ($l_category_type_id == C__CMDB__CATEGORY__TYPE_GLOBAL) {
                            $this->m_view_properties[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_category_id] = $l_properties;
                        } elseif ($l_category_type_id == C__CMDB__CATEGORY__TYPE_SPECIFIC) {
                            $this->m_view_properties[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_category_id] = $l_properties;
                        }
                    }
                }
            }
        }

        $this->build_query_by_properties();

        if (is_null($l_location_object_id) || empty($l_location_object_id)) {
            $l_obj_id = defined_or_default('C__OBJ__ROOT_LOCATION');
        } else {
            $l_obj_id = $l_location_object_id;
        }

        $l_objects = isys_cmdb_dao_location::instance($this->database)
            ->get_child_locations_recursive($l_obj_id);

        $l_in = '';

        foreach ($l_objects as $l_object) {
            // We want to go sure we get no corrupted data.
            if ($l_object['isys_obj__id'] > 0) {
                $l_in .= $l_object['isys_obj__id'] . ',';
            }
        }

        if ($l_in != '') {
            $l_in = rtrim($l_in, ',');
            $this->m_sql .= ' WHERE main.isys_obj__id IN (' . $l_in . ') ';

            if (count($l_type_filter) > 0) {
                $this->m_sql .= ' AND main.isys_obj__isys_obj_type__id IN (' . implode(',', $l_type_filter) . ') ';
            }

            $this->m_sql .= 'ORDER BY isys_catg_location_list__parentid';
        } else {
            header('Content-Type: application/json');

            echo isys_format_json::encode(false);
            die();
        }

        foreach (array_keys($this->m_headers) as $l_val) {
            $l_return['headLine'][$l_val] = $this->language->get($l_val);
        }

        $l_result = $this->database->query($this->m_sql);
        if ($this->database->num_rows($l_result) > 0) {
            while ($l_row = $this->database->fetch_row_assoc($l_result)) {
                foreach ($l_row as $l_lc => $l_value) {
                    if (array_key_exists($l_lc, $this->m_helpers)) {
                        $l_callback = $this->m_helpers[$l_lc];
                        $l_helper = explode('::', array_shift($l_callback));
                        $l_class = $l_helper[0];
                        $l_method = $l_helper[1];
                        if (class_exists($l_class)) {
                            if (isset($this->m_class_cache[$l_class])) {
                                $l_class_obj = $this->m_class_cache[$l_class];
                            } else {
                                $this->m_class_cache[$l_class] = new $l_class($this->database);
                                $l_class_obj = $this->m_class_cache[$l_class];
                            }

                            if (method_exists($l_class_obj, $l_method)) {
                                if (count($l_callback) > 0) {
                                    $l_row[$l_lc] = call_user_func_array([$l_class_obj, $l_method], [$l_callback, $l_row[$l_lc]]);
                                } else {
                                    $l_row[$l_lc] = call_user_func_array([$l_class_obj, $l_method], [$l_row[$l_lc]]);
                                }
                            }
                        }
                    } else {
                        if ($l_value == '') {
                            $l_row[$l_lc] = '';
                        } else {
                            $l_row[$l_lc] = $l_value;
                        }
                    }
                }

                if (count($this->m_dynamic_headers) > 0) {
                    foreach ($this->m_dynamic_headers as $l_lc => $l_callback) {
                        $l_callback_arr = explode('::', $l_callback[0]);
                        $l_class = $l_callback_arr[0];
                        $l_method = $l_callback_arr[1];

                        if (isset($l_callback[1])) {
                            $l_parameters = [];
                            foreach ($l_callback[1] as $l_row_field) {
                                $l_parameters[] = $l_row[$l_row_field];
                            }
                        } else {
                            $l_parameters = null;
                        }

                        if (class_exists($l_class)) {
                            $l_class_obj = new $l_class($this->database);
                            if (method_exists($l_class_obj, $l_method)) {
                                $l_row[$l_lc] = call_user_func_array([$l_class_obj, $l_method], $l_parameters);
                            }
                        }
                    }
                }

                $l_return['lineValues'][] = $l_row;
            }
        } else {
            header('Content-Type: application/json');

            echo isys_format_json::encode(false);
            die();
        }

        if ($_GET['export'] == 1) {
            $this->generate_csv_file($l_return['headLine'], $l_return['lineValues']);
        }

        header('Content-Type: application/json');

        echo isys_format_json::encode($l_return);
        die();
    }

    /**
     * Property builder
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function build_properties()
    {
        $this->m_view_properties = [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => filter_array_by_keys_of_defined_constants([
                'C__CATG__GLOBAL'   => [
                    'table'      => 'isys_catg_global_list',
                    'obj_id'     => 'isys_catg_global_list__isys_obj__id',
                    'properties' => [
                        'LC__CMDB__OBJTYPE'            => 'isys_obj_type__title',
                        'LC_UNIVERSAL__OBJECT'         => 'main.isys_obj__title',
                        'LC__CMDB__CATG__GLOBAL_SYSID' => 'main.isys_obj__sysid'
                    ],
                    'helper'     => [
                        'LC__CMDB__OBJTYPE' => ['isys_report_view_devices_in_location_detailed::translate_value']
                    ]
                ],
                'C__CATG__LOCATION' => [
                    'table'      => 'isys_catg_location_list',
                    'obj_id'     => 'isys_catg_location_list__isys_obj__id',
                    'join'       => [
                        'join_type'  => 'INNER',
                        'join_field' => 'isys_catg_location_list__parentid',
                        'table'      => 'isys_obj',
                        'field'      => 'isys_obj__id',
                        'alias'      => 'loc'
                    ],
                    'properties' => [
                        'LC__CMDB__OBJTYPE__LOCATION' => 'main.isys_obj__id'
                    ],
                    'helper'     => [
                        'LC__CMDB__OBJTYPE__LOCATION' => ['isys_report_view_devices_in_location_detailed::get_location_path']
                    ]
                ]
            ]),
            C__CMDB__CATEGORY__TYPE_SPECIFIC => []
        ];
    }

    /**
     * Method which builds main query by properties
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function build_query_by_properties()
    {
        $l_sql = '';

        foreach ($this->m_view_properties as $l_category_type_id => $l_categories) {
            foreach ($l_categories as $l_category_id => $l_properties) {
                if (isset($l_properties['table'])) {
                    $l_table = $l_properties['table'];
                    $l_obj_field = $l_properties['obj_id'];

                    $l_sql .= 'LEFT JOIN ' . $l_table . ' ON ' . $l_obj_field . ' = main.isys_obj__id ';

                    if (isset($l_properties['join'])) {
                        $l_sql .= $l_properties['join']['join_type'] . ' JOIN ' . $l_properties['join']['table'] . ' AS ' . $l_properties['join']['alias'] . ' ON ';
                        $l_sql .= $l_properties['join']['join_field'] . ' = ' . $l_properties['join']['alias'] . '.' . $l_properties['join']['field'] . ' ';
                    }

                    foreach ($l_properties['properties'] as $l_property_key => $l_property_info) {
                        $this->m_headers[$l_property_key] = $l_property_info;
                    }

                    if (isset($l_properties['helper'])) {
                        foreach ($l_properties['helper'] as $l_field => $l_helper) {
                            $this->m_helpers[$l_field] = $l_helper;
                        }
                    }
                } else {
                    // Dynamic property.
                    foreach ($l_properties['properties'] as $l_property_key => $l_property_info) {
                        $this->m_dynamic_headers[$l_property_key] = $l_property_info;
                    }

                    if (isset($l_properties['helper'])) {
                        foreach ($l_properties['helper'] as $l_field => $l_helper) {
                            $this->m_helpers[$l_field] = $l_helper;
                        }
                    }
                }
            }
        }

        $l_selection = '*';

        if (count($this->m_headers) > 0) {
            $l_selection = '';
            foreach ($this->m_headers as $l_field_alias => $l_field) {
                $l_selection .= $l_field . ' AS ' . $l_field_alias . ',';
            }

            $l_selection = rtrim($l_selection, ',');
        }

        $this->m_headers = array_merge_recursive($this->m_headers, $this->m_dynamic_headers);

        $l_sql_main = 'SELECT ' . $l_selection . ', main.isys_obj__id AS __objid__ FROM isys_obj AS main ' .
            'INNER JOIN isys_obj_type ON main.isys_obj__isys_obj_type__id = isys_obj_type__id ' . $l_sql;

        $this->m_sql = $l_sql_main;
    }

    /**
     * @param $p_header
     * @param $p_content
     */
    private function generate_csv_file($p_header, $p_content)
    {
        $l_csv = Writer::createFromFileObject(new SplTempFileObject)
            ->setDelimiter(isys_tenantsettings::get('system.csv-export-delimiter', ';'))
            ->setOutputBOM(Writer::BOM_UTF8)
            ->insertOne(array_values($p_header));

        $this->m_filename = isys_application::instance()->container->get('session')->get_user_id() . "-csv_export_report_view_devices_in_location.csv";

        foreach ($p_content as $l_row) {
            $l_csv->insertOne(array_map('trim', array_map('strip_tags', array_map('isys_helper_textformat::remove_scripts', array_values($l_row)))));
        }

        $l_csv->output($this->m_filename);
        die;
    }
}
