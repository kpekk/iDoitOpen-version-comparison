<?php

/**
 * i-doit
 *
 * Factory for CMDB dialogs.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_factory_cmdb_dialog_dao extends isys_factory
{
    /**
     * Gets an instance of a category DAO.
     *
     * @param   string                  $p_table Table name of the desired dialog.
     * @param   isys_component_database $p_db    Database component.
     *
     * @return  isys_cmdb_dao_dialog
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_instance($p_table, $p_db = null)
    {
        // @todo  This is a "fix" for using the wrong parameter order.
        if (is_string($p_db) && is_object($p_table)) {
            $l_tmp = $p_table;
            $p_table = $p_db;
            $p_db = $l_tmp;
        }

        if (isset(self::$m_instances[$p_table])) {
            return self::$m_instances[$p_table];
        }

        return self::$m_instances[$p_table] = new isys_cmdb_dao_dialog($p_db, $p_table);
    }
}