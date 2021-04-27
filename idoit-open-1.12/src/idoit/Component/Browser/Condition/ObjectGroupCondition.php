<?php

namespace idoit\Component\Browser\Condition;

use idoit\Component\Browser\Condition;
use isys_cmdb_dao_category_s_group as daoCategorySGroup;

class ObjectGroupCondition extends Condition
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'LC__CMDB__OBJECT_BROWSER__BY_GROUPS';
    }

    /**
     * @inheritdoc
     */
    public function retrieveOverview()
    {
        $overview = [];

        $countSql = 'SELECT COUNT(*) 
            FROM isys_cats_group_list
            INNER JOIN isys_connection ON isys_connection__id = isys_cats_group_list__isys_connection__id
			INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_obj__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
            AND isys_cats_group_list__isys_obj__id = id ' .
            $this->getFilterQueryConditions();

        $sql = 'SELECT isys_obj__id AS id, isys_obj__title AS title, (' . $countSql . ') AS count 
            FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            INNER JOIN isysgui_cats ON isysgui_cats__id = isys_obj_type__isysgui_cats__id
            WHERE isysgui_cats__const = "C__CATS__GROUP"
            AND isys_obj__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
            AND isys_obj_type__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) .
            $this->getFilterQueryConditions() . '
            HAVING count > 0;';

        $result = $this->dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $overview[$row['id']] = $row['title'] . ' (' . $row['count'] . ')';
        }

        natcasesort($overview);

        return $overview;
    }

    /**
     * @inheritdoc
     */
    public function retrieveObjects()
    {
        $return = [];

        $result = daoCategorySGroup::instance($this->db)
            ->get_data(null, $this->parameter, $this->getFilterQueryConditions(), null, C__RECORD_STATUS__NORMAL);

        while ($row = $result->get_row()) {
            $return[] = $row['isys_connection__isys_obj__id'];
        }

        return $return;
    }
}
