<?php

/**
 * i-doit Report Manager View.
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   Copyright 2011 - synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_report_view_it_service_cmdb_status extends isys_report_view
{
    /**
     * Inconsistence array.
     *
     * @var  array
     */
    private $m_inconsistence = [];

    /**
     * Object array.
     *
     * @var  array
     */
    private $m_obj_arr = [];

    /**
     * @return string
     */
    public static function name()
    {
        return 'LC__REPORT__VIEW__CMDB_STATUS_CHECK_ON_ITS__TITLE';
    }

    /**
     * @return string
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__CMDB_STATUS_CHECK_ON_ITS__DESCRIPTION';
    }

    /**
     * @return string
     */
    public function template()
    {
        return isys_module_report::getPath() . 'templates/view_it_service_cmdb_status.tpl';
    }

    /**
     * @return string
     */
    public static function viewtype()
    {
        return 'LC__CMDB__CATG__IT_SERVICE';
    }

    /**
     * @throws isys_exception_database
     */
    public function start()
    {
        global $g_dirs;

        $l_dao = new isys_cmdb_dao_category_g_relation($this->database);

        $l_its_arr = [];
        $l_sql = 'SELECT * FROM isys_obj
			INNER JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
			WHERE isys_obj__isys_obj_type__id = ' . $l_dao->convert_sql_id(defined_or_default('C__OBJTYPE__IT_SERVICE')) . '
			AND isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                if (!in_array($l_row["isys_obj__isys_cmdb_status__id"], filter_defined_constants([
                    'C__CMDB_STATUS__IN_OPERATION',
                    'C__CMDB_STATUS__IDOIT_STATUS',
                    'C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE',
                ]), true)) {
                    $this->m_inconsistence[$l_row["isys_obj__id"]][$l_row["isys_obj__id"]] = $l_row["isys_obj__isys_cmdb_status__id"];
                }

                $l_its_arr[$l_row["isys_obj__id"]]["cmdb_status"] = $l_row["isys_obj__isys_cmdb_status__id"];
                $l_its_arr[$l_row["isys_obj__id"]]["cmdb_color"] = $l_row["isys_cmdb_status__color"];
                $l_its_arr[$l_row["isys_obj__id"]]["child"] = $this->recurse_relation($l_row["isys_obj__id"]);

                if (count($this->m_inconsistence[$l_row["isys_obj__id"]]) == 0) {
                    unset($l_its_arr[$l_row["isys_obj__id"]]);
                }

                $this->m_obj_arr = [];
            }
        }

        $this->template->assign("image_dir", $g_dirs["images"] . "dtree/")
            ->assign("viewContent", $this->prepare_root($l_its_arr))
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
    }

    /**
     *
     */
    public function ajax_request()
    {
        if (isys_glob_get_param('request') === 'show_relations') {
            echo $this->prepare_table($this->get_its_relations($_POST[C__CMDB__GET__OBJECT]));
            die;
        }
    }

    /**
     * @param $p_its_obj_id
     *
     * @return array
     * @throws isys_exception_database
     */
    private function get_its_relations($p_its_obj_id)
    {
        $l_dao = new isys_cmdb_dao($this->database);

        $l_its_arr = [];
        $l_sql = 'SELECT * FROM isys_obj
			INNER JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
			WHERE isys_obj__isys_obj_type__id = ' . $l_dao->convert_sql_id(defined_or_default('C__OBJTYPE__IT_SERVICE')) . '
			AND isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_obj__id = ' . $l_dao->convert_sql_id($p_its_obj_id) . ';';

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                if (!in_array($l_row["isys_obj__isys_cmdb_status__id"], filter_defined_constants([
                    'C__CMDB_STATUS__IN_OPERATION',
                    'C__CMDB_STATUS__IDOIT_STATUS',
                    'C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE',
                ]), true)) {
                    $this->m_inconsistence[$l_row["isys_obj__id"]][$l_row["isys_obj__id"]] = $l_row["isys_obj__isys_cmdb_status__id"];
                }

                $l_its_arr[$l_row["isys_obj__id"]]["cmdb_status"] = $l_row["isys_obj__isys_cmdb_status__id"];
                $l_its_arr[$l_row["isys_obj__id"]]["cmdb_color"] = $l_row["isys_cmdb_status__color"];
                $l_its_arr[$l_row["isys_obj__id"]]["child"] = $this->recurse_relation($l_row["isys_obj__id"]);

                $this->m_obj_arr = [];
            }
        }

        return $l_its_arr;
    }

    /**
     * @param      $p_obj_id
     * @param null $p_it_service
     *
     * @return array
     * @throws isys_exception_database
     */
    private function recurse_relation($p_obj_id, $p_it_service = null)
    {
        $l_dao = new isys_cmdb_dao_category_g_relation($this->database);

        if ($p_it_service === null) {
            $p_it_service = $p_obj_id;
        }

        $l_arr = [];
        $l_sql = "SELECT * FROM isys_catg_relation_list
			LEFT JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id__master
			LEFT JOIN isys_cmdb_status ON isys_cmdb_status__id = isys_obj__isys_cmdb_status__id
			WHERE isys_obj__status = " . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . " AND isys_catg_relation_list__isys_obj__id__slave = " .
            $l_dao->convert_sql_id($p_obj_id) . ';';

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                if (is_null($this->m_obj_arr) || !in_array($l_row["isys_catg_relation_list__isys_obj__id__master"], $this->m_obj_arr)) {
                    $this->m_obj_arr[] = $p_obj_id;
                    if (!is_value_in_constants(
                        $l_row["isys_obj__isys_cmdb_status__id"],
                        ['C__CMDB_STATUS__IN_OPERATION', 'C__CMDB_STATUS__IDOIT_STATUS', 'C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE']
                    )) {
                        if (is_null($this->m_inconsistence[$p_it_service]) ||
                            !in_array($l_row["isys_catg_relation_list__isys_obj__id__master"], $this->m_inconsistence[$p_it_service])) {
                            $this->m_inconsistence[$p_it_service][$l_row["isys_catg_relation_list__isys_obj__id__master"]] = $l_row["isys_obj__isys_cmdb_status__id"];
                        }
                    }

                    $l_arr[$l_row["isys_obj__id"]]["cmdb_status"] = $l_row["isys_obj__isys_cmdb_status__id"];
                    $l_arr[$l_row["isys_obj__id"]]["cmdb_color"] = $l_row["isys_cmdb_status__color"];
                    $l_arr[$l_row["isys_obj__id"]]["child"] = $this->recurse_relation($l_row["isys_catg_relation_list__isys_obj__id__master"], $p_it_service);
                }
            }
        }

        return $l_arr;
    }

    /**
     * @param $p_its_arr
     *
     * @return string
     * @throws Exception
     */
    private function prepare_root($p_its_arr)
    {
        global $g_dirs;

        $l_dao = new isys_cmdb_dao($this->database);
        $l_quicky = new isys_ajax_handler_quick_info();

        $l_quicky->set_style("line-height:20px;padding-left:5px;padding-right:5px;");

        if (count($p_its_arr)) {
            $l_return = "<table padding=\"0px\" cellspacing=\"0px\" style=\"position:relative;spacing:0px;\" width=\"100%\" class=\"report_listing\">";

            foreach ($p_its_arr as $l_obj_id => $l_value) {
                $l_return .= "<tr><td onclick=\"collapse_it_service('" . $l_obj_id . "');show_relations('" . $l_obj_id . "');\" class=\"report_listing\" id=\"it_service_" .
                    $l_obj_id . "\">";
                $l_return .= "<img id=\"" . $l_obj_id . "_plusminus\" src=\"" . $g_dirs["images"] . "dtree/nolines_plus.gif\" class=\"vam\"> " .
                    $l_quicky->get_quick_info($l_obj_id, $l_dao->get_obj_name_by_id_as_string($l_obj_id), C__LINK__OBJECT) . " <img src=\"" . $g_dirs["images"] .
                    "ajax-loading.gif\" id=\"ajax_loading_view_" . $l_obj_id . "\" style=\"display:none;\" class=\"vam\" />";
                $l_return .= "<br>";
                $l_return .= "</td></tr><tr><td><span id=\"row_" . $l_obj_id . "\"></span></td></tr>";
            }

            $l_return .= "</table>";
        } else {
            $l_return = '<div class="p5 m5 info"><img src="' . $g_dirs['images'] . 'icons/silk/information.png" class="vam mr5" /><span class="vam">' .
                $this->language->get('LC__REPORT__VIEW__NO_INCONSISTENCY') . '</span></div>';
        }

        return $l_return;
    }

    /**
     * @param $p_its_arr
     *
     * @return string
     * @throws Exception
     */
    private function prepare_table($p_its_arr)
    {
        $l_dao = new isys_cmdb_dao_status($this->database);
        $l_quicky = new isys_ajax_handler_quick_info();
        $l_inco_objects = '';

        $l_table = '<table padding="0" cellspacing="0" style="position:relative;"><tr><td style="padding-left:5px;">';

        if (count($this->m_inconsistence) > 0) {
            $l_table .= $this->language->get('LC__REPORT__VIEW__INCONSISTENCY_IN') . ': <p style="text-align:justify">';
            $l_counter = 900;

            foreach ($this->m_inconsistence as $l_inco_val) {
                $l_counter_arr = 1;
                $l_count_arr = count($l_inco_val);
                foreach ($l_inco_val as $l_inco_obj_id => $l_inco_status) {
                    $l_inco_status = $l_dao->get_cmdb_status($l_inco_status)
                        ->get_row();
                    $l_inco_objects .= $l_quicky->get_quick_info($l_inco_obj_id, $l_dao->get_obj_name_by_id_as_string($l_inco_obj_id), C__LINK__OBJECT) . " [<b>" .
                        $this->language->get($l_inco_status["isys_cmdb_status__title"]) . "</b>], ";
                    if (strlen($l_inco_objects) - $l_counter > 0) {
                        if ($l_counter_arr == $l_count_arr) {
                            $l_inco_objects = substr($l_inco_objects, 0, -2);
                        }
                        $l_inco_objects .= "<br>";
                        $l_counter = $l_counter + 900;
                    }
                    $l_counter_arr++;
                }
            }
            $l_table .= substr($l_inco_objects, 0, -2);
            $l_table .= "</p><br>";
        }

        $l_table .= "</td></tr><tr><td>";

        $l_quicky->set_style("line-height:20px;padding-left:5px;padding-right:5px;");

        foreach ($p_its_arr as $l_key => $l_value) {
            $l_object_title = $l_dao->get_obj_name_by_id_as_string($l_key);
            $l_object_type_title = $this->language->get($l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($l_key)));

            $l_table .= "<table padding=\"0px\" cellspacing=\"0px\" style=\"position:relative;spacing:0px;\"><tr><td align=\"center\" title=\"" . $l_object_title . " (" .
                $l_object_type_title . ")\" class=\"vam\" style=\"cursor:pointer;border:2px solid #" . $l_value["cmdb_color"] . ";background-color:#EFEFEF;\">";
            $l_table .= "  " . $l_quicky->get_quick_info($l_key, $l_object_title, C__LINK__OBJECT) . "<br>";
            $l_table .= "  (" . $l_object_type_title . ")<br>";
            $l_table .= "</td>";

            $l_table .= "<td>";
            $l_table .= $this->prepare_childs($l_value["child"], $l_key);
            $l_table .= "</td>";

            $l_table .= "</tr></table>";
        }

        $l_table .= "</td></tr></table><br />";

        return $l_table;
    }

    /**
     * @param $p_arr
     * @param $p_root_obj
     *
     * @return string
     * @throws Exception
     */
    private function prepare_childs($p_arr, $p_root_obj)
    {
        $l_dao = new isys_cmdb_dao($this->database);
        $l_quicky = new isys_ajax_handler_quick_info();
        $l_quicky->set_style("line-height:20px;padding-left:5px;padding-right:5px;");

        if (is_array($p_arr)) {
            $l_table = '';

            foreach ($p_arr as $l_root_obj => $l_value) {
                $l_object_title = $l_dao->get_obj_name_by_id_as_string($l_root_obj);
                $l_object_type_title = $this->language->get($l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($l_root_obj)));

                $l_table .= '<table padding="0" cellspacing="2" style="position:relative;"><tr>' . '<td valign="top" align="center" title="' . $l_object_title . ' (' .
                    $l_object_type_title . ')" class="child" style="border-color: #' . $l_value['cmdb_color'] . ';">' .
                    $l_quicky->get_quick_info($l_root_obj, $l_object_title, C__LINK__OBJECT) . '<br />(' . $l_object_type_title . ')' . '</td>';

                if (is_array($l_value)) {
                    $l_table .= '<td>' . $this->prepare_childs($l_value["child"], $p_root_obj) . '</td>';
                }

                $l_table .= '</tr></table>';
            }

            return $l_table;
        }
    }
}
