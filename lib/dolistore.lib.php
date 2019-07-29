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

if (! function_exists('get_module_version'))
{
    /**
     * Get module version from dolistore url
     * 
     * @param   string   $module_url   url of the module on dolistore
     * @return  string|null            module version as 'x.x.x' or null
     */
    function get_module_version($module_url)
    {
        if (! empty($module_url) && $module_url != '#')
        {
            $connected = @fsockopen("www.dolistore.com", 80);

            if ($connected)
            {
                // Close socket
                fclose($connected);

                // Get module page content
                $page = @file_get_contents($module_url);

                // Extract module version
                preg_match("/var module_version = '(.*)'/", $page, $module_version);
            }
        }

        return isset($module_version[1]) ? $module_version[1] : null;
    }
}

// --------------------------------------------------------------------

/**
 * Compare two module versions
 *
 * @param     $version        Version string, possible values: 'x', 'x.x', 'x.x.x'
 * @param     $sign           Compare sign, possible values: '>', '>=', '<', '<='
 * @param     $version_to     Version to compare with
 * @return    boolean         true or false
 */
if (! function_exists('compare_module_version'))
{
    function compare_module_version($version, $sign, $version_to)
    {
        $version_digits = explode('.', $version);
        $version_to_digits = explode('.', $version_to);

        if (! in_array($sign, array('>', '>=', '<', '<=')))
        {
            die('Wrong sign='.$sign.' provided to '.__FUNCTION__);
        }

        // 1st - try using built-in dolibarr function
        else if (function_exists('versioncompare'))
        {
            $result = versioncompare($version_digits, $version_to_digits);

            if ($sign == '>') {
                return ($result > 0);
            }
            else if ($sign == '>=') {
                return ($result >= 0);
            }
            else if ($sign == '<') {
                return ($result < 0);
            }
            else if ($sign == '<=') {
                return ($result <= 0);
            }
        }

        // 2nd - try using our own implementation
        else if ($sign == '>' || $sign == '>=')
        {
            $greater_than = $version_digits[0] > $version_to_digits[0] || 
            (isset($version_to_digits[1]) && $version_digits[0] == $version_to_digits[0] && $version_digits[1] > $version_to_digits[1]) || 
            (isset($version_to_digits[2]) && $version_digits[0] == $version_to_digits[0] && $version_digits[1] == $version_to_digits[1] && $version_digits[2] > $version_to_digits[2]) ? true : false;

            return ($sign == '>=' ? (($version == $version_to) || $greater_than) : $greater_than);
        }
        else if ($sign == '<' || $sign == '<=')
        {
            $lesser_than = $version_digits[0] < $version_to_digits[0] || 
            (isset($version_to_digits[1]) && $version_digits[0] == $version_to_digits[0] && $version_digits[1] < $version_to_digits[1]) || 
            (isset($version_to_digits[2]) && $version_digits[0] == $version_to_digits[0] && $version_digits[1] == $version_to_digits[1] && $version_digits[2] < $version_to_digits[2]) ? true : false;

            return ($sign == '<=' ? (($version == $version_to) || $lesser_than) : $lesser_than);
        }
    }
}
