<?php

/**
 * i-doit Report Manager View
 *
 * @package     i-doit
 * @subpackage  Reports
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   Copyright 2018 - synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_report_view_cable_connections extends isys_report_view
{
    const UNUSED_CABLE = 0;
    const OPEN_CABLE   = 1;
    const USED_CABLE   = 2;

    private static $usageToLanguageVariable = [
        self::UNUSED_CABLE => 'LC__REPORT__VIEW__CABLE_CONNECTIONS__CABLE_UNUSED',
        self::OPEN_CABLE   => 'LC__REPORT__VIEW__CABLE_CONNECTIONS__CABLE_OPEN',
        self::USED_CABLE   => 'LC__REPORT__VIEW__CABLE_CONNECTIONS__CABLE_USED'
    ];

    /**
     * @return string
     */
    public static function name()
    {
        return 'LC__REPORT__VIEW__CABLE_CONNECTIONS__TITLE';
    }

    /**
     * @return string
     */
    public static function description()
    {
        return 'LC__REPORT__VIEW__CABLE_CONNECTIONS__TITLE';
    }

    /**
     * @return string
     */
    public function template()
    {
        return isys_module_report::getPath() . 'templates/view_cable_connections.tpl';
    }

    /**
     * @return string
     */
    public static function viewtype()
    {
        return 'LC__CMDB__CATG__CABLING_AND_PATCH';
    }

    /**
     * @throws isys_exception_database
     */
    public function start()
    {
        global $g_dirs;

        $dao = new isys_cmdb_dao($this->database);
        $quickInfo = new isys_ajax_handler_quick_info();
        $result = [];
        if (!defined('C__OBJTYPE__CABLE')) {
            return;
        }

        $sql = 'SELECT usages, isys_obj__id, isys_obj__title FROM (
                SELECT count(*) AS usages, isys_obj.isys_obj__id, isys_obj.isys_obj__title FROM isys_obj
                LEFT JOIN isys_cable_connection ON(
                    isys_cable_connection.isys_cable_connection__isys_obj__id = isys_obj.isys_obj__id
                )
                LEFT JOIN isys_catg_connector_list ON(
                    isys_catg_connector_list.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection.isys_cable_connection__id
                )
                WHERE isys_obj.isys_obj__isys_obj_type__id = ' . C__OBJTYPE__CABLE . ' AND isys_obj.isys_obj__status = ' . C__RECORD_STATUS__NORMAL . ' AND isys_catg_connector_list.isys_catg_connector_list__isys_cable_connection__id IS NOT NULL GROUP BY isys_obj.isys_obj__id
                UNION
                SELECT count(*) - 1 AS usages, isys_obj.isys_obj__id, isys_obj.isys_obj__title FROM isys_obj
                LEFT JOIN isys_cable_connection ON(
                    isys_cable_connection.isys_cable_connection__isys_obj__id = isys_obj.isys_obj__id
                )
                LEFT JOIN isys_catg_connector_list ON(
                    isys_catg_connector_list.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection.isys_cable_connection__id
                )
                WHERE isys_obj.isys_obj__isys_obj_type__id = ' . C__OBJTYPE__CABLE . ' AND isys_obj.isys_obj__status = ' . C__RECORD_STATUS__NORMAL . ' AND isys_catg_connector_list.isys_catg_connector_list__isys_cable_connection__id IS NULL GROUP BY isys_obj.isys_obj__id
            ) closure GROUP BY isys_obj__id;';

        $cableObjectsSql = 'SELECT isys_catg_connector_list__isys_obj__id AS objId, isys_catg_connector_list__title AS title
            FROM isys_catg_connector_list
            LEFT JOIN isys_cable_connection ON(
                isys_catg_connector_list.isys_catg_connector_list__isys_cable_connection__id = isys_cable_connection.isys_cable_connection__id
            )
            WHERE isys_cable_connection.isys_cable_connection__isys_obj__id = %s';

        $resource = $dao->retrieve($sql);

        if ($resource->num_rows() > 0) {
            while ($row = $resource->get_row()) {
                $connectedObjects = [];

                if ($row['usages'] > self::UNUSED_CABLE) {
                    $connectedObjectData = $dao->retrieve(sprintf($cableObjectsSql, $row['isys_obj__id']))
                        ->__as_array();

                    if (!empty($connectedObjectData)) {
                        foreach ($connectedObjectData as $connectedObject) {
                            $connectedObjects[] = $quickInfo->get_quick_info($connectedObject['objId'], $connectedObject['title'], C__LINK__CATG, false, [
                                C__CMDB__GET__CATG => defined_or_default('C__CATG__CONNECTOR')
                            ]);
                        }
                    }
                }

                if ($row['usages'] !== self::USED_CABLE) {
                    $result[self::$usageToLanguageVariable[$row['usages']]][$row['isys_obj__id']] = $row;
                    $result[self::$usageToLanguageVariable[$row['usages']]][$row['isys_obj__id']]['quickInfoLink'] = $quickInfo->get_quick_info(
                        $row['isys_obj__id'],
                        $row['isys_obj__title'],
                        C__LINK__CATG,
                        false,
                        [
                            C__CMDB__GET__CATG => defined_or_default('C__CATG__GLOBAL')
                        ]
                    );
                    $result[self::$usageToLanguageVariable[$row['usages']]][$row['isys_obj__id']]['connectedObjects'] = $connectedObjects;
                }
            }
        }

        $this->template
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->assign("dir_images", $g_dirs["images"])
            ->assign("viewContent", $result);
    }
}
