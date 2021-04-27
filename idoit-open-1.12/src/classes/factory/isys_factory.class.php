<?php

/**
 * i-doit
 *
 * Class factory
 *
 * @deprecated  Please use something else - if it is REALLY NECESSARY, use idoit\Component\Provider\Factory
 *
 * @package     i-doit
 * @subpackage  Factory
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_factory
{
    /**
     * Contains self representations of factorized classes.
     *
     * @var  array  Associative array of instances
     */
    protected static $m_instances = [];

    /**
     * Gets an instance of a class.
     *
     * @param   string $p_class
     * @param   mixed  $p_params
     *
     * @return  Object
     */
    public static function get_instance($p_class, $p_params = null)
    {
        if (isset(self::$m_instances[$p_class])) {
            return self::$m_instances[$p_class];
        }

        if (method_exists($p_class, 'get_instance')) {
            return (self::$m_instances[$p_class] = call_user_func_array([$p_class, 'get_instance'], $p_params));
        }

        // @todo Find a way to call the constructor and pass variable params - "call_user_func_array(array($p_class, '__construct') ..." does not work.
        return (self::$m_instances[$p_class] = new $p_class($p_params));
    }
}
