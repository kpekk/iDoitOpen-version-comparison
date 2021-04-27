<?php

/**
 * i-doit
 *
 * Export helper for global category hostaddress
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_specific_application_assigned_obj_export_helper extends isys_export_helper
{
    /**
     * Export Helper for property assigned_variant for specific category application installation
     *
     * @param $p_value
     *
     * @return array
     */
    public function applicationAssignedVariant($p_value)
    {
        if (!empty($p_value)) {
            $l_dao = isys_cmdb_dao_category_s_application_variant::instance($this->m_database);
            $l_data = $l_dao->get_data($p_value)
                ->get_row();

            return [
                'id'      => $p_value,
                'title'   => $l_data['isys_cats_app_variant_list__title'],
                'type'    => 'C__CATS__APPLICATION_VARIANT',
                'variant' => $l_data['isys_cats_app_variant_list__variant']
            ];
        }

        return null;
    }

    /**
     * Import Helper for property assigned_variant for specific category application installation
     *
     * @param $p_value
     *
     * @return array
     */
    public function applicationAssignedVariant_import($p_value)
    {
        if (is_array($p_value[C__DATA__VALUE])) {
            $l_data = $p_value[C__DATA__VALUE];
            if (defined('C__CATS__APPLICATION_VARIANT') && array_key_exists($l_data['id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_SPECIFIC][constant('C__CATS__APPLICATION_VARIANT')])) {
                return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_SPECIFIC][constant('C__CATS__APPLICATION_VARIANT')][$l_data['id']];
            }
        }

        return null;
    }
}