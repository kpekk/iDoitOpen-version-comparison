<?php

/**
 * i-doit Report Manager View
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   Copyright 2011 - synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-8
 */
class isys_report_view_network_plan extends isys_report_view
{

    /**
     * @var int
     */
    private $m_counter = 0;

    /**
     * @var isys_cmdb_dao_category_g_ip
     */
    private $m_daoIP;

    /**
     * @var isys_cmdb_dao_category_g_relation
     */
    private $m_dao_relation;

    /**
     * @var isys_log
     */
    private $m_log;

    /**
     * @var array
     */
    private $m_objects_processed = [];

    /**
     * @var isys_tree
     */
    private $m_tree;

    /**
     * @return string
     */
    public static function name()
    {
        return 'Layer-3 network plan';
    }

    /**
     * @return string
     */
    public static function description()
    {
        return '';
    }

    /**
     * @return string
     */
    public function template()
    {
        return isys_module_report::getPath() . 'templates/view_network_plan.tpl';
    }

    /**
     * @return string
     */
    public static function viewtype()
    {
        return 'LC__CMDB__OBJTYPE__RELATION';
    }

    /**
     * @throws isys_exception_database
     */
    public function start()
    {
        global $g_dirs;

        $this->m_dao_relation = new isys_cmdb_dao_category_g_relation($this->database);
        $l_dao_net = new isys_cmdb_dao_category_s_net($this->database);

        /* Get gloval default net*/
        if (isset($_POST['layer3net'])) {
            $l_net = $l_dao_net->get_data(null, $_POST['layer3net'])->__to_array();
        } else {
            $l_net = $l_dao_net->get_global_ipv4_net();
        }

        /* Initialize tree algorythm */
        $this->m_tree = new isys_tree(new isys_tree_node_explorer([
            'id'   => $this->m_counter++,
            'name' => $l_net['isys_obj__title'],
            'data' => [
                'image'      => ($l_net["isys_obj_type__obj_img_name"]) ? $g_dirs["images"] . "objecttypes/" . $l_net["isys_obj_type__obj_img_name"] : false,
                'objectType' => $this->language->get('LC__CMDB__OBJTYPE__LAYER3_NET'),
                'cmdbStatus' => '',
                'ipAddress'  => $l_net['isys_cats_net_list__address'],
                'hostname'   => '',
                'color'      => $l_net['isys_obj_type__color']
            ]
        ]));

        $this->m_daoIP = new isys_cmdb_dao_category_g_ip($this->database);

        /* Increase nesting level */
        ini_set("xdebug.max_nesting_level", "1000");

        /* Log */
        //$this->m_log->notice('Starting to read network information for "'.$l_net['isys_obj__title'].'"');

        /* Recurse network and build the tree */
        $this->recurse($l_net['isys_cats_net_list__isys_obj__id'], $this->m_tree);

        // Finally assign the data to the template.
        $this->template
            ->assign('layer3networks', $l_dao_net->get_data()->__as_array())
            ->assign('data', $this->m_tree->toJSON());
    }

    /**
     * Recursively walk through network relations.
     *
     * @param $p_obj_id
     * @param $p_node
     *
     * @return bool
     * @throws isys_exception_database
     */
    private function recurse($p_obj_id, $p_node)
    {
        global $g_dirs;

        if (!isset($this->m_objects_processed[$p_obj_id])) {
            $this->m_objects_processed[$p_obj_id] = true;

            $l_relations = $this->m_dao_relation->get_related_objects($p_obj_id, filter_defined_constants([
                'C__RELATION_TYPE__NETWORK_PORT',
                'C__RELATION_TYPE__IP_ADDRESS',
                'C__RELATION_TYPE__CONNECTORS'
            ]));

            while ($l_row = $l_relations->get_row()) {
                $l_related = $this->m_dao_relation->get_object_by_id($l_row['related'])->__to_array();
                $l_ip = $this->m_daoIP->get_primary_ip($l_related['isys_obj__id'])->get_row();

                $l_node = new isys_tree_node_explorer([
                    'id'   => $l_related['isys_obj__id'],
                    'name' => $l_related['isys_obj__title'],
                    'data' => [
                        'image'      => $l_related["isys_obj_type__obj_img_name"] ? $g_dirs["images"] . "objecttypes/" . $l_related["isys_obj_type__obj_img_name"] : false,
                        'objectType' => $this->language->get($l_related['isys_obj_type__title']),
                        'cmdbStatus' => $l_related['isys_cmdb_status__title'] ? $this->language->get($l_related['isys_cmdb_status__title']) : '',
                        'ipAddress'  => @$l_ip['isys_cats_net_ip_addresses_list__title'] ? @$l_ip['isys_cats_net_ip_addresses_list__title'] : '',
                        'hostname'   => @$l_ip['isys_catg_ip_list__hostname'] ? @$l_ip['isys_catg_ip_list__hostname'] : '',
                    ]
                ]);

                //$this->m_log->notice('Processing ' . $l_row['isys_obj__title'] . '('.$l_related['isys_obj_type__title'].')');
                $p_node->add($l_node);

                /* Recurse further, if this is no connection to another net */
                if ($l_related['isys_obj_type__id'] != defined_or_default('C__OBJTYPE__LAYER3_NET') && $l_related['isys_obj_type__id'] != defined_or_default('C__OBJTYPE__LAYER2_NET')) {
                    $this->recurse($l_row['related'], $l_node);
                }
            }

            return true;
        }

        return false;
    }
}
