<?php

/**
 * This file is a part of DAMB
 *
 * An advanced module builder for Dolibarr ERP/CRM
 *
 *
 * @package     DAMB
 * @author      AXeL
 * @copyright   Copyright (c) 2019 - 2020, AXeL-dev
 * @license     GPL
 * @link        https://github.com/AXeL-dev/damb
 *
 */

// --------------------------------------------------------------------

if (! function_exists('is_submitted'))
{
    /**
     * Check if a value have been submitted by GET or POST method
     *
     * @param   string   $value_name   value name
     * @return  boolean                true or false
     */
    function is_submitted($value_name)
    {
        return isset($_GET[$value_name]) || isset($_POST[$value_name]);
    }
}

// --------------------------------------------------------------------

if (! function_exists('GETPOSTDATE'))
{
    /**
     * Return posted date
     *
     * @param   string    $date_input_name        date input name
     * @param   boolean   $convert_to_db_format   should convert the date to database format or not
     * @return  string                            date in your db format, null if error/empty
     */
    function GETPOSTDATE($date_input_name, $convert_to_db_format = false)
    {
        if (is_submitted($date_input_name.'month') && is_submitted($date_input_name.'day') && is_submitted($date_input_name.'year')) {
            $date = dol_mktime(0, 0, 0, GETPOST($date_input_name.'month'), GETPOST($date_input_name.'day'), GETPOST($date_input_name.'year'));
        }
        else {
            $date = GETPOST($date_input_name);
        }

        if ($convert_to_db_format) {
            global $db;

            return empty($date) ? null : $db->idate($date);
        }
        else {
            return $date;
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('GETPOSTDATETIME'))
{
    /**
     * Return posted datetime
     *
     * @param   string    $datetime_input_name    datetime input name
     * @param   boolean   $convert_to_db_format   should convert the datetime to database format or not
     * @return  string                            datetime in your db format, null if error/empty
     */
    function GETPOSTDATETIME($datetime_input_name, $convert_to_db_format = false)
    {
        if (is_submitted($datetime_input_name.'hour') && is_submitted($datetime_input_name.'min') && is_submitted($datetime_input_name.'month') && is_submitted($datetime_input_name.'day') && is_submitted($datetime_input_name.'year')) {
            $date = dol_mktime(GETPOST($datetime_input_name.'hour'), GETPOST($datetime_input_name.'min'), 0, GETPOST($datetime_input_name.'month'), GETPOST($datetime_input_name.'day'), GETPOST($datetime_input_name.'year'));
        }
        else {
            $date = GETPOST($datetime_input_name);
        }

        if ($convert_to_db_format) {
            global $db;

            return empty($date) ? null : $db->idate($date);
        }
        else {
            return $date;
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('empty_to_null'))
{
    /**
     * Convert empty values to null
     *
     * @param   string|int   $value            value to convert
     * @param   boolean      $minus_one_also   consider -1 also as an empty value
     * @return  null|string                    null or initial value
     */
    function empty_to_null($value, $minus_one_also = false)
    {
        return empty($value) || ($minus_one_also && $value == -1) ? null : $value;
    }
}

// --------------------------------------------------------------------

if (! function_exists('now'))
{
    /**
     * Return current date & time
     *
     * @param   boolean   $convert_to_db_format   should convert the date to database format or not
     * @return  string                            current date in your db format
     */
    function now($convert_to_db_format = false)
    {
        global $db;

        $now = dol_now();

        return $convert_to_db_format ? $db->idate($now) : $now;
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_date'))
{
    /**
     * Output date in a string format
     *
     * @param   string   $date     date in db format or GM Timestamps date if $convert_to_tms is false
     * @param   string   $format   output date format (tag of strftime function)
     *                             "%d %b %Y",
     *                             "%d/%m/%Y %H:%M",
     *                             "%d/%m/%Y %H:%M:%S",
     *                             "%B"=Long text of month, "%A"=Long text of day, "%b"=Short text of month, "%a"=Short text of day
     *                             "day", "daytext", "dayhour", "dayhourldap", "dayhourtext", "dayrfc", "dayhourrfc", "...reduceformat"
     * @param   boolean   $convert_to_tms   convert the $date parameter to timestamp or not
     * @return  string                      formated date or '' if date is null
     */
    function print_date($date, $format, $convert_to_tms = true)
    {
        global $db;

        $time = $convert_to_tms ? $db->jdate($date) : $date;

        return dol_print_date($time, $format);
    }
}

// --------------------------------------------------------------------

if (! function_exists('str_escape'))
{
    /**
     * Escape a string from ' or " to avoid errors when dealing with database
     *
     * @param   string   $str   string to escape
     * @return  string          escaped string
     */
    function str_escape($str)
    {
        global $db;

        return $db->escape($str);
    }
}

// --------------------------------------------------------------------

if (! function_exists('price_with_currency'))
{
    /**
     * Return price with currency
     *
     * @param   int|float   $price   price
     * @return  string               price with currency
     */
    function price_with_currency($price, $currency = 'auto')
    {
        return price($price, 0, '', 1, -1, -1, $currency);
    }
}

// --------------------------------------------------------------------

if (! function_exists('get_func_output'))
{
    /**
     * Return function output as a string
     *
     * @param   string   $func   function name
     * @param   array    $args   function arguments
     */
    function get_func_output($func, $args = array())
    {
        ob_start();
        call_user_func_array($func, $args);
        $out = ob_get_contents();
        ob_end_clean();

        return $out;
    }
}

// --------------------------------------------------------------------

if (! function_exists('object_to_array'))
{
    /**
     * Dumps all the object propreties and its associations recursively into an array
     *
     * @see     http://php.net/manual/fr/function.get-object-vars.php#62470
     * @param   Object   $obj   Object
     * @return  array           Object as an array
     */
    function object_to_array($obj)
    {
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        $arr = array();

        foreach ($_arr as $key => $val) {
            $val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
            $arr[$key] = $val;
        }

        return $arr;
    }
}

// --------------------------------------------------------------------

if (! function_exists('array_to_table'))
{
    /**
     * Create an HTML table from a two-dimensional array
     *
     * @param   array     $array          array
     * @param   boolean   $show_header    show table header or not
     * @return  string                    HTML table
     */
    function array_to_table($array, $show_header = true)
    {
        $out = '<table class="liste">';
        $count = 0;

        foreach ($array as $row)
        {
            if ($count == 0 && $show_header)
            {
                $out.= '<tr class="liste_titre">';
                foreach ((array)$row as $key => $value) {
                    $out.= '<th><strong>'.$key.'</strong></th>';
                }
                $out.= '</tr>';
            }

            $out.= '<tr>';
            foreach ((array)$row as $value) {
                $out.= '<td>'.$value.'</td>';
            }
            $out.= '</tr>';

            $count++;
        }

        $out.= '</table>';

        return $out;
    }
}

// --------------------------------------------------------------------

if (! function_exists('array_to_string'))
{
    /**
     * Converts an array values to string separated by a delimiter
     *
     * @param   array    $array       Array
     * @param   string   $delimiter   Values delimiter
     * @return  string                array values string separated by the delimiter or empty string if array is empty
     */
    function array_to_string($array, $delimiter = ',')
    {
        return (is_array($array) && ! empty($array) ? join($delimiter, $array) : '');
    }
}

// --------------------------------------------------------------------

if (! function_exists('string_to_array'))
{
    /**
     * Converts a string into an array using a delimiter to separate/get the values
     *
     * @param   string   $str         String
     * @param   string   $delimiter   Values delimiter
     * @return  array                 array filled with values from string as ['value' => 'value'] or empty array if string is empty
     */
    function string_to_array($str, $delimiter = ',')
    {
        $arr = array();

        if (! empty($str))
        {
            foreach (explode($delimiter, $str) as $value) {
                $trimed_value = trim($value);
                $arr[$trimed_value] = $trimed_value;
            }
        }

        return $arr;
    }
}

// --------------------------------------------------------------------

if (! function_exists('js_enabled'))
{
    /**
     * Returns if javascript/jquery is enabled
     *
     * @return   boolean   true if javascript is enabled, else false
     */
    function js_enabled()
    {
        global $conf;

        return (! empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile));
    }
}
