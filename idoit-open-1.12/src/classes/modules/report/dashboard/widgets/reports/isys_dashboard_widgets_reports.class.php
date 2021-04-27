<?php

/**
 * i-doit
 *
 * Dashboard widget class
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_dashboard_widgets_reports extends isys_dashboard_widgets
{
    /**
     * Path and Filename of the configuration template.
     *
     * @var  string
     */
    protected $m_config_tpl_file = '';

    /**
     * Path and Filename of the template.
     *
     * @var  string
     */
    protected $m_tpl_file = '';

    /**
     * Returns a boolean value, if the current widget has an own configuration page.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function has_configuration()
    {
        return true;
    }

    /**
     * Init method.
     *
     * @param   array $p_config
     *
     * @return  isys_dashboard_widgets_quicklaunch
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init($p_config = [])
    {
        $this->m_tpl_file = __DIR__ . '/templates/report.tpl';
        $this->m_config_tpl_file = __DIR__ . '/templates/config.tpl';

        return parent::init($p_config);
    }

    /**
     * Method for loading the widget configuration.
     *
     * @param   array   $p_row The current widget row from "isys_widgets".
     * @param   integer $p_id  The ID from "isys_widgets_config".
     *
     * @return  string
     * @throws  Exception
     * @throws  SmartyException
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function load_configuration(array $p_row, $p_id)
    {
        global $g_comp_database_system;

        $l_reports = [];

        $reports = isys_report_dao::instance($g_comp_database_system)
            ->get_reports(null, isys_auth_report::instance()
                ->get_allowed_reports(), null, false, false);

        foreach ($reports as $report) {
            $l_reports[$report['isys_report_category__title']][$report['isys_report__id']] = $report['isys_report__title'];
        }

        $l_reports = array_map(function ($l_item) {
            asort($l_item);

            return $l_item;
        }, $l_reports);

        $l_rules = [
            'report_list'     => serialize($l_reports),
            'selected_report' => $this->m_config['report_id'],
            'count'           => $this->m_config['count'],
            'limit'           => (!isset($this->m_config['limit'])) ? 5000 : $this->m_config['limit']
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', 'Reports')
            ->assign('rules', $l_rules)
            ->fetch($this->m_config_tpl_file);
    }

    /**
     * Render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @throws  Exception
     * @throws  SmartyException
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function render($p_unique_id)
    {
        global $g_comp_database_system;

        /* @var  isys_report_dao $l_dao */
        $l_dao = isys_report_dao::instance($g_comp_database_system);

        try {
            $l_report = $l_dao->get_report($this->m_config['report_id']);

            if (empty($l_report)) {
                throw new InvalidArgumentException(isys_application::instance()->container->get('language')
                    ->get('LC__WIDGET__REPORT__NO_REPORT'));
            }

            $l_limit = 5000;

            if (isset($this->m_config['limit'])) {
                if ($this->m_config['limit'] > 0) {
                    $l_limit = $this->m_config['limit'];
                }
            }

            $l_report['isys_report__query'] = rtrim(trim($l_report['isys_report__query']), ';') . ' LIMIT ' . $l_limit;
            $l_report_data = isys_factory::get_instance('isys_module_report_pro', [])
                ->process_show_report($l_report['isys_report__query'], null, true, false, false, true, !!$l_report['isys_report__compressed_multivalue_results'], !!$l_report['isys_report__show_html']);

            $l_report_js = isys_module_report_pro::get_tpl_dir() . '/report.js';

            $this->m_tpl->assign('items_per_page', $this->m_config['count'])
                ->assign('report_id', $l_report['isys_report__id'])
                ->assign('report_title', $l_report['isys_report__title'])
                ->assign('report_description', $l_report['isys_report__description'])
                ->assign('report_js', $l_report_js)
                ->assign('compressedMultivalueCategories', $l_report['isys_report__compressed_multivalue_results'])
                ->assign('showHtml', $l_report['isys_report__show_html'])
                ->assign('columnNames', isys_format_json::encode((array)array_keys(reset($l_report_data))))
                ->assign('report_json', isys_format_json::encode($l_report_data));
        } catch (InvalidArgumentException $e) {
            // This should only happen, when no report is selected.
            $this->m_tpl->assign('friendly_error', true)
                ->assign('error_message', $e->getMessage());
        } catch (Exception $e) {
            $this->m_tpl->assign('error_message', $e->getMessage());
        }

        return $this->m_tpl->assign('unique_id', $p_unique_id)
            ->fetch($this->m_tpl_file);
    }
}
