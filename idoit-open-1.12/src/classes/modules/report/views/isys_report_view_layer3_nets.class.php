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
class isys_report_view_layer3_nets extends isys_report_view
{
    /**
     * @return string
     */
    public static function name()
    {
        return 'LC__REPORT__VIEW__LAYER3_NETS__TITLE';
    }

    /**
     * @return string
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__LAYER3_NETS__DESCRIPTION';
    }

    /**
     * @return string
     */
    public function template()
    {
        return isys_module_report::getPath() . 'templates/view_layer3_nets.tpl';
    }

    /**
     * @return string
     */
    public static function viewtype()
    {
        return 'LC__CMDB__OBJTYPE__RELATION';
    }

    /**
     *
     */
    public function start()
    {
        // Preparing some variables.
        $l_data = [];
        $l_return = [];

        // Initializing the DAO's.
        $l_obj_dao = new isys_cmdb_dao($this->database);
        $l_l3_dao = new isys_cmdb_dao_category_s_net($this->database);
        $l_port_dao = new isys_cmdb_dao_category_g_network_port($this->database);

        // At first we search all objects of the type "layer2 net".
        $l_obj_res = $l_obj_dao->get_objects_by_type(defined_or_default('C__OBJTYPE__LAYER3_NET'));

        // And now the fun begins...
        while ($l_obj_row = $l_obj_res->get_row()) {
            if (empty($l_obj_row['isys_obj__title'])) {
                $l_obj_row['isys_obj__title'] = '(' . $this->language->get('LC__UNIVERSAL__NO_TITLE') . ')';
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
                    $l_server_row['isys_obj__title'] = '(' . $this->language->get('LC__UNIVERSAL__NO_TITLE') . ')';
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
        $this->template->assign('data', isys_format_json::encode($l_return));
    }
}
