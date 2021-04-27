<?php

/**
 * Interface isys_module_authable
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/**
 * Interface isys_module_auth
 *
 * @todo Refactor to new structure like "idoit\AddOn\InstallableInterface" (+ this code still needs to work for compatibility).
 * @author Selcuk Kekec <skekec@i-doit.com>
 */
interface isys_module_authable
{
    /**
     * Get related auth class for module
     *
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return isys_auth|false
     */
    public static function get_auth();
}
