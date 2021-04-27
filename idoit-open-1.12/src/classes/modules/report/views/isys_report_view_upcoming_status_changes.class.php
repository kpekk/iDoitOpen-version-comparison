<?php

/**
 * i-doit Report Manager View for upcoming changes.
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_report_view_upcoming_status_changes extends isys_report_view
{
    /**
     * Empty abstract method.
     */
    public function ajax_request()
    {
        ;
    }

    /**
     * LC string of this reports view's description.
     *
     * @return  string
     */
    public static function description()
    {
        return "LC__REPORT__VIEW__UPCOMING_STATUS_CHANGES__DESCRIPTION";
    }

    /**
     * Initialize method.
     *
     * @return  boolean
     */
    public function init()
    {
        return true;
    }

    /**
     * LC string of this report view's title.
     *
     * @return  string
     */
    public static function name()
    {
        return "LC__REPORT__VIEW__UPCOMING_STATUS_CHANGES__TITLE";
    }

    /**
     * Method for preparing the data.
     */
    public function start()
    {
        global $g_comp_database;

        $l_changedata = $l_titles = [];
        $l_changegroups = [
            3,
            7,
            10,
            14,
            30
        ];

        $l_dao_planning = new isys_cmdb_dao_category_g_planning($g_comp_database);

        foreach ($l_changegroups as $l_changedays) {
            $l_data = [];

            $l_tmp = $l_dao_planning->get_data(null, null, " AND (isys_catg_planning_list__start BETWEEN " . time() . " AND " . strtotime("+$l_changedays days") . ")");

            while ($l_row = $l_tmp->get_row()) {
                $l_data[] = [
                    "id"     => $l_row["isys_obj__id"],
                    "title"  => $l_row["isys_obj__title"],
                    "status" => $l_row["isys_cmdb_status__title"],
                    "start"  => $l_row["isys_catg_planning_list__start"],
                    "end"    => $l_row["isys_catg_planning_list__end"]
                ];
            }

            $l_changedata[$l_changedays] = $l_data;
            $l_titles[$l_changedays] = isys_application::instance()->container->get('language')
                ->get('LC__REPORT__VIEW__UPCOMING_STATUS_CHANGES__NEXT_DAYS', $l_changedays);
        }

        isys_application::instance()->template->assign("changeData", $l_changedata)
            ->assign('titles', $l_titles);
    }

    /**
     * Template file of this report view.
     *
     * @return  string
     */
    public function template()
    {
        return "view_upcoming_status_change.tpl";
    }

    /**
     * Returns the report view's type.
     *
     * @return  integer
     */
    public static function type()
    {
        return self::c_php_view;
    }

    /**
     * Report view's view type.
     *
     * @return string
     */
    public static function viewtype()
    {
        return "CMDB-Status";
    }
}
