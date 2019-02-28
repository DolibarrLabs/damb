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
 * @link        https://gitlab.com/AXeL-dev/damb
 *
 */

/**
 * Get module version from dolistore url
 * 
 * @param     $module_url     url of the module on dolistore
 * @return    string|NULL     module version as 'x.x.x' or NULL
 */
if (! function_exists('get_module_version'))
{
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

        return isset($module_version[1]) ? $module_version[1] : NULL;
    }
}
