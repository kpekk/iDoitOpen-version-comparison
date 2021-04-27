<?php

namespace idoit\Component\Browser\Condition;

use idoit\Component\Browser\Condition;
use isys_application;

class PersonGroupCondition extends Condition
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'LC__CMDB__OBJECT_BROWSER__BY_PERSON_GROUPS';
    }

    /**
     * @inheritdoc
     */
    public function retrieveOverview()
    {
        $language = isys_application::instance()->container->get('language');
        $overview = [];

        $countSql = 'SELECT COUNT(*) 
            FROM isys_person_2_group 
            INNER JOIN isys_obj ON isys_obj__id = isys_person_2_group__isys_obj__id__person
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_person_2_group__isys_obj__id__group = id 
            AND isys_obj__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) .
            $this->getFilterQueryConditions();

        $sql = 'SELECT isys_obj__id AS id, isys_obj__title AS title, (' . $countSql . ') AS count FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            INNER JOIN isysgui_cats ON isysgui_cats__id = isys_obj_type__isysgui_cats__id
            WHERE isys_obj__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
            AND isysgui_cats__const = "C__CATS__PERSON_GROUP"
            AND isys_obj_type__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) .
            $this->getFilterQueryConditions() . '
            HAVING count > 0;';

        $result = $this->dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $overview[$row['id']] = $language->get($row['title']) . ' (' . $row['count'] . ')';
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

        // Got the original query from "isys_cmdb_dao_category_s_person_group_members->get_data()".
        $sql = 'SELECT person.isys_obj__id as `person_id`
            FROM isys_person_2_group 
            INNER JOIN isys_cats_person_list ON isys_person_2_group__isys_obj__id__person = isys_cats_person_list__isys_obj__id 
            INNER JOIN isys_obj person ON person.isys_obj__id = isys_cats_person_list__isys_obj__id 
            INNER JOIN isys_obj_type ON isys_obj_type__id = person.isys_obj__isys_obj_type__id 
            WHERE person.isys_obj__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
            AND isys_person_2_group__isys_obj__id__group = ' . $this->dao->convert_sql_id($this->parameter) . '
            AND isys_cats_person_list__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) .
            $this->getFilterQueryConditions();

        $result = $this->dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $return[] = $row['person_id'];
        }

        return $return;
    }
}
