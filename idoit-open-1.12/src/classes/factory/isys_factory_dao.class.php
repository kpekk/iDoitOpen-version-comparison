<?php

class isys_factory_dao
{
    /**
     * Contains self representations of factorized classes.
     *
     * @var  array  Associative array of instances
     */
    protected static $m_instances = [];

    /**
     * Gets an instance of a DAO.
     *
     * @param   string                  $p_class
     * @param   isys_component_database $p_db
     *
     * @return isys_cmdb_dao
     * @throws isys_exception_general
     */
    public static function get_instance($p_class, isys_component_database $p_db)
    {
        if (!$p_class) {
            throw new isys_exception_general('Instance class is not set in ' . __FILE__ . ':' . __LINE__);
        }

        if (!isset(self::$m_instances[$p_class])) {
            self::$m_instances[$p_class] = new $p_class($p_db);
        }

        return self::$m_instances[$p_class];
    }
}
