<?php
/**
 * i-doit - Updates
 *
 * Migrating api module to be compatible with i-doit v1.12
 *
 * @package     i-doit
 * @subpackage  Update
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @author      Selcuk Kekec <skekec@i-doit.com>
 */

/**
 * @var $g_comp_database isys_component_database
 */
global $g_comp_database, $g_absdir, $g_mandator_info, $g_comp_database_system;

// Set migrationId
$g_migration_identifier = "api_update_migration";

// Check whether migration was executed before
if ($this->is_migration_done($g_migration_identifier)) {
    $g_migration_log[] = '<span class="bold">Api-update-migration has already been done.</span>';
} else {
    $g_migration_log[] = '<span class="bold">Starting api-update-migration.</span>';

    do {
        /**
         * 1. Check whether api addon is present
         */
        $apiPath = $g_absdir . '/src/classes/modules/api';

        // Check whether api addon is installed
        if (!file_exists($apiPath)) {
            $g_migration_log[] = '<span class="bold">API-Addon is not installed.</span>';

            continue;
        }

        // Check whether version is incompatible with i-doit v1.12
        if (file_get_contents($apiPath . '/VERSION') == '1.10') {
            $g_migration_log[] = '<span class="bold">API-Addon is already the newest available version.</span>';

            continue;
        }

        $g_migration_log[] = '<span class="bold">API-Addon is present in i-doit installation.</span>';

        /**
         * 2. Install new api addon package
         */
        try {
            $deleted = 0;
            $undeleted = 0;
            // Delete old api files
            if (isys_glob_delete_recursive($apiPath, $deleted, $undeleted)) {
                $g_migration_log[] = '<span class="bold green">Old api files removed successfully.</span>';
            }
        } catch (Exception $e) {
            $g_migration_log[] = '<span class="bold red">Deletion of old api files failed.</span>';
        }

        try {
            // Unpack new api addon package zip
            if (unpackAddon(__DIR__ . '/idoit-api-1.10.zip')) {
                $g_migration_log[] = '<span class="bold green">New version of API-Addon installed successfully.</span>';
            }
        } catch (Exception $e) {
            $g_migration_log[] = '<span class="bold red">Unpacking of API package zip failed.</span>';
        }

        /**
         * 3. Check whether module is installed and insert default settings set with best backward compatibility
         */
        $sql = 'SELECT * FROM isys_module WHERE isys_module__const= \'C__MODULE__API\'';

        $resource = $g_comp_database->query($sql);

        // Check whether module is installed
        if ($g_comp_database->num_rows($resource)) {
            $moduleUpdateSql = 'UPDATE isys_module SET ' . 'isys_module__date_install = NOW() WHERE isys_module__const = \'C__MODULE__API\';';

            // Update 'installed' information
            $g_comp_database->query($moduleUpdateSql);

            /**
             * Create default settings for updated api module
             */
            if (isys_tenantsettings::get('api.use-auth') || isys_tenantsettings::get('api.authenticated-users-only')) {
                isys_tenantsettings::set('api.authenticated-users-only', 1);
            }

            isys_settings::set('api.log-level', 300);
            if (isys_tenantsettings::get('logging.system.api')) {
                isys_tenantsettings::set('api.log-level', 200);
            }

            isys_settings::set('api.validation', 1);
            isys_tenantsettings::set('api.validation', 0);
        }
    } while (false);

    $g_migration_log[] = '<span class="bold">Migration finished!</span>';

    // Mark migration as done
    $this->migration_done($g_migration_identifier);
}
