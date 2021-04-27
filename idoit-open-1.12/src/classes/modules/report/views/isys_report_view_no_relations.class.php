<?php

/**
 * i-doit Report Manager View
 *
 * @package    i-doit
 * @subpackage Reports
 * @author     Van Quyen Hoang <qhoang@synetics.de>
 * @copyright  Copyright 2011 - synetics GmbH
 */
class isys_report_view_no_relations extends isys_report_view
{
    /**
     * @return string
     */
    public static function name()
    {
        return 'LC__REPORT__VIEW__OBJECTS_WITHOUT_RELATIONS__TITLE';
    }

    /**
     * @return string
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__OBJECTS_WITHOUT_RELATIONS__DESCRIPTION';
    }

    /**
     * @return string
     */
    public function template()
    {
        return isys_module_report::getPath() . 'templates/view_no_relations.tpl';
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

        $l_dao = isys_cmdb_dao::instance($this->database);
        $l_navbar = isys_component_template_navbar::getInstance();

        $l_sql = "SELECT * FROM isys_obj_type_group";
        $l_res = $l_dao->retrieve($l_sql);
        $l_arData[-1] = "LC__CMDB__RECORD_STATUS__ALL";
        while ($l_row = $l_res->get_row()) {
            $l_arData[$l_row["isys_obj_type_group__id"]] = $l_row["isys_obj_type_group__title"];
        }
        $l_rules["C__DIALOG_OBJECTGROUP"]["p_arData"] = $l_arData;

        $l_sql = "SELECT * FROM isys_obj_type";
        $l_res = $l_dao->retrieve($l_sql);
        $l_arData[-1] = $this->language->get('LC__UNIVERSAL__ALL');
        while ($l_row = $l_res->get_row()) {
            $l_arData[$l_row["isys_obj_type__id"]] = $l_row["isys_obj_type__title"];
        }
        $l_rules["C__DIALOG_OBJECTTYPE"]["p_arData"] = $l_arData;

        $l_record_status = [
            C__RECORD_STATUS__NORMAL   => $this->language->get('LC__CMDB__RECORD_STATUS__NORMAL'),
            C__RECORD_STATUS__ARCHIVED => $this->language->get('LC__CMDB__RECORD_STATUS__ARCHIVED'),
            C__RECORD_STATUS__DELETED  => $this->language->get('LC__CMDB__RECORD_STATUS__DELETED')
        ];

        $l_rules["C__DIALOG_STATUS"]["p_arData"] = $l_record_status;
        $l_rules["C__DIALOG_STATUS"]["p_strSelectedID"] = ($_POST['C__DIALOG_STATUS']) ?: C__RECORD_STATUS__NORMAL;

        if (isset($_POST[C__GET__NAVMODE])) {
            $l_ids = $_POST['id'];
            $l_current_status = $_POST['C__DIALOG_STATUS'];

            if (count($l_ids)) {
                switch ($_POST[C__GET__NAVMODE]) {
                    case C__NAVMODE__ARCHIVE:
                        if ($l_current_status < C__RECORD_STATUS__ARCHIVED) {
                            while ($l_current_status !== C__RECORD_STATUS__ARCHIVED) {
                                $l_dao->rank_records($l_ids, C__CMDB__RANK__DIRECTION_DELETE);
                                $l_current_status++;
                            }
                        } elseif ($l_current_status > C__RECORD_STATUS__ARCHIVED) {
                            while ($l_current_status !== C__RECORD_STATUS__ARCHIVED) {
                                $l_dao->rank_records($l_ids, C__CMDB__RANK__DIRECTION_RECYCLE);
                                $l_current_status--;
                            }
                        }
                        break;
                    case C__NAVMODE__DELETE:
                        if ($l_current_status < C__RECORD_STATUS__DELETED) {
                            while ($l_current_status !== C__RECORD_STATUS__DELETED) {
                                $l_dao->rank_records($l_ids, C__CMDB__RANK__DIRECTION_DELETE);
                                $l_current_status++;
                            }
                        }
                        break;
                    case C__NAVMODE__PURGE:
                        if ($l_current_status <= C__RECORD_STATUS__DELETED) {
                            $l_dao->rank_records($l_ids, C__CMDB__RANK__DIRECTION_DELETE, 'isys_obj', null, true);
                        }
                        break;
                    case C__NAVMODE__RECYCLE:
                        if ($l_current_status > C__RECORD_STATUS__NORMAL) {
                            $l_dao->rank_records($l_ids, C__CMDB__RANK__DIRECTION_RECYCLE);
                        }
                        break;
                    default:
                        break;
                }
            }

            $this->template->assign('fire_filter', true);
        }

        $l_navbar->set_active(true, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_active(true, C__NAVBAR_BUTTON__DELETE)
            ->set_active(true, C__NAVBAR_BUTTON__RECYCLE)
            ->set_active(true, C__NAVBAR_BUTTON__PURGE);

        $l_ajax_url = isys_glob_url_remove(isys_glob_add_to_query('ajax', 1), 'call');

        $this->template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->assign("dir_images", $g_dirs["images"])
            ->assign('page_limit', isys_glob_get_pagelimit())
            ->assign('ajax_url', $l_ajax_url);
    }

    /**
     * Hanldes all ajax request for the report view
     *
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function ajax_request()
    {
        if (isset($_GET['func'])) {
            $l_func = $_GET['func'];
            $l_dao = isys_cmdb_dao::instance($this->database);

            switch ($l_func) {
                case 'show_list':
                    $l_return = [
                        'success' => false,
                        'data'    => "<span class='p10 m10'><strong>" . $this->language->get("LC__UNIVERSAL__NO_OBJECTS_FOUND") . "</strong></span>"
                    ];

                    $l_objtypegroup_id = (int)$_POST['C__DIALOG_OBJECTGROUP'];
                    $l_objtype_id = (int)$_POST['C__DIALOG_OBJECTTYPE'];
                    $l_obj_status = (int)$_POST['C__DIALOG_STATUS'];

                    $l_arr = $this->get_objects_no_relations($l_objtypegroup_id, $l_objtype_id, $l_obj_status);
                    if (count($l_arr)) {
                        $l_return['success'] = true;
                        $l_return['data'] = $l_arr;
                    }

                    header('Content-Type: application/json');
                    echo isys_format_json::encode($l_return);
                    break;
                case 'reload_objecttypes':

                    $l_smarty_plugin = new isys_smarty_plugin_f_dialog();

                    $l_objtype_group_id = $_POST["objTypeGroupID"];
                    $l_sel_id = $_POST["selID"];

                    $l_sql = "SELECT * FROM isys_obj_type WHERE TRUE ";
                    if ($l_objtype_group_id > 0) {
                        $l_sql .= "AND isys_obj_type__isys_obj_type_group__id = " . $l_dao->convert_sql_id($l_objtype_group_id) . " ";
                    }
                    $l_sql .= "AND isys_obj_type__show_in_tree = 1 ";
                    $l_res = $l_dao->retrieve($l_sql);
                    $l_objecttypes[-1] = "LC__CMDB__RECORD_STATUS__ALL";
                    while ($l_row = $l_res->get_row()) {
                        $l_objecttypes[$l_row["isys_obj_type__id"]] = $this->language->get($l_row["isys_obj_type__title"]);
                    }

                    $l_param["p_arData"] = $l_objecttypes;
                    $l_param["name"] = "C__DIALOG_OBJECTTYPE";
                    $l_param["id"] = "C__DIALOG_OBJECTTYPE";
                    $l_param["p_bDbFieldNN"] = 1;
                    if ($l_sel_id > 0) {
                        $l_param["p_strSelectedID"] = $l_sel_id;
                    }

                    $l_edit = $l_smarty_plugin->navigation_edit($this->template, $l_param);
                    echo $l_edit;
                    break;
                default:
                    break;
            }
        }
        die;
    }

    /**
     * @param null $p_objgroup_id
     * @param null $p_objtype_id
     * @param int  $p_obj_status
     *
     * @return array
     * @throws isys_exception_database
     */
    public function get_objects_no_relations($p_objgroup_id = null, $p_objtype_id = null, $p_obj_status = C__RECORD_STATUS__NORMAL)
    {
        $l_dao = isys_cmdb_dao::instance($this->database);

        $l_sql = 'SELECT * FROM isys_obj 
            INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
            INNER JOIN isys_obj_type_group ON isys_obj_type_group__id = isys_obj_type__isys_obj_type_group__id
            WHERE TRUE ';

        if ($p_objgroup_id > 0) {
            $l_sql .= "AND isys_obj_type_group__id = " . $l_dao->convert_sql_id($p_objgroup_id) . " ";
        }

        if ($p_objtype_id > 0) {
            $l_sql .= "AND isys_obj_type__id = " . $l_dao->convert_sql_id($p_objtype_id) . " ";
        }

        $l_sql .= "AND isys_obj_type__show_in_tree = 1 " . "AND isys_obj__status = " . $l_dao->convert_sql_int($p_obj_status);

        $l_res = $l_dao->retrieve($l_sql);
        $l_no_relation = [];

        $l_quickinfo = new isys_ajax_handler_quick_info();

        while ($l_row = $l_res->get_row()) {
            $l_has_no_relations = false;

            $l_sql_relation = 'SELECT isys_catg_relation_list__id FROM isys_catg_relation_list
				WHERE isys_catg_relation_list__isys_obj__id__master = ' . $l_dao->convert_sql_id($l_row['isys_obj__id']) . '
				OR isys_catg_relation_list__isys_obj__id__slave = ' . $l_dao->convert_sql_id($l_row['isys_obj__id']) . ' LIMIT 1;';

            $l_res_relation = $l_dao->retrieve($l_sql_relation);

            if ($l_res_relation && $l_res_relation->num_rows() == 0) {
                $l_has_no_relations = true;
            }

            if ($l_has_no_relations) {
                $l_no_relation[] = [
                    '__id__'                                            => $l_row['isys_obj__id'],
                    'ID'                                                => $l_row['isys_obj__id'],
                    $this->language->get('LC__UNIVERSAL__OBJECT_TITLE') => $l_quickinfo->get_quick_info($l_row["isys_obj__id"], $l_row["isys_obj__title"], C__LINK__OBJECT),
                    $this->language->get('LC_UNIVERSAL__OBJECT_TYPE')   => $this->language->get($l_row['isys_obj_type__title'])
                ];
            }
        }

        return $l_no_relation;
    }
}
