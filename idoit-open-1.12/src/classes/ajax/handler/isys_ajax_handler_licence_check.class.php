<?php

/**
 * AJAX
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_licence_check extends isys_ajax_handler
{

    /**
     * @return bool
     */
    public function init()
    {
        global $g_comp_database_system;
        global $g_comp_database;
        global $g_config;

        if (class_exists("isys_module_licence")) {
            $l_licence = new isys_module_licence();

            try {
                $l_licences = $l_licence->get_installed_licences($g_comp_database_system);

                if (!is_null($l_licences) && is_countable($l_licences)) {
                    if (count($l_licences) > 0) {
                        foreach ($l_licences as $l_lic) {
                            $l_licence->check_licence($l_lic["licence_data"], $g_comp_database);
                        }
                    }
                } else {
                    throw new isys_exception_licence(isys_application::instance()->container->get('language')
                        ->get("LC__LICENCE__NO_LICENCE"), 1);
                }

            } catch (isys_exception_licence $e) {
                // Try: isys_application::instance()->www_path
                $l_html = $e->getMessage() . " (" . $e->get_errorcode() . ")<br />" . "<a href=\"" . $g_config["www_dir"] . "index.php?moduleID=" . defined_or_default('C__MODULE__SYSTEM') .
                    "&handle=licence_overview\">Zur Lizenzverwaltung</a>";

                isys_application::instance()->template->assign("error_topic", "Lizenzen")
                    ->assign("g_error", $l_html)
                    ->display("exception.tpl");
            }
        }

        return true;
    }

    public function checkLicense()
    {
        if (class_exists("isys_module_licence")) {
            // todo licensing 2.0
            $l_licence = new isys_module_licence();
            $l_licence->verify();
        }
    }
}
