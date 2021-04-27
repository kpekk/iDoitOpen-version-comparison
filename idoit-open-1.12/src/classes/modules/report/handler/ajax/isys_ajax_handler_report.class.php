<?php

use idoit\Module\Report\SqlQuery\Placeholder\Placeholder;

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-8
 */
class isys_ajax_handler_report extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [];

        if (isset($_GET['func'])) {
            $l_method = $_GET['func'];
            if (method_exists($this, $l_method)) {
                $l_return = $this->$l_method();
            }
        }

        echo isys_format_json::encode($l_return);
        $this->_die();
    }

    /**
     * This method is used for the ajax pagination of the reports.
     *
     * @global  isys_component_database $g_comp_database_system
     * @global  isys_component_database $g_comp_database
     * @global  integer                 $g_page_limit
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function ajax_pager()
    {
        global $g_comp_database_system;

        $l_row = isys_report_dao::instance($g_comp_database_system)
            ->get_report($_GET['report_id']);

        $l_query = stripslashes($l_row["isys_report__query"]);

        // First we modify the SQL to find out, with how many rows we are dealing...
        $l_preloadable_rows = isys_glob_get_pagelimit() * ((int)isys_usersettings::get('gui.lists.preload-pages', 30));
        $l_offset = $l_preloadable_rows * $_POST['offset_block'];

        if (strpos($l_query, 'LIMIT')) {
            return [];
        }

        $l_query = rtrim($l_query, ';') . ' LIMIT ' . $l_offset . ', ' . $l_preloadable_rows . ';';

        return isys_module_report::get_instance()
            ->process_show_report($l_query, null, true);
    }

    /**
     * Method which deletes report categories
     *
     * @return array
     */
    protected function delete_report_category()
    {
        global $g_comp_database_system;

        $l_return = [
            'error'   => false,
            'message' => null
        ];

        /**
         * @var isys_report_dao
         */
        $l_report_dao = isys_report_dao::instance(isys_application::instance()->database_system);

        if (count($l_report_dao->get_reports_by_category($_POST['id'])) === 0) {
            $l_report_dao->delete_report_category($_POST['id']);
            $l_return['message'] = isys_application::instance()->container->get('language')
                ->get('LC__REPORT__POPUP__REPORT_CATEGORIES__CONFIRMATION_SUCCESS');
        } else {
            $l_return['error'] = true;
            $l_return['message'] = isys_application::instance()->container->get('language')
                ->get('LC__REPORT__POPUP__REPORT_CATEGORIES__CONFIRMATION_ERROR');
        }

        return $l_return;
    }

    protected function get_report_category()
    {
        return current(isys_report_dao::instance(isys_application::instance()->database_system)
            ->get_report_categories($_POST['id']));
    }

    /**
     * Method to retrieve all the categories.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function add_division()
    {
        $l_return = [
            'error'   => false,
            'message' => null
        ];

        $l_dao = new isys_cmdb_dao($this->m_database_component);

        $l_blacklist_categories = [
            'C__CATS__NET_IP_ADDRESSES'
        ];

        try {
            $l_sql = "SELECT child.isysgui_catg__title, child.isysgui_catg__id, child.isysgui_catg__const, parent.isysgui_catg__title AS parent " .
                "FROM isys_property_2_cat " . "INNER JOIN isysgui_catg AS child ON isys_property_2_cat__isysgui_catg__id = child.isysgui_catg__id " .
                "LEFT JOIN isysgui_catg AS parent ON parent.isysgui_catg__id = child.isysgui_catg__parent " . "WHERE isys_property_2_cat__prop_provides & " .
                C__PROPERTY__PROVIDES__REPORT . " " . "AND isys_property_2_cat__prop_type = " . C__PROPERTY_TYPE__STATIC . " " . "GROUP BY isysgui_catg__id;";

            $l_res = $l_dao->retrieve($l_sql);

            while ($l_row = $l_res->get_row()) {
                if (!in_array($l_row['isysgui_catg__const'], $l_blacklist_categories)) {
                    $l_title = isys_application::instance()->container->get('language')
                        ->get($l_row['isysgui_catg__title']);
                    if ($l_row['parent'] !== null) {
                        $l_title .= ' (' . isys_application::instance()->container->get('language')
                                ->get($l_row['parent']) . ')';
                    }
                    $l_return['data']['catg'][$l_row['isysgui_catg__const']] = $l_title;
                }
            }

            $l_sql = "SELECT isysgui_cats__id, isysgui_cats__title, isysgui_cats__const FROM isys_property_2_cat " .
                "INNER JOIN isysgui_cats ON isys_property_2_cat__isysgui_cats__id = isysgui_cats__id " . "WHERE isys_property_2_cat__prop_provides & " .
                C__PROPERTY__PROVIDES__REPORT . " " . "AND isys_property_2_cat__prop_type = " . C__PROPERTY_TYPE__STATIC . " " . "GROUP BY isysgui_cats__id;";
            $l_res = $l_dao->retrieve($l_sql);

            while ($l_row = $l_res->get_row()) {
                if (!in_array($l_row['isysgui_cats__const'], $l_blacklist_categories)) {
                    $l_parent = '';
                    $l_parent_arr = [];
                    // Check parent
                    $l_check_parent_sql = 'SELECT isysgui_cats__title, isysgui_cats__id FROM isysgui_cats ' .
                        'INNER JOIN isysgui_cats_2_subcategory ON isysgui_cats_2_subcategory__isysgui_cats__id__parent = isysgui_cats__id ' .
                        'WHERE isysgui_cats_2_subcategory__isysgui_cats__id__child = ' . $l_dao->convert_sql_id($l_row['isysgui_cats__id']);

                    $l_res2 = $l_dao->retrieve($l_check_parent_sql);
                    if (count($l_res2) > 0) {
                        $l_parent_arr = [];

                        while ($l_row2 = $l_res2->get_row()) {
                            $l_check_objtypes = 'SELECT isys_obj_type__title FROM isys_obj_type WHERE isys_obj_type__isysgui_cats__id = ' .
                                $l_dao->convert_sql_id($l_row2['isysgui_cats__id']);
                            $l_res3 = $l_dao->retrieve($l_check_objtypes);

                            while ($l_row3 = $l_res3->get_row()) {
                                $l_title = isys_application::instance()->container->get('language')
                                    ->get($l_row3['isys_obj_type__title']);
                                $l_parent_arr[$l_title] = $l_title;
                            }
                        }
                    } else {
                        $l_check_objtypes = 'SELECT isys_obj_type__title FROM isys_obj_type WHERE isys_obj_type__isysgui_cats__id = ' .
                            $l_dao->convert_sql_id($l_row['isysgui_cats__id']);
                        $l_res3 = $l_dao->retrieve($l_check_objtypes);

                        while ($l_row3 = $l_res3->get_row()) {
                            $l_title = isys_application::instance()->container->get('language')
                                ->get($l_row3['isys_obj_type__title']);
                            $l_parent_arr[$l_title] = $l_title;
                        }
                    }
                    if (count($l_parent_arr) > 0) {
                        $l_parent = ' (' . implode(', ', $l_parent_arr) . ')';
                    }

                    $l_return['data']['cats'][$l_row['isysgui_cats__const']] = isys_application::instance()->container->get('language')
                            ->get($l_row['isysgui_cats__title']) . $l_parent;
                }
            }

            $l_sql = "SELECT isysgui_catg_custom__id, isysgui_catg_custom__title, isysgui_catg_custom__const FROM isys_property_2_cat " .
                "INNER JOIN isysgui_catg_custom ON isys_property_2_cat__isysgui_catg_custom__id = isysgui_catg_custom__id " . "WHERE isys_property_2_cat__prop_provides & " .
                C__PROPERTY__PROVIDES__REPORT . " " . "AND isys_property_2_cat__prop_type = " . C__PROPERTY_TYPE__STATIC . " " . "GROUP BY isysgui_catg_custom__id;";
            $l_res = $l_dao->retrieve($l_sql);

            while ($l_row = $l_res->get_row()) {
                $l_return['data']['catg_custom'][$l_row['isysgui_catg_custom__const']] = isys_application::instance()->container->get('language')
                    ->get($l_row['isysgui_catg_custom__title']);
            }

            if (is_array($l_return['data']['catg'])) {
                asort($l_return['data']['catg']);
                $l_return['data']['catg'] = array_flip($l_return['data']['catg']);
            }

            if (is_array($l_return['data']['cats'])) {
                asort($l_return['data']['cats']);
                $l_return['data']['cats'] = array_flip($l_return['data']['cats']);
            }

            if (is_array($l_return['data']['catg_custom'])) {
                asort($l_return['data']['catg_custom']);
                $l_return['data']['catg_custom'] = array_flip($l_return['data']['catg_custom']);
            }
        } catch (Exception $e) {
            $l_return['error'] = true;
            $l_return['message'] = $e->getMessage();
        }

        return $l_return;
    }

    /**
     * Method to retrieve the properties of a given category.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function add_property_selection_to_division()
    {
        $l_dao = new isys_cmdb_dao_category_property($this->m_database_component);

        $l_return = [
            'error'   => false,
            'data'    => null,
            'message' => null
        ];

        if (defined($_POST['cat_id'])) {
            $l_category_info = $l_dao->get_cat_by_const($_POST['cat_id']);
            $l_catg = null;
            $l_cats = null;
            $l_catg_custom = null;

            switch ($l_category_info['type']) {
                case C__CMDB__CATEGORY__TYPE_GLOBAL:
                    $l_catg = constant($_POST['cat_id']);
                    break;
                case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                    $l_cats = constant($_POST['cat_id']);
                    break;
                case C__CMDB__CATEGORY__TYPE_CUSTOM:
                    $l_catg_custom = constant($_POST['cat_id']);
                    break;
            }
            $l_res = $l_dao->retrieve_properties(null, $l_catg, $l_cats, C__PROPERTY__PROVIDES__REPORT, "", false, $l_catg_custom);
        } else {
            $l_return['error'] = true;
            $l_return['message'] = "Constant '" . $_POST['cat_id'] . "' is not defined.";

            return $l_return;
        }

        try {
            $l_cnt_properties = $l_res->num_rows();
            $l_specialCategories = filter_defined_constants(['C__CATG__OPERATING_SYSTEM', 'C__CATG__LOCATION']); // @see ID-3891

            while ($l_row = $l_res->get_row()) {
                if (((int)$l_row['provides'] & C__PROPERTY__PROVIDES__VIRTUAL && !in_array($l_catg, $l_specialCategories)) && $l_cnt_properties > 1 &&
                    !($l_row['const'] == 'C__CATG__GLOBAL' && $l_row['key'] == 'id')) {
                    continue;
                }

                $l_return['data'][$l_row['const'] . '-' . $l_row['key']] = isys_application::instance()->container->get('language')
                    ->get($l_row['title']);
            }

            if (is_array($l_return['data'])) {
                asort($l_return['data']);
            }
        } catch (Exception $e) {
            $l_return['error'] = true;
            $l_return['message'] = $e->getMessage();
        }

        return $l_return;
    }

    /**
     * Retrieve user input fields for given placeholders
     *
     * @return array
     */
    protected function get_user_input_field_for_placeholders()
    {
        $placeholders = $_POST['placeholders'];

        $fields = [];

        foreach ($placeholders as $placeholder) {
            $class = 'idoit\Module\Report\SqlQuery\Placeholder\\' . str_replace('-', '', ucwords($placeholder, '-'));

            if (class_exists($class)) {
                /**
                 * @var $queryPlaceholder Placeholder
                 */
                $queryPlaceholder = new $class();

                $fields[$placeholder] = $queryPlaceholder->getFieldsForUserInput();
            }
        }

        return $fields;
    }

    /**
     * Method for retrieving the options to a given property.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function add_contraint_to_property()
    {
        $l_return = $l_ui_params = [];
        $l_load_field = true;
        $l_condition = '';

        $l_dao = new isys_cmdb_dao_category_property($this->m_database_component);

        $l_prop_id = null;

        if (is_numeric($_POST['prop_id'])) {
            $l_prop_id = $_POST['prop_id'];
            $l_condition = '';
        } elseif (strpos($_POST['prop_id'], '-')) {
            $l_prop_info = explode('-', $_POST['prop_id']);
            $l_condition = ' AND isys_property_2_cat__cat_const = ' . $l_dao->convert_sql_text($l_prop_info[0]) . ' AND isys_property_2_cat__prop_key = ' .
                $l_dao->convert_sql_text($l_prop_info[1]);
        }

        $l_row = $l_dao->retrieve_properties($l_prop_id, null, null, C__PROPERTY__PROVIDES__REPORT, $l_condition)->get_row();

        $l_return['special_field'] = null;

        $l_cat_dao = $l_dao->get_dao_instance($l_row['class'], ($l_row['catg_custom'] ?: null));
        $l_properties = $l_cat_dao->get_properties();
        $l_props = $l_properties[$l_row['key']];

        $l_popup_types = [
            'browser_object_ng',
            'browser_location',
            'browser_object_relation',
            'browser_cable_connection_ng',
            'browser_file',
            'browser_sanpool'
        ];

        $changeablePopupTypes = [
            'browser_sanpool'
        ];

        $unchangeablePopupTypes = [
            'browser_cable_connection_ng',
            'browser_location',
            'browser_object_ng'
        ];

        $l_identifier = $l_row['class'] . '::' . $l_row['key'];
        $sourceTable = $l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__SOURCE_TABLE] ?: $l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0];

        // @see  ID-4706, ID-6634 Specific check for "special field".
        $specialField = isset($l_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]) &&
            $l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] !== C__PROPERTY__INFO__TYPE__OBJECT_BROWSER &&
            (strpos($sourceTable, 'catg') !== false || strpos($sourceTable, 'cats') !== false) &&
            strpos($sourceTable, '_list') !== false &&
            $sourceTable !== 'isys_catg_custom_fields_list';

        $_POST['division'] = str_replace('__HIDDEN', '', $_POST['division']);

        // We check for special formats to
        if ($l_props[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] === C__PROPERTY__UI__TYPE__DATE || $l_props[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] === C__PROPERTY__UI__TYPE__DATETIME) {
            $p_strValue = $_POST['value'];

            if (class_exists('idoit\Module\Report\SqlQuery\Placeholder\\' . trim(str_replace('-', '', ucwords($p_strValue, '-'))))) {
                $p_strValue = '';
            }

            $l_cal = new isys_popup_calendar();
            $l_cat_options = [
                'name'              => $_POST['division'],
                'p_bEditMode'       => true,
                'p_bInfoIconSpacer' => 0,
                'p_strClass'        => 'reportInput ' . $_POST['prop_class'],
                'p_strStyle'        => 'width:140px;',
                'p_strValue'        => $p_strValue,
                'p_dataIdentifier'  => $l_identifier
            ];

            $l_return['special_field'] = $l_cal->handle_smarty_include(isys_application::instance()->template, $l_cat_options);
            $l_load_field = false;
        } elseif ($l_props[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] === C__PROPERTY__UI__TYPE__POPUP && in_array($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'], $l_popup_types, true) && !$specialField) {
            // Get the ui params.
            $l_ui_params = $l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS];

            if (isset($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection'])) {
                $l_multiselection = (bool)$l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection'];
            } else {
                $l_multiselection = false;
            }

            if ((isset($l_ui_params['secondSelection']) || in_array($l_ui_params['p_strPopupType'], $changeablePopupTypes)) &&
                !in_array($l_ui_params['p_strPopupType'], $unchangeablePopupTypes, true)) {
                $l_ui_params['p_strPopupType'] = 'browser_object_ng';
                //unset($l_ui_params['secondSelection']);
                $l_multiselection = false;
            }

            $l_ui_params['name'] = $_POST['division'];
            $l_ui_params['p_strSelectedID'] = $_POST['value'];
            $l_ui_params['p_strValue'] = $_POST['value'];
            $l_ui_params['p_bInfoIconSpacer'] = 0;
            $l_ui_params['p_bEditMode'] = true;
            $l_ui_params['edit'] = true;
            $l_ui_params['p_strClass'] = 'reportInput ' . $_POST['prop_class'] . ' input-mini';
            $l_ui_params[isys_popup_browser_object_ng::C__EDIT_MODE] = true;
            if ($l_ui_params['p_strPopupType'] !== 'browser_object_relation') {
                $l_ui_params[isys_popup_browser_object_ng::C__MULTISELECTION] = $l_multiselection;
            }
            $l_ui_params[isys_popup_browser_object_ng::C__DISABLE_DETACH] = false;
            $l_ui_params['p_dataIdentifier'] = $l_identifier;
            //$l_ui_params['p_dataIdentifier'] = '';

            unset(
                $l_ui_params[isys_popup_browser_object_ng::C__DATARETRIEVAL],
                $l_ui_params[isys_popup_browser_object_ng::C__FORM_SUBMIT],
                $l_ui_params[isys_popup_browser_object_ng::C__RETURN_ELEMENT]
            );

            $l_popup_class = "isys_popup_" . $l_ui_params['p_strPopupType'];
            if (class_exists($l_popup_class)) {
                $l_popup = new isys_smarty_plugin_f_popup();

                $l_return['special_field'] = $l_popup->navigation_edit(isys_application::instance()->template, $l_ui_params);
                $l_load_field = false;
            }
        }

        if ($l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] === C__PROPERTY__INFO__TYPE__COMMENTARY) {
            $l_return['equation'] = [
                'LIKE %...%',
                'NOT LIKE %...%',
                'IS NULL',
                'IS NOT NULL'
            ];
            $l_return['field'] = null;
        } elseif ($l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] === C__PROPERTY__INFO__TYPE__DIALOG_LIST) {
            $l_return['equation'] = [
                'LIKE %...%',
                'NOT LIKE %...%'
            ];
            $l_return['field'] = null;
        } elseif ((($sourceTable == null ||
                    substr($sourceTable, 0, 5) !== 'isys_') &&
                empty($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) &&
                !in_array($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'], $l_popup_types, true)) || $specialField ||
            in_array($l_props[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1], isys_cmdb_dao_category_property::$m_ignored_format_callbacks)) {
            $l_return['equation'] = [
                '=',
                '&lt;',
                '&gt;',
                '!=',
                '&lt;=',
                '&gt;=',
                'LIKE',
                'LIKE %...%',
                'NOT LIKE',
                'NOT LIKE %...%',
                'PLACEHOLDER',
                'IS NULL',
                'IS NOT NULL'
            ];
            $l_return['field'] = null;
        } elseif ($sourceTable === 'isys_connection' &&
            empty($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
            $l_return['equation'] = [
                '=',
                '!=',
                'subcnd',
                'PLACEHOLDER',
                'IS NULL',
                'IS NOT NULL'
            ];

            if ($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection']) {
                unset($l_return['equation'][array_search('subcnd', $l_return['equation'])]);
            }
        } else {
            $l_return['equation'] = [
                '=',
                '!=',
                'PLACEHOLDER'
            ];
            $l_data = null;

            if (!empty($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
                if (is_array($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
                    // If we simply get an array.
                    $l_data = $l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];
                } elseif (is_object($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) &&
                    $l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'] instanceof \isys_callback) {
                    // If we get an instance of "isys_callback"
                    $l_data = $l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']->execute();
                    if (isys_format_json::is_json_array($l_data)) {
                        $l_data = isys_format_json::decode($l_data);
                    } elseif (is_string($l_data)) {
                        $l_data = unserialize($l_data);
                    }
                } elseif (is_string($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
                    // Or if we get a string (we assume it's serialized).
                    $l_data = unserialize($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']);
                }
            }

            // @todo Special treatment for the stupid IP addresses... We need to fix this generically!
            if ($l_load_field) {
                if ((!isset($l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_bDbFieldNN']) || $l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_bDbFieldNN'] == 0) &&
                    ($l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DIALOG ||
                        $l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DIALOG_PLUS ||
                        $l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__MULTISELECT)) {
                    $l_return['field'] = ['-1' => isys_tenantsettings::get('gui.empty_value')];
                } else {
                    $l_return['field'] = [];
                }

                if ($sourceTable !== null && $sourceTable !== 'isys_cats_net_ip_addresses_list' && $l_data === null) {
                    // Prepare array, so we can check this in the GUI.
                    $l_sql = "SELECT " . $sourceTable . "__id AS 'id', " . $sourceTable . "__title AS 'title' FROM " . $sourceTable . ";";
                    $l_field_res = $l_dao->retrieve($l_sql);

                    while ($l_field_row = $l_field_res->get_row()) {
                        $l_return['field'][$l_field_row['id'] . ' '] = isys_application::instance()->container->get('language')
                            ->get($l_field_row['title']);
                    }
                } elseif (is_array($l_data)) {
                    if (count($l_data) > 0) {
                        foreach ($l_data as $l_key => $l_val) {
                            if (is_array($l_val)) {
                                foreach ($l_val as $l_key2 => $l_val2) {
                                    $l_return['field'][$l_key2 . ' '] = isys_application::instance()->container->get('language')
                                        ->get($l_val2);
                                }
                            } else {
                                $l_return['field'][$l_key . ' '] = isys_application::instance()->container->get('language')
                                    ->get($l_val);
                            }
                        }
                    }
                }

                if (!empty($l_return['field']) && is_array($l_return['field'])) {
                    asort($l_return['field']);
                }
            }

            if ($l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] === C__PROPERTY__INFO__TYPE__OBJECT_BROWSER) {
                // Special equation for category location
                if ($l_ui_params['p_strPopupType'] === 'browser_location') {
                    $l_return['equation'][] = 'under_location';
                }

                // Object Browser with multiselection should be deactivated because the join consists of a comma seperated list
                if (!$l_props[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection'] || count($l_properties) === 1) {
                    $l_return['equation'][] = 'subcnd';
                    //$l_return['equation'][] = 'PLACEHOLDER';
                }

                if ($l_ui_params['secondSelection'] === true) {
                    $l_return['equation'][] = 'IS NULL';
                    $l_return['equation'][] = 'IS NOT NULL';
                    unset(
                        $l_return['equation'][array_search('subcnd', $l_return['equation'])],
                        $l_return['equation'][array_search('PLACEHOLDER', $l_return['equation'])]
                    );
                }
            }
        }

        // Check if we got a convert method to apply.
        if ($l_props[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] === 'convert' || isset($l_props[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT])) {
            // We need to get the unit information.
            if ($l_row['catg'] != null) {
                // We have to select from CATG.
                $l_unit_row = $l_dao->retrieve_properties(
                    null,
                    $l_row['catg'],
                    null,
                    null,
                    "AND isys_property_2_cat__prop_key = " . $l_dao->convert_sql_text($l_props[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT])
                )
                    ->get_row();
            } else {
                // We have to select from CATS.
                $l_unit_row = $l_dao->retrieve_properties(
                    null,
                    null,
                    $l_row['cats'],
                    null,
                    "AND isys_property_2_cat__prop_key = " . $l_dao->convert_sql_text($l_props[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT])
                )
                    ->get_row();
            }

            $l_cat_dao = $l_dao->get_dao_instance($l_unit_row['class'], ($l_unit_row['catg_custom'] ?: null));
            $l_properties = $l_cat_dao->get_properties();
            $l_unit_props = $l_properties[$l_unit_row['key']];

            $l_table = $l_unit_props[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0];
            $l_unit_property = $l_props[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT];

            if (!empty($l_table)) {
                $l_sql = "SELECT " . $l_table . "__id AS id, " . $l_table . "__title AS title FROM " . $l_table . " ORDER BY " . $l_table . "__sort ASC;";
                $l_unit_res = $l_dao->retrieve($l_sql);

                while ($l_unit_row = $l_unit_res->get_row()) {
                    $l_return['unit'][$l_unit_row['id'] . '-' . $l_unit_property] = isys_application::instance()->container->get('language')
                        ->get($l_unit_row['title']);
                }
            }
        }

        if ($l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DIALOG ||
            $l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__DIALOG_PLUS ||
            $l_props[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__MULTISELECT) {
            unset($l_return['equation'][array_search('PLACEHOLDER', $l_return['equation'])]);
        }

        // This is not necessary, but will provide the frontend with a ARRAY instead of JSON-Object.
        $l_return['equation'] = array_values($l_return['equation']);

        return $l_return;
    }

    /**
     * Method for "checking" if a report will work and find objects.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function check_report()
    {
        global $g_comp_database;

        $l_conditions = isys_format_json::decode($_POST['condition']);

        // We have to "simulate" the data comes from $_POST to use the "create_property_query_for_report()" method.
        if (is_array($l_conditions)) {
            foreach ($l_conditions as $l_field => $l_value) {
                $_POST[$l_field] = $l_value;
            }
        }

        try {
            $l_dao = new isys_cmdb_dao_category_property($g_comp_database);
            $l_sql = $l_dao->create_property_query_for_report(5);

            $l_return = [
                'error'   => false,
                'message' => isys_application::instance()->container->get('language')
                    ->get('LC__REPORT__FORM__CHECK_NOTE', [
                        $l_dao->retrieve($l_sql)
                            ->num_rows()
                    ])
            ];
        } catch (Exception $e) {
            $l_return = [
                'error'   => true,
                'message' => isys_application::instance()->container->get('language')
                    ->get('LC__REPORT__FORM__CHECK_ERROR')
            ];
        }

        return $l_return;
    }

    protected function build_tree()
    {
        $reportModule = isys_module_report::get_instance();

        $l_tree = isys_module_request::get_instance()
            ->get_menutree();

        $reportModule->build_tree($l_tree, false, null, 1);

        return [
            'error'   => false,
            'message' => $l_tree->process($_GET[C__GET__TREE_NODE])
        ];
    }
}
