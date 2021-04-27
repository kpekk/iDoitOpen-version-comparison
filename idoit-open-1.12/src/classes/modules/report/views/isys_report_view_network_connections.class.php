<?php

/**
 * i-doit Report View for showing all network connections
 *
 * @package     i-doit
 * @subpackage  Reports
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_report_view_network_connections extends isys_report_view
{

    /**
     * @return string
     */
    public static function name()
    {
        return 'LC__CATG__NET_CONNECTIONS';
    }

    /**
     * @return string
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__DESCRIPTION__NETWORK_CONNECTIONS';
    }

    /**
     * @return string
     */
    public function template()
    {
        return isys_module_report::getPath() . 'templates/view_network_connections.tpl';
    }

    /**
     * @return string
     */
    public static function viewtype()
    {
        return 'LC__CMDB__OBJTYPE__CATG';
    }

    /**
     *
     */
    public function start()
    {
        $l_dao_connections = new isys_cmdb_dao_category_g_net_listener($this->database);
        $l_rules['dialog_protocol']['p_arData'] = $l_dao_connections->get_dialog_as_array('isys_net_protocol');
        $l_rules['dialog_protocol_5']['p_arData'] = $l_dao_connections->get_dialog_as_array('isys_net_protocol_layer_5');

        $l_dao_net = new isys_cmdb_dao_category_s_net($this->database);
        $l_arNetworks = [];
        $l_networks = $l_dao_net->get_data();
        while ($l_row = $l_networks->get_row()) {
            $l_arNetworks[$l_row['isys_obj__id']] = $l_row['isys_obj__title'] . ' (' . $l_row['isys_cats_net_list__address'] . ')';
        }
        $l_rules['dialog_net']['p_arData'] = $l_arNetworks;

        $this->template
            ->activate_editmode()
            ->assign('ajax_url', isys_glob_add_to_query('ajax', 1))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }

    /**
     *
     */
    public function ajax_request()
    {
        $l_dao_connections = new isys_cmdb_dao_category_g_net_listener($this->database);

        $l_condition = '';
        if (isset($_POST['dialog_protocol']) && $_POST['dialog_protocol'] > 0) {
            $l_condition .= ' AND (isys_net_protocol__id = ' . $l_dao_connections->convert_sql_id($_POST['dialog_protocol']) . ')';
        }
        if (isset($_POST['dialog_protocol_5']) && $_POST['dialog_protocol_5'] > 0) {
            $l_condition .= ' AND (isys_net_protocol_layer_5__id = ' . $l_dao_connections->convert_sql_id($_POST['dialog_protocol_5']) . ')';
        }

        if (isset($_POST['text_port']) && $_POST['text_port'] > 0) {
            $l_condition .= ' AND (isys_catg_net_listener_list__port_from >= ' . $l_dao_connections->convert_sql_id($_POST['text_port']) .
                ' AND isys_catg_net_listener_list__port_to <= ' . $l_dao_connections->convert_sql_id($_POST['text_port']) . ')';
        }

        if (isset($_POST['dialog_net']) && $_POST['dialog_net'] > 0) {
            $l_condition .= ' AND (network.isys_obj__id = ' . $l_dao_connections->convert_sql_id($_POST['dialog_net']) . ')';
        }

        $l_connections = $l_dao_connections->get_connections($l_condition);

        $l_headers = [
            $this->language->get('LC__CMDB__OBJTYPE__LAYER3_NET'),
            $this->language->get('LC__CMDB__CATG__NET_CONNECTOR__SOURCE_DEVICE'),
            $this->language->get('LC__CMDB__CATG__NET_CONNECTOR__IP_ADDRESS'),
            $this->language->get('LC__CATD__PROTOCOL') . '/Port',
            $this->language->get('LC__CMDB__CATG__NET_LISTENER__BIND_DEVICE'),
            $this->language->get('LC__CMDB__CATG__NET_LISTENER__DESTINATION_IP_ADDRESS'),
            $this->language->get('LC__CMDB__CATG__APPLICATION_OBJ_APPLICATION'),
            $this->language->get('LC__CATG__NET_CONNECTIONS__GATEWAY') . '-Source',
            $this->language->get('LC__CATG__NET_CONNECTIONS__GATEWAY') . '-Destination',
        ];

        $l_return = [];
        while ($l_row = $l_connections->get_row()) {
            if (isset($l_row['protocol_layer_5']) && $l_row['protocol_layer_5']) {
                $l_layer5 = ': ' . $l_row['protocol_layer_5'];
            } else {
                $l_layer5 = '';
            }

            $l_return[] = [
                $l_headers[0] => $l_row['network'] . ' (' . $l_row['net_address'] . ')',
                $l_headers[1] => $l_row['source_object'],
                $l_headers[2] => $l_row['source_ip'],
                $l_headers[3] => '<= ' . $l_row['protocol'] . $l_layer5 . '/' .
                    ($l_row['source_port_from'] == $l_row['source_port_to'] ? $l_row['source_port_from'] : $l_row['source_port_from'] . '-' . $l_row['source_port_to']) .
                    ' =>',
                $l_headers[4] => $l_row['bind_object'],
                $l_headers[5] => $l_row['bind_ip'],
                $l_headers[6] => $l_row['bind_application'] ?: '-',
                $l_headers[7] => $l_row['source_gateway'] ?: '-',
                $l_headers[8] => $l_row['bind_gateway'] ?: '-',
            ];
        }

        header('Content-Type: application/json');
        echo isys_format_json::encode($l_return);

        die;
    }
}
