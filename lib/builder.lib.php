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

// --------------------------------------------------------------------

if (! function_exists('chmod_r'))
{
    /**
     * Changes permissions on files and directories within $dir and dives recursively into found subdirectories.
     *
     * @see https://stackoverflow.com/questions/9262622/set-permissions-for-all-files-and-folders-recursively
     *
     * @param  string   $dir               directory path
     * @param  int      $dirPermissions    directory permissions
     * @param  int      $filePermissions   file permissions
     */
    function chmod_r($dir, $dirPermissions, $filePermissions)
    {
        $dp = opendir($dir);
        while($file = readdir($dp)) {
            if (($file == ".") || ($file == ".."))
                continue;

            $fullPath = $dir."/".$file;

            if(is_dir($fullPath)) {
                @chmod($fullPath, $dirPermissions);
                @chmod_r($fullPath, $dirPermissions, $filePermissions);
            } else {
                @chmod($fullPath, $filePermissions);
            }
        }
        closedir($dp);
    }
}

// --------------------------------------------------------------------

if (! function_exists('mkdir_r'))
{
    /**
     * Create folder(s) recursively.
     *
     * @see https://stackoverflow.com/questions/3997641/why-cant-php-create-a-directory-with-777-permissions
     *
     * @param  array    $folders       array of folders to create
     * @param  in       $perm_code     folders permissions
     * @param  string   $path_prefix   path prefix for all folders
     * @return boolean                 true if success, false if error
     */
    function mkdir_r($folders, $perm_code = 0777, $path_prefix = '')
    {
        if (! empty($path_prefix) && substr($path_prefix, -1) != '/') {
            $path_prefix .= '/';
        }

        $old = umask(0);
        foreach ($folders as $folder) {
            if (! @mkdir($path_prefix.$folder, $perm_code, true)) {
                return false;
            }
        }
        umask($old);

        return true;
    }
}

// --------------------------------------------------------------------

if (! function_exists('get_template'))
{
    /**
     * Return file template as a string
     *
     * @see https://stackoverflow.com/questions/26962791/load-file-as-string-which-contains-variable-definitions
     *
     * @param  string   $file    template file
     * @param  array    $hooks   template hooks
     * @return string            template as a string
     */
    function get_template($file, $hooks = array())
    {
        // Read our template in as a string.
        $template = file_get_contents($file);

        if (is_array($hooks) && ! empty($hooks))
        {
            $keys = array();
            $data = array();
            foreach($hooks as $key => $value) {
                array_push($keys, '${'. $key .'}');
                array_push($data, $value);
            }

            // Replace all of the variables with the variable values.
            $template = str_replace($keys, $data, $template);
        }

        return $template;
    }
}

// --------------------------------------------------------------------

if (! function_exists('sanitize_string'))
{
    /**
     * Sanitize specified string
     *
     * @param  string    $str              string to sanitize
     * @param  boolean   $no_underscores   do not allow underscores also
     * @return string                      sanitized string
     */
    function sanitize_string($str, $no_underscores = false)
    {
        $sanitized_str = str_replace(' ', '', $str);

        if ($no_underscores) {
            return str_replace('_', '', $sanitized_str);
        }

        return $sanitized_str;
    }
}
