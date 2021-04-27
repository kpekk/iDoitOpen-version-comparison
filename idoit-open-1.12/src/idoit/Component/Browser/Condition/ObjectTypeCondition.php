<?php

namespace idoit\Component\Browser\Condition;

use idoit\Component\Browser\Condition;
use isys_application;

class ObjectTypeCondition extends Condition
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'LC__CMDB__OBJECT_BROWSER__BY_OBJECT_TYPE';
    }

    /**
     * @inheritdoc
     */
    public function retrieveOverview()
    {
        $language = isys_application::instance()->container->get('language');
        $overview = [];

        $sql = 'SELECT isys_obj_type__id AS id, isys_obj_type__title AS title, isys_obj_type_group__title AS groupTitle, COUNT(*) AS count 
            FROM isys_obj_type
            INNER JOIN isys_obj_type_group ON isys_obj_type_group__id = isys_obj_type__isys_obj_type_group__id
            INNER JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id
            WHERE isys_obj__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
            AND isys_obj_type__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
            AND isys_obj_type__show_in_tree = 1
            ' . $this->getFilterQueryConditions() . '
            GROUP BY isys_obj_type__id
            HAVING count > 0;';

        $result = $this->dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $overview[$language->get($row['groupTitle'])][$row['id']] = $language->get($row['title']) . ' (' . $row['count'] . ')';
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

        $sql = 'SELECT isys_obj__id AS id
            FROM isys_obj 
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_obj_type__id = ' . $this->dao->convert_sql_id($this->parameter) . '
            AND isys_obj__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) .
            $this->getFilterQueryConditions() . ';';

        $result = $this->dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $return[] = $row['id'];
        }

        return $return;
    }
}
