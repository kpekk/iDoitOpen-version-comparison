<?php
/**
 * i-doit
 *
 * Static constant not registered by the dynamic constant manager.
 * Please empty this list every major release.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

// @see ID-934 -- global categories
if (!defined('C__CMDB__SUBCAT__NETWORK_PORT') && defined('C__CATG__NETWORK_PORT')) {
    /**
     * @deprecated  Use "C__CATG__NETWORK_PORT" instead.
     */
    define('C__CMDB__SUBCAT__NETWORK_PORT', C__CATG__NETWORK_PORT);
}

if (!defined('C__CMDB__SUBCAT__NETWORK_INTERFACE_P') && defined('C__CATG__NETWORK_INTERFACE')) {
    /**
     * @deprecated  Use "C__CATG__NETWORK_INTERFACE" instead.
     */
    define('C__CMDB__SUBCAT__NETWORK_INTERFACE_P', C__CATG__NETWORK_INTERFACE);
}

if (!defined('C__CMDB__SUBCAT__NETWORK_INTERFACE_L') && defined('C__CATG__NETWORK_LOG_PORT')) {
    /**
     * @deprecated  Use "C__CATG__NETWORK_LOG_PORT" instead.
     */
    define('C__CMDB__SUBCAT__NETWORK_INTERFACE_L', C__CATG__NETWORK_LOG_PORT);
}

if (!defined('C__CMDB__SUBCAT__STORAGE__DEVICE') && defined('C__CATG__STORAGE_DEVICE')) {
    /**
     * @deprecated  Use "C__CATG__STORAGE_DEVICE" instead.
     */
    define('C__CMDB__SUBCAT__STORAGE__DEVICE', C__CATG__STORAGE_DEVICE);
}

if (!defined('C__CMDB__SUBCAT__NETWORK_PORT_OVERVIEW') && defined('C__CATG__NETWORK_PORT_OVERVIEW')) {
    /**
     * @deprecated  Use "C__CATG__NETWORK_PORT_OVERVIEW" instead.
     */
    define('C__CMDB__SUBCAT__NETWORK_PORT_OVERVIEW', C__CATG__NETWORK_PORT_OVERVIEW);
}

// @see ID-934 -- specific categories
if (!defined('C__CMDB__SUBCAT__LICENCE_LIST') && defined('C__CATS__LICENCE_LIST')) {
    /**
     * @deprecated  Use "C__CATS__LICENCE_LIST" instead.
     */
    define('C__CMDB__SUBCAT__LICENCE_LIST', C__CATS__LICENCE_LIST);
}

if (!defined('C__CMDB__SUBCAT__LICENCE_OVERVIEW') && defined('C__CATS__LICENCE_OVERVIEW')) {
    /**
     * @deprecated  Use "C__CATS__LICENCE_OVERVIEW" instead.
     */
    define('C__CMDB__SUBCAT__LICENCE_OVERVIEW', C__CATS__LICENCE_OVERVIEW);
}

if (!defined('C__CMDB__SUBCAT__EMERGENCY_PLAN_LINKED_OBJECT_LIST') && defined('C__CATS__EMERGENCY_PLAN_LINKED_OBJECTS')) {
    /**
     * @deprecated  Use "C__CATS__EMERGENCY_PLAN_LINKED_OBJECTS" instead.
     */
    define('C__CMDB__SUBCAT__EMERGENCY_PLAN_LINKED_OBJECT_LIST', C__CATS__EMERGENCY_PLAN_LINKED_OBJECTS);
}

if (!defined('C__CMDB__SUBCAT__EMERGENCY_PLAN') && defined('C__CATS__EMERGENCY_PLAN_ATTRIBUTE')) {
    /**
     * @deprecated  Use "C__CATS__EMERGENCY_PLAN_ATTRIBUTE" instead.
     */
    define('C__CMDB__SUBCAT__EMERGENCY_PLAN', C__CATS__EMERGENCY_PLAN_ATTRIBUTE);
}

if (!defined('C__CMDB__SUBCAT__WS_NET_TYPE') && defined('C__CATS__WS_NET_TYPE')) {
    /**
     * @deprecated  Use "C__CATS__EMERGENCY_PLAN_ATTRIBUTE" instead.
     */
    define('C__CMDB__SUBCAT__WS_NET_TYPE', C__CATS__WS_NET_TYPE);
}

if (!defined('C__CMDB__SUBCAT__WS_ASSIGNMENT') && defined('C__CATS__WS_ASSIGNMENT')) {
    /**
     * @deprecated  Use "C__CATS__EMERGENCY_PLAN_ATTRIBUTE" instead.
     */
    define('C__CMDB__SUBCAT__WS_ASSIGNMENT', C__CATS__WS_ASSIGNMENT);
}

if (!defined('C__CMDB__SUBCAT__FILE_OBJECTS') && defined('C__CATS__FILE_OBJECTS')) {
    /**
     * @deprecated  Use "C__CATS__EMERGENCY_PLAN_ATTRIBUTE" instead.
     */
    define('C__CMDB__SUBCAT__FILE_OBJECTS', C__CATS__FILE_OBJECTS);
}

if (!defined('C__CMDB__SUBCAT__FILE_VERSIONS') && defined('C__CATS__FILE_VERSIONS')) {
    /**
     * @deprecated  Use "C__CATS__EMERGENCY_PLAN_ATTRIBUTE" instead.
     */
    define('C__CMDB__SUBCAT__FILE_VERSIONS', C__CATS__FILE_VERSIONS);
}

if (!defined('C__CMDB__SUBCAT__FILE_ACTUAL') && defined('C__CATS__FILE_ACTUAL')) {
    /**
     * @deprecated  Use "C__CATS__EMERGENCY_PLAN_ATTRIBUTE" instead.
     */
    define('C__CMDB__SUBCAT__FILE_ACTUAL', C__CATS__FILE_ACTUAL);
}
// --- end of ID-934

if (!defined('ISYS_EMPTY')) {
    /**
     * @deprecated
     */
    define('ISYS_EMPTY', '');
}

if (!defined('C__CMDB__LOCATION_SEPARATOR')) {
    /**
     * @deprecated
     */
    define('C__CMDB__LOCATION_SEPARATOR', ' > ');
}

if (!defined('C__CMDB__CONNECTOR_SEPARATOR')) {
    /**
     * @deprecated
     */
    define('C__CMDB__CONNECTOR_SEPARATOR', ' > ');
}

if (!defined('C__CMDB__GET__NETPORT')) {
    /**
     * @deprecated
     */
    define('C__CMDB__GET__NETPORT', 'NetportID');
}

if (!defined('C__RACK_INSERTION__BACK')) {
    /**
     * @deprecated Please use "C__INSERTION__REAR".
     */
    define('C__RACK_INSERTION__BACK', 0);
}

if (!defined('C__RACK_INSERTION__FRONT')) {
    /**
     * @deprecated Please use "C__INSERTION__FRONT".
     */
    define('C__RACK_INSERTION__FRONT', 1);
}

if (!defined('C__RACK_INSERTION__BOTH')) {
    /**
     * @deprecated Please use "C__INSERTION__BOTH".
     */
    define('C__RACK_INSERTION__BOTH', 2);
}

if (!defined('C__CATEGORY_DATA__TAG')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__TAG', 'tag');
}

if (!defined('C__CATEGORY_DATA__TITLE')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__TITLE', 'title');
}

if (!defined('C__CATEGORY_DATA__FORMTAG')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__FORMTAG', 'formtag');
}

if (!defined('C__CATEGORY_DATA__EXPORT')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__EXPORT', 'export');
}

if (!defined('C__CATEGORY_DATA__EXPORT_HELPER')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__EXPORT_HELPER', 'helper');
}

if (!defined('C__CATEGORY_DATA__PARAM')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__PARAM', 'param');
}

if (!defined('C__CATEGORY_DATA__EXPORT_PARAM')) {
    /**
     * Parameter(s) for the export helper class' constructor.
     *
     * @deprecated
     * @todo  Refactor to 'export_param'.
     */
    define('C__CATEGORY_DATA__EXPORT_PARAM', 'param');
}

if (!defined('C__CATEGORY_DATA__IMPORT_HELPER')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__IMPORT_HELPER', 'helper');
}

if (!defined('C__CATEGORY_DATA__IMPORT_PARAM')) {
    /**
     * Parameter(s) for the export helper class' constructor.
     *
     * @deprecated
     * @todo  Refactor to 'import_param'.
     */
    define('C__CATEGORY_DATA__IMPORT_PARAM', 'param');
}

if (!defined('C__CATEGORY_DATA__VALIDATE')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__VALIDATE', 'validate');
}

if (!defined('C__CATEGORY_DATA__TYPE')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__TYPE', 'type');
}

if (!defined('C__CRYPT_KEY')) {
    /**
     * @deprecated
     */
    define('C__CRYPT_KEY', '');
}

if (!defined('C__WRITE_EXCEPTION_LOGS')) {
    /**
     * @deprecated
     */
    define('C__WRITE_EXCEPTION_LOGS', true);
}

if (!defined('C__CATEGORY_DATA__FIELD')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__FIELD', 'field');
}

if (!defined('C__CATEGORY_DATA__REF')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__REF', 'ref');
}

if (!defined('C__CATEGORY_DATA__TABLE')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__TABLE', 'table');
}

if (!defined('C__CATEGORY_DATA__FILTER')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__FILTER', 'filter');
}

if (!defined('C__CATEGORY_DATA__IMPORT')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__IMPORT', 'import');
}

if (!defined('C__CATEGORY_DATA__OPTIONAL')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__OPTIONAL', 'optional');
}

if (!defined('C__CATEGORY_DATA__DEFAULT')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__DEFAULT', 'default');
}

if (!defined('C__CATEGORY_DATA__VALUE')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__VALUE', 'value');
}

if (!defined('C__CATEGORY_DATA__REPORT')) {
    /**
     * @deprecated
     */
    define('C__CATEGORY_DATA__REPORT', 'report');
}

if (!defined('ISYS_NULL')) {
    /**
     * @deprecated
     */
    define('ISYS_NULL', null);
}

if (!defined('CRLF')) {
    /**
     * @deprecated
     */
    define('CRLF', "\r\n");
}
