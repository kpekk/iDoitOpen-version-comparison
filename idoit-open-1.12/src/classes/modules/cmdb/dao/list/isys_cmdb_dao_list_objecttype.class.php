<?php

/**
 * i-doit
 *
 * DAO: ObjectType lists.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_objecttype extends isys_component_dao_object_table_list
{
    /**
     * Retrieve all obj_types.
     *
     * @param string $p_strTableName unused
     * @param integer $p_object_id   unused
     * @param integer $p_cRecStatus  unused
     *
     * @return  isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_result($p_strTableName = null, $p_object_id = null, $p_cRecStatus = null)
    {
        $l_sql = "SELECT
			isys_obj_type__id,
			isys_obj_type__title,
			isys_obj_type_group__title,
			isys_obj_type__color AS color,
			isys_obj_type__overview AS overview,
			isys_obj_type__container AS container,
			isys_obj_type__isysgui_cats__id AS cats,
			isysgui_cats__title AS cats_title,
			COUNT(isys_obj__id) AS object_count,
			isys_obj_type__show_in_tree AS show_in_tree
			FROM isys_obj_type
			LEFT JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id
			LEFT JOIN isys_obj_type_group ON isys_obj_type__isys_obj_type_group__id = isys_obj_type_group__id
			LEFT JOIN isysgui_cats ON isys_obj_type__isysgui_cats__id = isysgui_cats__id
			WHERE isys_obj_type__const != 'C__OBJTYPE__LOCATION_GENERIC' ";

        $l_allowed_objecttypes = isys_auth_cmdb_object_types::instance()->get_allowed_objecttype_configs();

        if (is_array($l_allowed_objecttypes) && count($l_allowed_objecttypes) > 0) {
            $l_sql .= ' AND isys_obj_type__id IN (' . implode(',', $l_allowed_objecttypes) . ') ';
        } elseif ($l_allowed_objecttypes === false) {
            $l_sql .= ' AND isys_obj_type__id = FALSE ';
        }

        if ($_GET[C__CMDB__GET__OBJECTGROUP]) {
            $l_sql .= " AND (isys_obj_type_group__id = " . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECTGROUP]) . ")";
        }

        $l_sql .= "GROUP BY isys_obj_type__id;";

        return $this->retrieve($l_sql);
    }

    /**
     * Method for modifying the single row-data.
     *
     * @param  array &$p_arrRow
     */
    public function modify_row(&$p_arrRow)
    {
        $language = isys_application::instance()->container->get('language');

        $p_arrRow['show_in_tree'] = (!$p_arrRow['show_in_tree'])
            ? '<span class="text-red">' . $language->get('LC__UNIVERSAL__NO') . '</span>'
            : '<span class="text-green">' . $language->get('LC__UNIVERSAL__YES') . '</span>';

        $p_arrRow['overview'] = (!$p_arrRow['overview'])
            ? $language->get('LC__UNIVERSAL__NO')
            : $language->get('LC__UNIVERSAL__YES');

        $p_arrRow['container'] = (!$p_arrRow["container"])
            ? $language->get('LC__UNIVERSAL__NO')
            : $language->get('LC__UNIVERSAL__YES');

        $p_arrRow['cats'] = (!$p_arrRow['cats'])
            ? isys_tenantsettings::get('gui.empty_value', '-')
            : $language->get($p_arrRow['cats_title']);

        $p_arrRow["isys_obj_type__title"] = '<span class="vam">' .
            '<div style="margin-left:15px;">' . $language->get($p_arrRow["isys_obj_type__title"]) . '</div>' .
            '<div class="cmdb-marker" style="position:absolute; top:5px; left:5px; background:#' . $p_arrRow["color"] . ';"></div>' .
            '</span>';
    }

    /**
     * Method for modifying the single row-data for rendering.
     *
     * @param  array &$p_arrRow
     */
    public function format_row(&$p_arrRow)
    {
        $p_arrRow["object_count"] = '<span class="text-grey">' . $p_arrRow["object_count"] . '</span>';
    }

    /**
     * Method for returning the fields to display in the list.
     *
     * @return  array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_fields()
    {
        $language = isys_application::instance()->container->get('language');

        return [
            'isys_obj_type__id'          => 'LC__UNIVERSAL__ID',
            'isys_obj_type__title'       => 'LC__UNIVERSAL__TITLE',
            'isys_obj_type_group__title' => 'LC__CMDB__OBJTYPE__GROUP',
            'cats'                       => 'LC__REPORT__FORM__SELECT_PROPERTY_S',
            'overview'                   => 'LC__CMDB__CATG__OVERVIEW',
            'container'                  => 'LC__CMDB__OBJTYPE__LOCATION',
            'object_count'               => $language->get('LC_UNIVERSAL__OBJECT') . ' ' . $language->get('LC__POPUP__DUPLICATE__NUMBER'),
            'show_in_tree'               => 'LC__CMDB__OBJTYPE__SHOW_IN_TREE'
        ];
    }
}
