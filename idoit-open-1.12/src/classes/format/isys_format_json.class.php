<?php

/**
 * JSON Data Interface
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   Copyright 2010 - synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-7
 */
class isys_format_json
{
    /**
     * Wrapper method for json_decode, takes care of magic quotes and strip slashes.
     *
     * @param   string  $p_str
     * @param   boolean $p_as_assoc
     *
     * @return  mixed  If second parameter is set to FALSE the method will return a stdClass.
     *
     * @throws  \idoit\Exception\JsonException
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function decode($p_str, $p_as_assoc = true)
    {
        try {
            if (is_scalar($p_str) && $p_str) {
                $l_result = json_decode($p_str, $p_as_assoc);

                if (($l_err = self::last_error())) {
                    throw new \idoit\Exception\JsonException($l_err);
                } else {
                    return $l_result;
                }
            }

            return $p_str;
        } catch (ErrorException $e) {
            return null;
        }

    }

    /**
     * Wrapper method for json_encode.
     *
     * @param   mixed $p_val
     *
     * @return  string
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function encode($p_val)
    {
        return json_encode($p_val);
    }

    /**
     * Method to assure the given string really IS a JSON string.
     *
     * @param   string $p_val
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function is_json($p_val)
    {
        try {
            if (is_scalar($p_val) && json_decode($p_val, false, 1024) !== null) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Method to assure the given string really IS a JSON array.
     *
     * @param   string $p_val
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function is_json_array($p_val)
    {
        if (self::is_json($p_val) && is_array(self::decode($p_val))) {
            return true;
        }

        return false;
    }

    /**
     * Returns the last error (if any) occurred by last JSON parsing.
     *
     * @return  mixed  String with error message when an error occured, boolean false if eveything is okay.
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function last_error()
    {
        if (function_exists("json_last_error")) {
            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    return 'Maximum stack depth exceeded';
                    break;

                case JSON_ERROR_CTRL_CHAR:
                    return 'Unexpected control character found';
                    break;

                case JSON_ERROR_SYNTAX:
                    return 'Syntax error, malformed JSON';
                    break;

                case JSON_ERROR_NONE:
                    return false;
                    break;
            }
        }

        return false;
    }
}