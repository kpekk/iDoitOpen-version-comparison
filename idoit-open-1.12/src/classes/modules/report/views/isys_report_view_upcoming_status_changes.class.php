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
     * @return string
     */
    public static function name()
    {
        return 'LC__REPORT__VIEW__UPCOMING_STATUS_CHANGES__TITLE';
    }

    /**
     * @return string
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__UPCOMING_STATUS_CHANGES__DESCRIPTION';
    }

    /**
     * @return string
     */
    public function template()
    {
        return isys_module_report::getPath() . 'templates/view_upcoming_status_change.tpl';
    }

    /**
     * @return string
     */
    public static function viewtype()
    {
        return 'CMDB-Status';
    }

    /**
     * @throws Exception
     */
    public function start()
    {
        $l_changedata = $l_titles = [];
        $l_changegroups = [3, 7, 10, 14, 30];

        $l_dao_planning = new isys_cmdb_dao_category_g_planning($this->database);

        foreach ($l_changegroups as $l_changedays) {
            $l_data = [];

            $l_tmp = $l_dao_planning->get_data(null, null, " AND (isys_catg_planning_list__start BETWEEN " . time() . " AND " . strtotime("+$l_changedays days") . ")");

            while ($l_row = $l_tmp->get_row()) {
                $l_data[] = [
                    'id'     => $l_row['isys_obj__id'],
                    'title'  => $l_row['isys_obj__title'],
                    'status' => $l_row['isys_cmdb_status__title'],
                    'start'  => $l_row['isys_catg_planning_list__start'],
                    'end'    => $l_row['isys_catg_planning_list__end']
                ];
            }

            $l_changedata[$l_changedays] = $l_data;
            $l_titles[$l_changedays] = $this->language->get('LC__REPORT__VIEW__UPCOMING_STATUS_CHANGES__NEXT_DAYS', $l_changedays);
        }

        $this->template
            ->assign('changeData', $l_changedata)
            ->assign('titles', $l_titles);
    }
}
