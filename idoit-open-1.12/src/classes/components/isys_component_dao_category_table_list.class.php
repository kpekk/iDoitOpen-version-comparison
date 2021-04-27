<?php

/**
 * i-doit
 *
 * DAO for category table list template.
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Leonard Fischer <lfischer@i-doit.com>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_dao_category_table_list extends isys_cmdb_dao_list
{
    /**
     * Static build method which will automatically deal with the database component and the category DAO.
     *
     * @param isys_component_database $p_database
     * @param isys_cmdb_dao_category  $p_cat
     *
     * @return static
     */
    public static function build(isys_component_database $p_database, isys_cmdb_dao_category $p_cat)
    {
        $instance = new static($p_database);
        $instance->set_dao_category($p_cat);

        return $instance;
    }
}