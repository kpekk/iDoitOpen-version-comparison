<?php
/**
 * @author     Dennis Stuecken
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
global $g_comp_database, $g_config, $g_absdir;

$l_template = isys_component_template::instance();

if (!C__ENABLE__LICENCE) {
    throw new Exception("Licence pages are not available in this i-doit version! " . "You need to subscribe at <a href=\"http://www.i-doit.com\">http://www.i-doit.com</a>.");
}

/* Load statistics module */
include_once($g_absdir . '/src/classes/modules/statistics/init.php');

$app = isys_application::instance();

$l_licences = new isys_module_licence();
$l_dao_mandator = new isys_component_dao_mandator($app->database_system);

$l_licences_single = [];
$l_licences_hosting = [];

/* Request processing */
switch ($_POST["action"]) {
    case "delete":

        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $l_licence_data) {
                list($l_tenant_id, $l_licence_id, $licenceType) = explode(",", $l_licence_data);

                if ($l_licence_id > 0 && $l_tenant_id >= 0) {
                    if ($licenceType === 'hosting' && (int)$_POST['multiLicenceAction']) {
                        // Delete all installed child licences referenced by the parent licence
                        $l_licences->deleteLicenceByParentLicence($app->database_system, $l_licence_id);
                    }

                    //connect_mandator($l_tenant_id);
                    $l_licences->delete_licence($app->database_system, $l_licence_id);

                    if ($l_tenant_id === 0 && $l_licence_id > 0) {
                        $app->database_system->query("DELETE FROM isys_licence WHERE isys_licence__type = " . C__LICENCE_TYPE__HOSTING_SINGLE);
                    } else {
                        $app->database_system->query("DELETE FROM isys_licence WHERE isys_licence__id = " . (int)$l_licence_id . ";");
                    }
                }
            }
        }

        break;
    case "add":

        $mandatorDatabase = null;
        switch ($_POST["licence_type"]) {
            case "subscription":
                $_POST["licence_type"] = C__LICENCE_TYPE__SINGLE;
                $l_tenant = $_POST["mandator"];
                $mandatorDatabase = connect_mandator($l_tenant);
                break;

            case "hosting":
                $_POST["licence_type"] = C__LICENCE_TYPE__HOSTING;
                $l_tenant = -1;
                break;

            case "buyers-hosting":
                $_POST["licence_type"] = C__LICENCE_TYPE__BUYERS_LICENCE_HOSTING;
                $l_tenant = -1;
                break;

            case "buyers":
                $_POST["licence_type"] = C__LICENCE_TYPE__BUYERS_LICENCE;
                $l_tenant = $_POST["mandator"];
                $mandatorDatabase = connect_mandator($l_tenant);
                break;
        }

        isys_module_system::handle_licence_installation($l_tenant, $mandatorDatabase);

        $l_frontend_error = $l_template->get_template_vars('error');

        // Only redirect, if there is no error message!
        if (empty($l_frontend_error)) {
            header('Location: ?req=licences');
        }

        break;
    case "save":

        try {
            $l_hosting_licences = $l_licences->get_installed_licences($app->database_system, null, null, "AND ISNULL(isys_licence__isys_mandator__id)");

            if (is_array($l_hosting_licences)) {
                $l_hosting_count = [];
                foreach ($l_hosting_licences as $l_lic) {
                    $l_d = unserialize($l_lic["licence_data"]);
                    $l_hosting_count[$l_lic['id']] += $l_d[C__LICENCE__OBJECT_COUNT];
                }

                if (is_array($_POST["object_count"])) {
                    $l_count_check = [];

                    foreach ($_POST["object_count"] as $l_licence_id => $l_object_count) {
                        list($licenceId, $parentLicenceKey) = explode('_', $l_licence_id);
                        if (isset($l_hosting_count[$parentLicenceKey])) {
                            if (is_numeric($l_object_count)) {
                                $l_count_check[$parentLicenceKey] += $l_object_count;
                            }
                        }
                    }

                    foreach ($l_hosting_count as $licenceKey => $maxObjects) {
                        /* Calculate max count*/
                        if ($maxObjects < $l_count_check[$licenceKey] && $maxObjects !== 0) {
                            throw new Exception("Error! Combined object count of " . $l_count_check[$licenceKey] . " is higher than the maximum allowed count of " .
                                $l_hosting_count[$licenceKey] . ". " . "Licences are not saved!");
                        }
                    }

                    /* Set new object counts */
                    foreach ($_POST["object_count"] as $l_licence_id => $l_object_count) {
                        if ($l_object_count > 0) {
                            $l_lic = $l_licences->get_licence($app->database_system, $l_licence_id);
                            $l_lic_row = $l_lic->get_row();
                            $l_lic_serialized_data = $l_lic_row["isys_licence__data"];

                            $l_lic_data = unserialize($l_lic_serialized_data);

                            if ($l_lic_data === null) {
                                $l_lic_data = unserialize(isys_glob_replace_accent($l_lic_serialized_data));
                            }

                            unset($l_lic_data[C__LICENCE__KEY]);

                            if (!is_numeric($l_object_count)) {
                                throw new Exception("Object count not numeric.");
                            }

                            /* Round object count. If user types in a float value */
                            $l_object_count = round($l_object_count);

                            $l_lic_data[C__LICENCE__TYPE] = C__LICENCE_TYPE__HOSTING_SINGLE;
                            $l_lic_data[C__LICENCE__OBJECT_COUNT] = $l_object_count;
                            $l_lic_data[C__LICENCE__KEY] = sha1(serialize($l_lic_data));

                            $l_licences->update_licence($app->database_system, $l_licence_id, $l_lic_data);
                        } else {
                            throw new Exception("Object count must be higher than 0.");
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $l_template->assign("error", $e->getMessage());
        }

        break;
    case "attach":

        try {
            $l_lic = $l_licences->get_licence($app->database_system, $_GET["licence_id"]);
            $l_hosting_licences = $l_licences->get_installed_licences($app->database_system, null, C__LICENCE_TYPE__HOSTING_SINGLE);

            $l_lic_row = $l_lic->get_row();
            // @todo  Check if "utf8_decode" is necessary!
            $l_lic_serialized_data = utf8_decode($l_lic_row["isys_licence__data"]);
            $l_lic_data = unserialize($l_lic_serialized_data);
            if (!$l_lic_data) {
                $l_lic_data = unserialize($l_lic_row["isys_licence__data"]);
            }

            /* Multi client limitation; fixed to 50 if not available in licence: */
            if (isset($l_lic_data[C__LICENCE__MAX_CLIENTS]) && $l_lic_data[C__LICENCE__MAX_CLIENTS] > 0) {
                $l_multi_client_max = $l_lic_data[C__LICENCE__MAX_CLIENTS];
            } else {
                $l_multi_client_max = 50;
            }

            header('Content-Type: application/json');
            if ((is_array($l_hosting_licences) || $l_hosting_licences instanceof Countable) && count($l_hosting_licences) <= $l_multi_client_max) {
                if (is_numeric($_POST["mandator"])) {
                    $mandatorDatabase = connect_mandator($_POST["mandator"]);

                    $l_licence_type = $l_lic_data[C__LICENCE__TYPE] ==
                    C__LICENCE_TYPE__BUYERS_LICENCE_HOSTING ? C__LICENCE_TYPE__BUYERS_LICENCE : C__LICENCE_TYPE__HOSTING_SINGLE;

                    $l_licence = [
                        C__LICENCE__OBJECT_COUNT  => 1,
                        C__LICENCE__DB_NAME       => $mandatorDatabase->get_db_name(),
                        C__LICENCE__CUSTOMER_NAME => $l_lic_data[C__LICENCE__CUSTOMER_NAME],
                        C__LICENCE__REG_DATE      => $l_lic_data[C__LICENCE__REG_DATE],
                        C__LICENCE__RUNTIME       => $l_lic_data[C__LICENCE__RUNTIME],
                        C__LICENCE__EMAIL         => $l_lic_data[C__LICENCE__EMAIL],
                        C__LICENCE__DATA          => $l_lic_data[C__LICENCE__DATA],
                        C__LICENCE__TYPE          => $l_licence_type
                    ];

                    // @todo  Check if "utf8_encode" is necessary!
                    $l_licence[C__LICENCE__KEY] = sha1(utf8_encode(serialize($l_licence)));

                    $l_licences->install($app->database_system, $l_licence, $_POST["mandator"], $l_lic_row["isys_licence__id"]);
                }
            } else {
                throw new Exception(sprintf('You are only allowed to licence at least %s clients with this licence', $l_multi_client_max));
            }
        } catch (Exception $e) {
            die(json_encode([
                'success' => false,
                'error'   => $e->getMessage()
            ]));
        }
        echo json_encode(['success' => true]);
        die;
}

try {
    $l_free_objects = 0;
    $inUse = [];
    $distributed = [];

    if ($app->database_system) {
        // Single licences.
        if (($l_licences_single = $l_licences->get_installed_licences(
            $app->database_system,
            null,
            null,
            "AND (isys_licence__type = " . C__LICENCE_TYPE__HOSTING_SINGLE . " " . "OR isys_licence__type = " . C__LICENCE_TYPE__SINGLE . " " . "OR isys_licence__type = " .
            C__LICENCE_TYPE__BUYERS_LICENCE . ") " . "AND !ISNULL(isys_licence__isys_mandator__id)"
        ))) {
        }

        // Multi-tenant licences.
        if (($l_licences_hosting = $l_licences->get_installed_licences($app->database_system, null, null, "AND ISNULL(isys_licence__isys_mandator__id)"))) {
        }
    }

    if (is_array($l_licences_single)) {
        foreach ($l_licences_single as $l_tmp) {
            $l_hosting_mandators_exclude[$l_tmp["mandator"]] = true;

            if ($l_tmp['parent_licence'] > 0) {
                $inUse[$l_tmp['parent_licence']] += $l_tmp["in_use"];
                $distributed[$l_tmp['parent_licence']] += $l_tmp["objcount"];
            }
        }
    }

    $l_tenants = $l_dao_mandator->get_mandator(null, 1);
    while ($l_dbdata = $l_tenants->get_row()) {
        $l_arMandators[$l_dbdata["isys_mandator__id"]] = $l_dbdata["isys_mandator__title"] . " (" . $l_dbdata["isys_mandator__db_name"] . ")";

        if (!isset($l_hosting_mandators_exclude[$l_dbdata["isys_mandator__id"]])) {
            $l_hosting_mandators[$l_dbdata["isys_mandator__id"]] = $l_dbdata["isys_mandator__title"] . " (" . $l_dbdata["isys_mandator__db_name"] . ")";
        }
    }

    if (isset($l_hosting_mandators)) {
        $l_template->assign("hosting_mandators", $l_hosting_mandators);
    }
    if (isset($l_arMandators)) {
        $l_template->assign("mandators", $l_arMandators);
    }
} catch (isys_exception_database $e) {
    $l_template->assign("error", $e->getMessage());
}

$l_template->assign("licences_single", $l_licences_single);
$l_template->assign("licences_hosting", $l_licences_hosting);
$l_template->assign("objectsInUse", $inUse);
$l_template->assign("objectsDistributed", $distributed);
