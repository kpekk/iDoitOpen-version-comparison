<?php

/**
 * Class isys_report_view
 */
abstract class isys_report_view implements isys_report_view_interface
{
    /**
     * @todo        Remove in i-doit 1.13
     * @deprecated  Unused constant, will be removed in i-doit 1.13
     */
    const c_sql_view = 1;

    /**
     * @todo        Remove in i-doit 1.13
     * @deprecated  Unused constant, will be removed in i-doit 1.13
     */
    const c_php_view = 2;

    /**
     * @var isys_component_template_language_manager
     */
    protected $language;

    /**
     * @var isys_component_template
     */
    protected $template;

    /**
     * @var isys_component_database
     */
    protected $database;

    /**
     * @return void
     */
    public function ajax_request()
    {
        isys_core::send_header('Content-Type', 'application/json');

        echo '{"success":true, "data":null, "message":""}';
        die;
    }

    /**
     * Determines, if a report view is brought in by an external source (module?).
     *
     * @todo        Remove in i-doit 1.13
     * @deprecated  Unused method, will be removed in i-doit 1.13
     * @return      boolean
     */
    public function external()
    {
        return false;
    }

    /**
     * isys_report_view constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->template = isys_application::instance()->container->get('template');
        $this->language = isys_application::instance()->container->get('language');
        $this->database = isys_application::instance()->container->get('database');
    }
}

/**
 * i-doit Report Manager Views
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   Copyright 2012 - synetics GmbH
 */
interface isys_report_view_interface
{
    /**
     * Method for processing the report view logic itself.
     *
     * @return void
     */
    public function start();

    /**
     * Will be called for ajax requests, should directly use ECHO and end the request.
     *
     * @return void
     */
    public function ajax_request();

    /**
     * Returns the absolute path to the report-views template.
     *
     * @return string
     */
    public function template();

    /**
     * Returns a language constant of the report-views name.
     *
     * @return string
     */
    public static function name();

    /**
     * Returns a language constant of the report-views description.
     *
     * @return string
     */
    public static function description();

    /**
     * Returns a language constant of the report-views type.
     *
     * @return string
     */
    public static function viewtype();
}
