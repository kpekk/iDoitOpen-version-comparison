<?php

/**
 * i-doit Report Manager View
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   Copyright 2011 - synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_report_view_devices_in_location extends isys_report_view
{
    /**
     * @return string
     */
    public static function name()
    {
        return 'LC__REPORT__VIEW__DEVICES_IN_LOCATION';
    }

    /**
     * @return string
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__DEVICES_IN_LOCATION_DESCRIPTION';
    }

    /**
     * @return string
     */
    public function template()
    {
        return isys_module_report::getPath() . 'templates/view_devices_in_location.tpl';
    }

    /**
     * @return string
     */
    public static function viewtype()
    {
        return 'LC__CMDB__OBJTYPE__CATG';
    }

    /**
     * @throws Exception
     */
    public function start()
    {
        // Preparing some variables.
        $l_objtypes = [];

        $l_objtype_res = isys_cmdb_dao::instance($this->database)
            ->get_object_types_by_properties();

        if ($l_objtype_res->num_rows() > 0) {
            while ($l_objtype_row = $l_objtype_res->get_row()) {
                $l_objtypes[$l_objtype_row['isys_obj_type__id']] = $this->language->get($l_objtype_row['isys_obj_type__title']);
            }
        }

        asort($l_objtypes);

        $l_rules = ['C__OBJECT_TYPES' => ['p_arData' => $l_objtypes]];

        // Finally assign the data to the template.
        $this->template->activate_editmode()
            ->assign('ajax_url', isys_glob_add_to_query('ajax', 1))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }

    /**
     * @throws isys_exception_database
     */
    public function ajax_request()
    {
        $l_return = [];

        $l_objects = isys_cmdb_dao_location::instance($this->database)
            ->get_child_locations_recursive($_POST['obj_id']);

        foreach ($l_objects as $l_object) {
            // We want to go sure we get no corrupted data.
            if ($l_object['isys_obj__id'] > 0) {
                $l_return[] = $l_object;
            }
        }

        // Now we add the "parent" object itself.
        $l_rootnode = isys_cmdb_dao::instance($this->database)
            ->get_object_by_id($_POST['obj_id'])
            ->get_row();

        $l_rootnode['isys_obj_type__title'] = $this->language->get($l_rootnode['isys_obj_type__title']);
        $l_return[] = $l_rootnode;

        header('Content-Type: application/json');

        echo isys_format_json::encode($l_return);
        die();
    }
}
