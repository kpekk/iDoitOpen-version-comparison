<?php

/**
 * i-doit Report Manager View
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   Copyright 2011 - synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_report_view_devices_in_location extends isys_report_view
{
    /**
     * Method for ajax-requests.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function ajax_request()
    {
        global $g_comp_database;

        $l_return = [];

        $l_objects = isys_cmdb_dao_location::instance($g_comp_database)
            ->get_child_locations_recursive($_POST['obj_id']);

        foreach ($l_objects as $l_object) {
            // We want to go sure we get no corrupted data.
            if ($l_object['isys_obj__id'] > 0) {
                $l_return[] = $l_object;
            }
        }

        // Now we add the "parent" object itself.
        $l_rootnode = isys_cmdb_dao::instance($g_comp_database)
            ->get_object_by_id($_POST['obj_id'])
            ->get_row();

        $l_rootnode['isys_obj_type__title'] = isys_application::instance()->container->get('language')
            ->get($l_rootnode['isys_obj_type__title']);
        $l_return[] = $l_rootnode;

        header('Content-Type: application/json');

        echo isys_format_json::encode($l_return);
        die();
    }

    /**
     * Method for retrieving the language constant of the report-description.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__DEVICES_IN_LOCATION_DESCRIPTION';
    }

    /**
     * Initialize method.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        return true;
    }

    /**
     * Method for retrieving the language constant of the report-name.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public static function name()
    {
        return 'LC__REPORT__VIEW__DEVICES_IN_LOCATION';
    }

    /**
     * Start-method - Implement the logic for displaying your data here.
     *
     * @global  isys_component_database $g_comp_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function start()
    {
        global $g_comp_database;

        // Preparing some variables.
        $l_objtypes = [];

        $l_objtype_res = isys_cmdb_dao::instance($g_comp_database)
            ->get_object_types_by_properties();

        if ($l_objtype_res->num_rows() > 0) {
            while ($l_objtype_row = $l_objtype_res->get_row()) {
                $l_objtypes[$l_objtype_row['isys_obj_type__id']] = isys_application::instance()->container->get('language')
                    ->get($l_objtype_row['isys_obj_type__title']);
            }
        }

        asort($l_objtypes);

        $l_rules = ['C__OBJECT_TYPES' => ['p_arData' => $l_objtypes]];

        // Finally assign the data to the template.
        isys_application::instance()->template->activate_editmode()
            ->assign('ajax_url', isys_glob_add_to_query('ajax', 1))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }

    /**
     * Method for retrieving the template-name of this report.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @todo    Should we update the parent method to retrieve this automatically?
     */
    public function template()
    {
        return 'view_devices_in_location.tpl';
    }

    /**
     * Method for declaring the type of this report.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function type()
    {
        return self::c_php_view;
    }

    /**
     * Method for declaring the view-type.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function viewtype()
    {
        return 'LC__CMDB__OBJTYPE__CATG';
    }
}

?>
