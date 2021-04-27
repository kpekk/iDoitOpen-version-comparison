<?php
/**
 * i-doit - Updates
 *
 * Migrating Nagios module to be compatible with i-doit v1.12
 *
 * @package     i-doit
 * @subpackage  Update
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @author      Leonard Fischer <lfischer@i-doit.com>
 */

global $g_absdir;

// Set migration identifier.
$g_migration_identifier = 'workflow_update_migration';

// Check whether migration was executed before
if ($this->is_migration_done($g_migration_identifier)) {
    $g_migration_log[] = '<span class="bold">Workflow update migration has already been done.</span>';
} else {
    $g_migration_log[] = '<span class="bold">Starting Workflow update migration.</span>';

    // Define the nagios add-on directory.
    $workflowPath = $g_absdir . '/src/classes/modules/workflow';

    // Check if the Nagios add-on has already been updated (the VERSION file did not exist before).
    if (file_exists($workflowPath . '/VERSION')) {
        $g_migration_log[] = '<span class="bold">Workflow add-on has already been updated.</span>';
    } else {
        $g_migration_log[] = '<span class="bold">Workflow add-on will now be updated.</span>';

        try {
            // Unpack new Nagios add-on package zip
            if (unpackAddon(__DIR__ . '/idoit-workflow-1.0.0.zip')) {
                $g_migration_log[] = '<span class="bold green">Current Workflow add-on has been installed successfully.</span>';
            }
        } catch (Exception $e) {
            $g_migration_log[] = '<span class="bold red">Unpacking of Workflow add-on package zip failed: ' . $e->getMessage() . '</span>';
        }

        $g_migration_log[] = '<span class="bold">Migration finished!</span>';

        // Mark migration as done
        $this->migration_done($g_migration_identifier);
    }
}
