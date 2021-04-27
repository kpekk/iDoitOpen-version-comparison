<?php

/**
 * Class isys_report_view
 */
abstract class isys_report_view implements isys_report_view_interface
{
    /**
     *
     */
    const c_sql_view = 1;
    /**
     *
     */
    const c_php_view = 2;

    /**
     * @return mixed
     */
    abstract public function ajax_request();

    /**
     * @return mixed
     */
    abstract public function start();

    /**
     * Returns the report-view's template.
     *
     * @return  null
     */
    public function template()
    {
        return null;
    }

    /**
     * Determines, if a report view is brought in by an external source (module?).
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function external()
    {
        return false;
    }

    /**
     * Naked constructor.
     */
    public function __construct()
    {
        ;
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
     * @return mixed
     */
    public function init();

    /**
     * @return mixed
     */
    public static function name();

    /**
     * @return mixed
     */
    public static function description();

    /**
     * @return mixed
     */
    public static function type();

    /**
     * @return mixed
     */
    public static function viewtype();
}