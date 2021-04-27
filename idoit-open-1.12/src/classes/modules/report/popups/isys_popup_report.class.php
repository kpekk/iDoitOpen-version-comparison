<?php

/**
 * i-doit
 *
 * Popup for Report
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_report extends isys_component_popup
{
    /**
     * Handles Smarty inclusion.
     *
     * @global  array                   $g_config
     *
     * @param   isys_component_template $p_tplclass (unused)
     * @param   mixed                   $p_params   (unused)
     *
     * @return  string
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        // This is never used - the popup will directly be triggered via JS callback.
    }

    /**
     * Handles module request.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  isys_component_template
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        try {
            switch ($_POST['func']) {
                case 'report_preview':
                    $this->report_preview();
                    break;
                case 'report_preview_sql':
                    $this->report_preview(false);
                    break;
                case 'show_duplicate':
                    $this->show_duplicate();
                    break;
                case 'show_category':
                    $this->show_category();
                    break;
            }

            die;
        } catch (Exception $e) {
            return $this->template->assign('error', $e->getMessage());
        }
    }

    /**
     * Shows popup for report categories
     */
    protected function show_category()
    {
        $l_has_right = isys_auth_report::instance()
            ->is_allowed_to(isys_auth::SUPERVISOR, 'REPORT_CATEGORY');

        if ($l_has_right) {
            $l_dao = isys_report_dao::instance(isys_application::instance()->database_system);
            $l_report_categories = $l_dao->get_report_categories();
            $l_data = [
                '-1' => $this->language->get('LC__REPORT__POPUP__REPORT_CATEGORY__ADD_NEW_CATEGORY')
            ];

            if (count($l_report_categories) > 0) {
                foreach ($l_report_categories as $l_category) {
                    $l_data[$this->language->get('Bestehende bearbeiten')][$l_category['isys_report_category__id']] = $l_category['isys_report_category__title'];
                }
            }

            $l_sort = (int)$l_dao->retrieve('SELECT count(*) AS count FROM isys_report_category')
                ->get_row_value('count');

            $this->template->activate_editmode()
                ->assign('category_selection', $l_data)
                ->assign('latest_id', $l_sort);
        } else {
            $this->template->assign('force_close', true);
            isys_notify::error($this->language->get('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_REPORT_CATEGORIES'));
        }

        $this->template->display(isys_module_report::get_tpl_dir() . '/report_category.tpl');
    }

    /**
     * Fills the fields in the duplicate report template
     */
    protected function show_duplicate()
    {
        $auth = isys_auth_report::instance();

        if (!is_array($_POST['id'])) {
            $this->template->assign('force_close', true);
            isys_notify::error($this->language->get('LC__REPORT__POPUP__REPORT_DUPLICATE__NO_REPORT_SELECTED'));

            return;
        }

        try {
            $l_has_right = $auth->check_report_right(isys_auth::SUPERVISOR, $_POST["id"][0]);
        } catch (isys_exception_auth $e) {
            $l_has_right = false;
        }

        $l_dao = isys_report_dao::instance(isys_application::instance()->container->get('database_system'));
        $l_report = $l_dao->get_report($_POST['id'][0]);

        $l_allowed_report_categories = $auth->get_allowed_report_categories();

        if ($l_allowed_report_categories === false) {
            $l_report_category_data = $l_dao->get_report_categories('Global', false)->get_row();
            $l_data[$l_report_category_data['isys_report_category__id']] = $l_report_category_data['isys_report_category__title'];
        } else {
            $l_report_categories = $l_dao->get_report_categories($l_allowed_report_categories);
            $l_data = [];
            if (count($l_report_categories) > 0) {
                foreach ($l_report_categories as $l_category) {
                    try {
                        // @see  ID-5548  Check if the user is allowed to see this category.
                        $auth->reports_in_category(isys_auth::CREATE, $l_category['isys_report_category__id']);

                        $l_data[$l_category['isys_report_category__id']] = $l_category['isys_report_category__title'];
                    } catch (Exception $e) {
                        // Do nothing.
                    }
                }
            }
        }

        $this->template->assign('category_selection', $l_data);

        if (!empty($_POST['id'][0]) && $l_has_right) {
            // @todo  How about using "rules" here?
            $this->template->activate_editmode()
                ->assign("chk_user_specific", $l_report["isys_report__user_specific"])
                ->assign("report_id", $l_report["isys_report__id"])
                ->assign("report_title", $l_report["isys_report__title"])
                ->assign("report_description", $l_report["isys_report__description"])
                ->assign("report_category", $l_report["isys_report__isys_report_category__id"]);
        } else {
            $this->template->assign('force_close', true);

            if (!$l_has_right) {
                isys_notify::error($this->language->get('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_DUPLICATING_REPORTS', [$l_report['isys_report__title']]));
            } else {
                isys_notify::error($this->language->get('LC__REPORT__POPUP__REPORT_DUPLICATE__NO_REPORT_SELECTED'));
            }
        }

        $this->template->display(isys_module_report::get_tpl_dir() . '/duplicate_report.tpl');
    }

    /**
     * This method builds the report and assigns the important data to the popup template
     *
     * @throws    Exception
     */
    protected function report_preview($p_query_builder = true)
    {
        global $g_comp_database;

        if ($p_query_builder) {
            if (!empty($_POST['report__HIDDEN_IDS']) && $_POST['report__HIDDEN_IDS'] != '[]') {
                $l_dao = new isys_cmdb_dao_category_property($g_comp_database);

                try {
                    $query = $l_dao->create_property_query_for_report(25);
                    $reportDao = new isys_report_dao($g_comp_database);

                    if ($reportDao->hasQueryOnlyInternalPlaceholder($query)) {
                        $this->show_report($reportDao->replacePlaceHolders($query), $_POST['compressed_multivalue_results']);
                    } else {
                        $this->template->assign('message', '<span>' . _L('LC__REPORT__REPORT_PREVIEW__NO') . '</span>');
                    }
                } catch (Exception $e) {
                    $this->template->assign('message_class', 'box-red')
                        ->assign('message', '<span>' . $this->language->get('LC__REPORT__POPUP__REPORT_PREVIEW__ERROR_GENERAL', [$e->getMessage()]) . '</span>');
                }
            } else {
                $this->template->assign('message', '<span>' . $this->language->get('LC__REPORT__POPUP__REPORT_PREVIEW__EMPTY_RESULT') . '</span>');
            }
        } elseif ($_POST['query'] != '') {
            try {
                $this->show_report(trim($_POST['query']), $_POST['compressed_multivalue_results'], $_POST['show_html']);
            } catch (Exception $e) {
                $this->template->assign('message', '<div class="mt5">' . $e->getMessage() . '</div>');
            }
        } else {
            $this->template->assign('message', '<span>' . $this->language->get('LC__REPORT__POPUP__REPORT_PREVIEW__EMPTY_RESULT') . '</span>');
        }

        $this->template->display(isys_module_report::get_tpl_dir() . '/popup/report_preview.tpl');
    }

    /**
     * Wrapper method for displaying the report.
     *
     * @param  string $p_query
     * @param  bool   $compressedMultivalueResults
     *
     * @throws Exception
     */
    private function show_report($p_query, $compressedMultivalueResults = false)
    {
        $l_mod_report = isys_module_report::get_instance();
        $unsortedColumns = [];

        if (method_exists($l_mod_report, 'process_show_report')) {
            $l_result = $l_mod_report->process_show_report($p_query, null, true, true, false, true, $compressedMultivalueResults);
            if ($l_result && count($l_result) > 0) {
                // Check whether grouping is enabled
                if ($compressedMultivalueResults) {
                    // Get fields
                    $unsortedColumns = array_keys(reset($l_result));

                    // Inform user about disabled sorting capabilities
                    $this->template->assign('groupingRelatedSortingHint', _L('LC__REPORT__VIEW__GROUPING_SORTING_HINT'));
                }

                $l_return = isys_format_json::encode($l_result);
                $this->template->assign('show_preview', true)
                    ->assign('l_json_data', $l_return)
                    ->assign('unsortedColumns', isys_format_json::encode($unsortedColumns));
            } else {
                $this->template->assign('message_class', 'p10')
                    ->assign('message', '<span>' . $this->language->get('LC__REPORT__POPUP__REPORT_PREVIEW__EMPTY_RESULT') . '</span>')
                    ->assign('show_preview', false);
            }
        }
    }
}
