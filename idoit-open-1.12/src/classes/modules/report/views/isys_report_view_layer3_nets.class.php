<?php

/**
 * i-doit Report Manager View
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   Copyright 2011 - synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-8
 */
class isys_report_view_layer3_nets extends isys_report_view
{
    /**
     * Method for ajax-requests. Must be implemented.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function ajax_request()
    {
        ;
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
        return 'LC__REPORT__VIEW__LAYER3_NETS__DESCRIPTION';
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
        return 'LC__REPORT__VIEW__LAYER3_NETS__TITLE';
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
        $l_data = [];
        $l_return = [];

        // Initializing the DAO's.
        $l_obj_dao = new isys_cmdb_dao($g_comp_database);
        $l_l3_dao = new isys_cmdb_dao_category_s_net($g_comp_database);
        $l_port_dao = new isys_cmdb_dao_category_g_network_port($g_comp_database);

        // At first we search all objects of the type "layer2 net".
        $l_obj_res = $l_obj_dao->get_objects_by_type(defined_or_default('C__OBJTYPE__LAYER3_NET'));

        // And now the fun begins...
        while ($l_obj_row = $l_obj_res->get_row()) {
            if (empty($l_obj_row['isys_obj__title'])) {
                $l_obj_row['isys_obj__title'] = '(' . isys_application::instance()->container->get('language')
                        ->get('LC__UNIVERSAL__NO_TITLE') . ')';
            }

            $l_l3_link = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $l_obj_row['isys_obj__id'] . '">' . $l_obj_row['isys_obj__title'] . '</a>';

            // We need this for L3 nets without assigned objects.
            if (!isset($l_data[$l_l3_link])) {
                $l_data[$l_l3_link] = [];
            }

            $l_server_res = $l_l3_dao->get_assigned_hosts($l_obj_row['isys_obj__id']);

            // Here we retrieve all server, which have assigned the layer3 net of this iteration.
            while ($l_server_row = $l_server_res->get_row()) {
                if (empty($l_server_row['isys_obj__title'])) {
                    $l_server_row['isys_obj__title'] = '(' . isys_application::instance()->container->get('language')
                            ->get('LC__UNIVERSAL__NO_TITLE') . ')';
                }

                $l_server_link = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $l_server_row['isys_obj__id'] . '">' . $l_server_row['isys_obj__title'] . '</a>';

                // We need this for L3 nets without ports.
                if (!isset($l_data[$l_l3_link][$l_server_link])) {
                    $l_data[$l_l3_link][$l_server_link] = [];
                }

                // For this server, we fetch all ports.
                $l_port_res = $l_port_dao->get_data(null, $l_server_row['isys_obj__id']);

                while ($l_port_row = $l_port_res->get_row()) {
                    if ($l_port_row['isys_catg_ip_list__id'] === null) {
                        continue;
                    }

                    $l_ip_address = $l_port_row['isys_cats_net_ip_addresses_list__title'];
                    $l_ip_port = $l_port_row['isys_catg_port_list__title'];

                    // For each port we can now select the assigned layer2 nets.
                    $l_l2_nets = $l_port_dao->get_attached_layer2_net_as_array($l_port_row['isys_catg_port_list__id']);

                    if (count($l_l2_nets) > 0) {
                        foreach ($l_l2_nets as $l_l2_net_id) {
                            $l_l2_link = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $l_l2_net_id . '">' . $l_obj_dao->get_obj_name_by_id_as_string($l_l2_net_id) . '</a>';
                            $l_data[$l_l3_link][$l_server_link][$l_ip_address][$l_ip_port] = $l_l2_link;
                        }
                    } else {
                        $l_data[$l_l3_link][$l_server_link][$l_ip_address][$l_ip_port] = '';
                    }
                }
            }
        }

        // To easily display the data we have to alter the array structure.
        foreach ($l_data as $l_key => $l_item) {
            if (is_array($l_item) && !empty($l_item)) {
                foreach ($l_item as $l_key2 => $l_item2) {
                    if (is_array($l_item2) && !empty($l_item2)) {
                        foreach ($l_item2 as $l_key3 => $l_item3) {
                            if (is_array($l_item3) && !empty($l_item3)) {
                                foreach ($l_item3 as $l_key4 => $l_item4) {
                                    $l_return[] = [
                                        $l_key,
                                        $l_key2,
                                        $l_key3,
                                        $l_key4,
                                        $l_item4
                                    ];
                                }
                            } else {
                                $l_return[] = [
                                    $l_key,
                                    $l_key2,
                                    $l_key3
                                ];
                            }
                        }
                    } else {
                        $l_return[] = [
                            $l_key,
                            $l_key2
                        ];
                    }
                }
            } else {
                $l_return[] = [$l_key];
            }
        }

        // Finally assign the data to the template.
        isys_application::instance()->template->assign('data', isys_format_json::encode($l_return));
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
        return 'view_layer3_nets.tpl';
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
        return 'LC__CMDB__OBJTYPE__RELATION';
    }
}

?>
