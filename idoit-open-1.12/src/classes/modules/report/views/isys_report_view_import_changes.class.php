<?php

/**
 * i-doit Report Manager View
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   Copyright 2011 - synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_report_view_import_changes extends isys_report_view
{
    private $m_dao;

    /**
     * @return string
     */
    public static function name()
    {
        return 'LC__REPORT__VIEW__IMPORT_CHANGES';
    }

    /**
     * @return string
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__IMPORT_CHANGES__DESCRIPTION';
    }

    /**
     * @return string
     */
    public function template()
    {
        return isys_module_report::getPath() . 'templates/view_import_changes.tpl';
    }

    /**
     * @return string
     */
    public static function viewtype()
    {
        return 'Import';
    }

    /**
     *
     */
    public function start()
    {
        $this->m_dao = isys_cmdb_dao::instance($this->database);

        $l_rules = [
            'C__IMPORT_TYPES'                    => [
                'p_arData'     => $this->get_import_types(),
                'p_strClass'   => 'input-mini',
                'p_bDbFieldNN' => true
            ],
            'C__IMPORT_TYPES__TIMEPERIOD__START' => [
                'p_strClass' => 'input-small',
                'p_strStyle' => 'width:70%;',
                'p_bTime'    => true
            ],
            'C__IMPORT_TYPES__TIMEPERIOD__END'   => [
                'p_strClass' => 'input-small',
                'p_strStyle' => 'width:70%;',
                'p_bTime'    => true
            ]
        ];

        // Finally assign the data to the template.
        $this->template
            ->activate_editmode()
            ->assign('ajax_url', isys_glob_add_to_query('ajax', 1))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }

    /**
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function ajax_request()
    {
        if (!isset($this->m_dao)) {
            $this->m_dao = isys_cmdb_dao::instance($this->database);
        }

        $l_return = [];
        $l_func = $_POST['func'];

        switch ($l_func) {
            case 'load_executed_imports':
                $l_return = $this->load_executed_imports($_POST['import_type'], $_POST['timeperiod_start'], $_POST['timeperiod_end']);
                break;
            case 'load_import_changes':
                echo $this->load_import_changes($_POST['import_id']);
                die;
                break;
            default:
                break;
        }

        header('Content-Type: application/json');

        echo isys_format_json::encode($l_return);
        die();
    }

    /**
     * @return array
     */
    private function get_import_types()
    {
        $l_sql = 'SELECT * FROM isys_import_type';
        $l_res = $this->m_dao->retrieve($l_sql);
        $l_return = [];

        while ($l_row = $l_res->get_row()) {
            $l_return[$l_row['isys_import_type__const']] = $l_row['isys_import_type__title'];
        }

        return $l_return;
    }

    /**
     * @param $p_import_id
     *
     * @return string
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    private function load_import_changes($p_import_id)
    {
        $l_listdao = isys_component_dao_logbook::instance($this->database);
        $l_listres = $l_listdao->get_result_by_import_id($p_import_id);

        if ($l_listres->num_rows() > 0) {
            $l_arTableHeader = [
                '+'                              => '',
                'isys_logbook__title'            => $this->language->get('LC__CMDB__LOGBOOK__TITLE'),
                'isys_logbook__user_name_static' => 'User',
                'isys_logbook__changes'          => $this->language->get('LC__CMDB__LOGBOOK__CHANGED_FIELDS'),
                'isys_logbook__date'             => $this->language->get('LC__CMDB__LOGBOOK__DATE'),
                'isys_logbook_level__title'      => $this->language->get('LC__CMDB__LOGBOOK__LEVEL')
            ];

            $l_objList = new isys_component_list_logbook(null, $l_listres, $l_listdao);

            $l_strRowLink = "document.location.href='?moduleID=" . defined_or_default('C__MODULE__LOGBOOK') . "&id=[{isys_logbook__id}]';";

            $l_filter['import_id'] = $p_import_id;
            $l_objList->config($l_arTableHeader, $l_strRowLink);

            return $l_objList->getTempTableHtml($l_filter);
        }

        return $this->language->get('LC__REPORT__VIEW__IMPORT_CHANGES__NO_CHANGES_FOUND');
    }

    /**
     * @param        $p_type
     * @param string $p_timeperiod_start
     * @param string $p_timeperiod_end
     *
     * @return array
     */
    private function load_executed_imports($p_type, $p_timeperiod_start = '', $p_timeperiod_end = '')
    {
        $l_sql = 'SELECT * FROM isys_import
            INNER JOIN isys_import_type ON isys_import_type__id = isys_import__isys_import_type__id
            WHERE isys_import_type__const = ' . $this->m_dao->convert_sql_text($p_type);

        if ($p_timeperiod_start !== '' && $p_timeperiod_end !== '') {
            $l_sql .= ' AND isys_import__import_date BETWEEN ' . $this->m_dao->convert_sql_text($p_timeperiod_start) . ' AND ' .
                $this->m_dao->convert_sql_text($p_timeperiod_end);
        } elseif ($p_timeperiod_start !== '') {
            $l_sql .= ' AND isys_import__import_date > ' . $this->m_dao->convert_sql_text($p_timeperiod_start);
        } elseif ($p_timeperiod_end !== '') {
            $l_sql .= ' AND isys_import__import_date < ' . $this->m_dao->convert_sql_text($p_timeperiod_start);
        }

        $l_sql .= ' ORDER BY isys_import__import_date DESC';

        $l_res = $this->m_dao->retrieve($l_sql);
        $l_return = [];

        while ($l_row = $l_res->get_row()) {
            switch ($l_row['isys_import_type__const']) {
                case 'C__IMPORT_TYPE__CSV':
                    if ($l_row['isys_import__isys_import_profile__id'] > 0) {
                        $l_sql_profile = 'SELECT isys_csv_profile__title FROM isys_csv_profile ' . 'WHERE isys_csv_profile__id = ' .
                            $this->m_dao->convert_sql_id($l_row['isys_import__isys_import_profile__id']);
                        $l_title = $this->m_dao->retrieve($l_sql_profile)
                            ->get_row_value('isys_csv_profile__title');
                        $l_title = preg_replace('/\(.*\)/', '(' . $l_row['isys_import__title'] . ')', $l_title);
                    } else {
                        $l_title = $l_row['isys_import__title'];
                    }
                    $l_return[] = [
                        'id'       => $l_row['isys_import__id'],
                        'title'    => $l_title,
                        'datetime' => $l_row['isys_import__import_date']
                    ];
                    break;
                case 'C__IMPORT_TYPE__XML':
                case 'C__IMPORT_TYPE__JDISC':
                default:
                    $l_return[] = [
                        'id'       => $l_row['isys_import__id'],
                        'title'    => $l_row['isys_import__title'],
                        'datetime' => $l_row['isys_import__import_date']
                    ];
                    break;
            }
        }

        return $l_return;
    }
}
