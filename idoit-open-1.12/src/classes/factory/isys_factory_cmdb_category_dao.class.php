<?php

/**
 * i-doit
 *
 * Factory for CMDB category DAOs
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @version     Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_factory_cmdb_category_dao extends isys_factory_dao
{
    /**
     * Contains information about all categories received from database.
     *
     * @var  array  Associative, multidimensional array with category types as keys and categories as values.
     */
    protected static $m_categories = [];

    /**
     * Gets an instance of a category DAO by the category identifier.
     *
     * @param   integer                 $p_type Category type identifier
     * @param   integer                 $p_id   Category identifier
     * @param   isys_component_database $p_db   Database component
     *
     * @return  isys_cmdb_dao_category
     */
    public static function get_instance_by_id($p_type, $p_id, isys_component_database $p_db)
    {
        if (is_countable(self::$m_categories) && count(self::$m_categories) === 0) {
            self::build_category_list($p_db);
        }

        return self::get_instance(self::$m_categories[$p_type][$p_id]['class_name'], $p_db);
    }

    /**
     * Builds the category list.
     *
     * @param isys_component_database $p_db
     */
    protected static function build_category_list(isys_component_database &$p_db)
    {
        $l_cmdb_dao = new isys_cmdb_dao($p_db);

        self::$m_categories = $l_cmdb_dao->get_all_categories();
    }
}
