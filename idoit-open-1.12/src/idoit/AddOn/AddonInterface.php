<?php
/**
 * i-doit Basic modules interface
 *
 * @package     idoit\Component
 * @author      atsapko
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\AddOn;

interface AddonInterface
{
    /**
     * Signal Slot initialization.
     *
     * @return $this
     */
    public function initSlots();

    /**
     * Default start method.
     *
     * @return $this
     */
    public function start();
}