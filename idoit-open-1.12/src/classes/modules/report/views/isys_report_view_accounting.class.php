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
class isys_report_view_accounting extends isys_report_view
{
    /**
     * @return string
     */
    public static function name()
    {
        return 'Buchhaltung Kostenstellen';
    }

    /**
     * @return string
     */
    public static function description()
    {
        return 'Report zum darstellen der geänderten Kostenstellen innerhalb eines Zeitraums';
    }

    /**
     * @return string
     */
    public function template()
    {
        return isys_module_report::getPath() . 'templates/view_accounting.tpl';
    }

    /**
     * @return string
     */
    public static function viewtype()
    {
        return 'LC__CMDB__OBJTYPE__CATG';
    }

    /**
     * @throws isys_exception_database
     */
    public function start()
    {
        global $g_dirs;

        // Preparing some variables.
        $l_return = [];

        if ($_POST['C__CALENDAR_FROM__HIDDEN'] && $_POST['C__CALENDAR_TO__HIDDEN']) {
            // Initializing the DAO's.
            $l_log_dao = new isys_component_dao_logbook($this->database);
            $l_from = date('Y-m-d', strtotime($_POST['C__CALENDAR_FROM__HIDDEN']));
            $l_to = date('Y-m-d', strtotime($_POST['C__CALENDAR_TO__HIDDEN']));

            // Prepare the SQL to select all entries from "accounting" between the given dates.
            $l_sql = "SELECT * FROM isys_logbook
				LEFT JOIN isys_catg_logb_list ON isys_catg_logb_list__isys_logbook__id = isys_logbook__id
				LEFT JOIN isys_obj ON isys_obj__id = isys_catg_logb_list__isys_obj__id
                LEFT JOIN isys_catg_model_list ON isys_catg_model_list__isys_obj__id = isys_catg_logb_list__isys_obj__id
				WHERE (isys_logbook__date BETWEEN '" . $l_from . "' AND '" . $l_to . "')
				AND (isys_logbook__category_static = 'LC__CMDB__CATG__ACCOUNTING');";

            $l_res = $l_log_dao->retrieve($l_sql);

            while ($l_row = $l_res->get_row()) {
                $l_changes = unserialize($l_row['isys_logbook__changes']);

                if (is_array($l_changes)) {
                    if (isset($l_changes['isys_cmdb_dao_category_g_accounting::account']) || isset($l_changes['C__CATG__ACCOUNTING__ACCOUNT'])) {
                        $l_change_from = $l_changes['isys_cmdb_dao_category_g_accounting::account']['from'] ?: $l_changes['C__CATG__ACCOUNTING__ACCOUNT']['from'];
                        $l_change_to = $l_changes['isys_cmdb_dao_category_g_accounting::account']['to'] ?: $l_changes['C__CATG__ACCOUNTING__ACCOUNT']['to'];

                        if ($l_change_from === null) {
                            $l_change_from = '<i>leer</i>';
                        }

                        if ($l_change_to === null) {
                            $l_change_to = '<i>leer</i>';
                        }

                        $l_return[] = [
                            $l_row['isys_obj__id'],
                            '<a href="?' . C__CMDB__GET__OBJECT . '=' . $l_row['isys_obj__id'] . '&' . C__CMDB__GET__CATG . '=' . defined_or_default('C__CATG__ACCOUNTING') .
                            '">' . $this->language->get($l_row['isys_logbook__obj_type_static']) . ' &raquo; ' . $l_row['isys_obj__title'] . '</a>',
                            date('d.m.Y H:i:s', strtotime($l_row['isys_logbook__date'])),
                            $l_row['isys_logbook__user_name_static'],
                            $l_change_from,
                            $l_change_to,
                            $l_row['isys_catg_model_list__serial']
                        ];
                    }
                }
            }
        }

        // Finally assign the data to the template.
        $this->template->activate_editmode()
            ->assign('data', $l_return)
            ->assign('from', $_POST['C__CALENDAR_FROM__HIDDEN'])
            ->assign('to', $_POST['C__CALENDAR_TO__HIDDEN'])
            ->assign('g_dirs', $g_dirs);
    }
}
