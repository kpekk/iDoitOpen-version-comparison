<?php

/**
 * i-doit
 *
 * Export helper for global category contact
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_global_contact_export_helper extends isys_export_helper
{
    /**
     * Export helper for contact information
     *
     * @return array
     * @throws isys_exception_database
     */
    public function exportContactAssignment()
    {
        // Get contact dao
        $daoContactAssignment = isys_cmdb_dao_category_g_contact::instance($this->m_database);

        // Export contact related information for xml processing
        $contacts = $this->export_contact($this->m_row["isys_connection__isys_obj__id"], $daoContactAssignment->get_objTypeID($this->m_row["isys_connection__isys_obj__id"]));

        return $contacts;
    }
}
