<?php

/**
 * i-doit - Updates
 *
 * Migrating legacy licenses to be compatible with i-doit v1.12
 *
 * @package     i-doit
 * @subpackage  Update
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @author      Kevin Mauel <kmauel@i-doit.com>
 */

use Carbon\Carbon;
use idoit\Module\License\LicenseService;
use idoit\Module\License\LicenseServiceFactory;

/**
 * @var $g_comp_database isys_component_database
 */
global $g_comp_database, $g_absdir, $g_mandator_info, $g_comp_database_system;

// Set migrationId
$g_migration_identifier = "legacy_license_migration";

// Check whether migration was executed before
if ($this->is_migration_done($g_migration_identifier)) {
    $g_migration_log[] = '<span class="bold">Migration of legacy licenses has already been done.</span>';
} else {
    $g_migration_log[] = '<span class="bold">Starting Migration of legacy licenses.</span>';

    do {
        isys_settings::set('admin.active_license_distribution', 0);
        isys_settings::force_save();

        // Check if expires field is correctly modified
        $expiresField = $g_comp_database_system->retrieveArrayFromResource(
            $g_comp_database_system->query('SHOW COLUMNS FROM `isys_licence` WHERE (`Field` = \'isys_licence__expires\')')
        );

        // Ensure that expires will be always datetime
        if (isset($expiresField[0]['type']) && $expiresField[0]['type'] !== 'datetime') {
            $g_comp_database_system->query('ALTER TABLE isys_licence MODIFY isys_licence__expires DATETIME;') && $g_comp_database_system->commit();
        }

        $licenseService = LicenseServiceFactory::createDefaultLicenseService($g_comp_database_system, $g_license_token);

        foreach ($licenseService->getLegacyLicenses(false) as &$oldLicense) {
            $updateLicenseKeyData = false;

            // Unlimited licenses
            if ($oldLicense[LicenseService::C__LICENCE__OBJECT_COUNT] == 0) {
                $updateLicenseKeyData = true;

                $licenseData = [
                    LicenseService::C__LICENCE__OBJECT_COUNT => 99999999,
                    LicenseService::C__LICENCE__DB_NAME => $oldLicense[LicenseService::C__LICENCE__DB_NAME],
                    LicenseService::C__LICENCE__CUSTOMER_NAME => $oldLicense[LicenseService::C__LICENCE__CUSTOMER_NAME],
                    LicenseService::C__LICENCE__REG_DATE => $oldLicense[LicenseService::C__LICENCE__REG_DATE],
                    LicenseService::C__LICENCE__RUNTIME => $oldLicense[LicenseService::C__LICENCE__RUNTIME],
                    LicenseService::C__LICENCE__EMAIL => $oldLicense[LicenseService::C__LICENCE__EMAIL],
                    LicenseService::C__LICENCE__TYPE => $oldLicense[LicenseService::C__LICENCE__TYPE],
                    LicenseService::C__LICENCE__CONTRACT => $oldLicense[LicenseService::C__LICENCE__CONTRACT],
                    LicenseService::C__LICENCE__MAX_CLIENTS => $oldLicense[LicenseService::C__LICENCE__MAX_CLIENTS],
                    LicenseService::C__LICENCE__DATA => $oldLicense[LicenseService::C__LICENCE__DATA],
                ];

                $licenseData[LicenseService::C__LICENCE__DATA] = serialize($licenseData);
                $licenseData[LicenseService::C__LICENCE__KEY] = sha1($licenseData[LicenseService::C__LICENCE__DATA]);

                // no array_merge to preserve array keys
                $oldLicense = $licenseData + [
                        LicenseService::LEGACY_LICENSE_ID => $oldLicense[LicenseService::LEGACY_LICENSE_ID],
                        LicenseService::LEGACY_LICENSE_PARENT => $oldLicense[LicenseService::LEGACY_LICENSE_PARENT],
                        LicenseService::LEGACY_LICENSE_TYPE => $oldLicense[LicenseService::LEGACY_LICENSE_TYPE],
                        LicenseService::LEGACY_LICENSE_MANDATOR => $oldLicense[LicenseService::LEGACY_LICENSE_MANDATOR],
                        LicenseService::LEGACY_LICENSE_EXPIRES => $oldLicense[LicenseService::LEGACY_LICENSE_EXPIRES],
                ];
            }

            // Transfer object counts for licenses
            if (!empty($oldLicense[LicenseService::LEGACY_LICENSE_MANDATOR])) {
                $sql = "UPDATE isys_mandator SET isys_mandator__license_objects = '" . $oldLicense[LicenseService::C__LICENCE__OBJECT_COUNT] . "' WHERE isys_mandator__id = " . $oldLicense[LicenseService::LEGACY_LICENSE_MANDATOR];
                $g_comp_database_system->query($sql) && $g_comp_database_system->commit();
            }

            // Handle licenses with hosting parent, transfer object count to tenants and remove licenses
            if (!empty($oldLicense[LicenseService::LEGACY_LICENSE_PARENT])) {
                $sql = "DELETE FROM isys_licence WHERE isys_licence__id = " . $oldLicense[LicenseService::LEGACY_LICENSE_ID];
                $g_comp_database_system->query($sql) && $g_comp_database_system->commit();
            }

            $expires = null;

            if (isset($oldLicense[LicenseService::C__LICENCE__REG_DATE])) {
                if (
                    $oldLicense[LicenseService::LEGACY_LICENSE_TYPE] === LicenseService::C__LICENCE_TYPE__BUYERS_LICENCE ||
                    $oldLicense[LicenseService::LEGACY_LICENSE_TYPE] === LicenseService::C__LICENCE_TYPE__BUYERS_LICENCE_HOSTING
                ) {
                    $registrationDate = Carbon::createFromTimestamp($oldLicense[LicenseService::C__LICENCE__REG_DATE]);

                    $expires = $registrationDate->modify("+99 years");
                }

                if (isset($oldLicense[LicenseService::C__LICENCE__RUNTIME])) {
                    $days = (int) round(abs((($oldLicense[LicenseService::C__LICENCE__RUNTIME] / 60 / 60 / 24))));

                    $registrationDate = Carbon::createFromTimestamp($oldLicense[LicenseService::C__LICENCE__REG_DATE]);

                    $expires = $registrationDate->modify("+{$days} days");
                }
            }

            $sql = "UPDATE isys_licence SET isys_licence__expires = '" . ($expires !== null ? $expires->format(\DateTime::ATOM) : null) . "' WHERE isys_licence__id = " . $oldLicense[LicenseService::LEGACY_LICENSE_ID];
            $g_comp_database_system->query($sql) && $g_comp_database_system->commit();

            if ($updateLicenseKeyData === true) {
                $sql = "UPDATE isys_licence SET isys_licence__key = '" . $g_comp_database_system->escape_string($oldLicense[LicenseService::C__LICENCE__KEY]) . "', isys_licence__data = '" . $g_comp_database_system->escape_string($oldLicense[LicenseService::C__LICENCE__DATA]) . "' WHERE isys_licence__id = " . $oldLicense[LicenseService::LEGACY_LICENSE_ID];
                $g_comp_database_system->query($sql) && $g_comp_database_system->commit();
            }
        }
    } while (false);

    $g_migration_log[] = '<span class="bold">Migration finished!</span>';

    // Mark migration as done
    $this->migration_done($g_migration_identifier);
}
