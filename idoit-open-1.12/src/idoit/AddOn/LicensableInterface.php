<?php
/**
 * i-doit Module interface for licenses
 *
 * @package     idoit\Component
 * @author      atsapko
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\AddOn;

interface LicensableInterface
{
    /**
     * Checks if a module is licenced
     *
     * @return  boolean
     */
    public static function isLicensed();

    /**
     * Set licence status.
     *
     * @param  boolean $isLicensed
     */
    public static function setLicensed($isLicensed);
}