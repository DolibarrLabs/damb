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
 * Load lang files
 *
 * @param     $lang_files    String|Array of lang files to load
 */
if (! function_exists('load_langs'))
{
    function load_langs($lang_files = array())
    {
        global $langs;

        if (is_array($lang_files))
        {
            foreach ($lang_files as $file) {
                $langs->load($file);
            }
        }
        else if (! empty($lang_files))
        {
            $langs->load($lang_files);
        }
    }
}

/**
 * Print page header
 *
 * @param     $title         Page title
 * @param     $lang_files    String|Array of lang files to load
 * @param     $css_files     Array of css files to include in page head
 * @param     $js_files      Array of js files to include in page head
 * @param     $head          Additional lines to include in page head
 */
if (! function_exists('print_header'))
{
    function print_header($title, $lang_files = array(), $css_files = array(), $js_files = array(), $head = '')
    {
        global $langs;

        // Start measuring time after print_header call
        if (function_exists('start_time_measure')) {
            start_time_measure('after_print_header_call', __FUNCTION__);
        }

        // Load translations
        load_langs($lang_files);

        // Load Page Header (Dolibarr header, menus, ...)
        llxHeader($head, $langs->trans($title), '', '', 0, 0, $js_files, $css_files);
    }
}

/**
 * Print page footer.
 * Note: this function should be called after print_header() call.
 *
 * @param     $add_fiche_end     Call dol_fiche_end() before printing footer
 */
if (! function_exists('print_footer'))
{
    function print_footer($add_fiche_end = false)
    {
        global $db;

        // Stop measuring time after print_header call & Start measuring time after print_footer ('after_print_footer_call' will be stopped when rendering the Debug bar)
        if (function_exists('start_time_measure')) {
            start_time_measure('after_print_footer_call', __FUNCTION__, 'after_print_header_call');
        }

        // Page footer
        if ($add_fiche_end) {
            dol_fiche_end();
        }
        llxFooter();
        $db->close();
    }
}

/**
 * Redirect to specific url
 *
 * @param     $url     Url
 */
if (! function_exists('redirect'))
{
    function redirect($url)
    {
        global $debugbar;

        if (is_object($debugbar)) {
            $debugbar->stackData();
        }

        header('Location: ' . $url);

        exit();
    }
}

/**
 * Control access
 *
 * @param     $access_permission     Access permission, e.: '$user->admin'
 */
if (! function_exists('control_access'))
{
    function control_access($access_permission)
    {
        if (! empty($access_permission) && ! verifCond($access_permission)) {
            accessforbidden();
        }
    }
}

/**
 * Print a subtitle
 *
 * @param    $title             subtitle title
 * @param    $picture           subtitle picture
 * @param    $morehtmlright     more HTML to show on the right
 */
if (! function_exists('print_subtitle'))
{
    function print_subtitle($title, $picture = 'title_generic.png', $morehtmlright = '')
    {
        global $langs;

        // Set $morehtmlright custom values
        if (preg_match('/^link:(.*?):label:(.*?)$/', $morehtmlright, $values)) {
            $morehtmlright = '<a href="'.$values[1].'">'.$langs->trans($values[2]).'</a>';
        }

        echo load_fiche_titre($langs->trans($title), $morehtmlright, $picture);
    }
}

/**
 * Print tabs
 *
 * @param     $tabs     tabs array as [
 * array(
 *     'title'   => 'MyTab',
 *     'url'     => 'mymodule/page.php',
 *     'active'  => true,
 *     'enabled' => '$user->admin'
 * )]
 * @param     $title             tabs main title
 * @param     $picture           tabs picture (picture file should have the prefix 'object_')
 * @param     $noheader          -1 or 0=Add tab header, 1=no tab header. If you set this to 1, using dol_fiche_end() to close tab is not required.
 * @param     $morehtmlright     more html to display on the right of tabs
 * @param     $type              used to display tabs from other modules, e.: 'mymodule'
 * @param     $object            also used to display tabs from other modules, e.: $myobject
 */
if (! function_exists('print_tabs'))
{
    function print_tabs($tabs, $title = '', $picture = '', $noheader = 0, $morehtmlright = '', $type = '', $object = null)
    {
        global $conf, $langs;

        $links = array();
        $active_link = '';

        // Set tabs links
        if (is_array($tabs))
        {
            foreach ($tabs as $tab)
            {
                if (is_array($tab))
                {
                    if (! isset($tab['enabled']) || empty($tab['enabled']) || verifCond($tab['enabled']))
                    {
                        $tab_id = count($links) + 1;
                        $tab_name = 'tab_'.$tab_id;

                        $links[] = array(
                            0 => dol_buildpath($tab['url'], 1),
                            1 => $langs->trans($tab['title']),
                            2 => $tab_name
                        );

                        if ($tab['active']) {
                            $active_link = $tab_name;
                        }
                    }
                }
            }
        }

        // Show more tabs from modules
        // Entries must be declared in modules descriptor with line
        // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
        // $this->tabs = array('entity:-tabname);                                                   to remove a tab
        if (! empty($type)) {
            complete_head_from_modules($conf, $langs, $object, $links, count($links), $type);
        }

        // Generate tabs
        dol_fiche_head($links, $active_link, $langs->trans($title), $noheader, $picture, 0, $morehtmlright);
    }
}

/**
 * Include a template into the page
 *
 * @param   $template_path      template relative path, e.: 'mymodule/tpl/template.php'
 * @param   $template_params    template parameters, e.: array('param' => 'value')
 * @param   $use_require_once   avoids including the template many times on the same page
 */
if (! function_exists('load_template'))
{
    function load_template($template_path, $template_params = array(), $use_require_once = false)
    {
        // Stop measuring time after print_header call & Start measuring time after load_template call
        if (function_exists('start_time_measure')) {
            start_time_measure('after_load_template_call', __FUNCTION__, 'after_print_header_call');
        }

        // Load template
        $path = dol_buildpath($template_path);

        foreach ($template_params as $param => $value) {
            ${$param} = $value;
        }

        if ($use_require_once) {
            require_once $path;
        } else {
            require $path;
        }

        // Stop measuring time after load_template call
        if (function_exists('stop_time_measure')) {
            stop_time_measure('after_load_template_call');
        }
    }
}

/**
 * Add a success message to session.
 * Note: message rendering will be done by print_footer() or more exactly llxFooter() function
 *
 * @param     $message       Message
 * @param     $parameters    Message parameter or an array of message parameters (max 4)
 * @param     $translate     Translate the message or not
 */
if (! function_exists('success_message'))
{
    function success_message($message, $parameters = array(), $translate = true)
    {
        global $langs;

        if ($translate)
        {
            if (is_array($parameters)) {
                $param1 = isset($parameters[0]) ? $parameters[0] : '';
                $param2 = isset($parameters[1]) ? $parameters[1] : '';
                $param3 = isset($parameters[2]) ? $parameters[2] : '';
                $param4 = isset($parameters[3]) ? $parameters[3] : '';
            }
            else {
                $param1 = $parameters;
                $param2 = $param3 = $param4 = '';
            }
            $message = $langs->trans($message, $param1, $param2, $param3, $param4);
        }

        setEventMessage($message, 'mesgs');
    }
}

/**
 * Add a error message to session.
 * Note: message rendering will be done by print_footer() or more exactly llxFooter() function
 *
 * @param     $message       Message
 * @param     $parameters    Message parameter or an array of message parameters (max 4)
 * @param     $translate     Translate the message or not
 */
if (! function_exists('error_message'))
{
    function error_message($message, $parameters = array(), $translate = true)
    {
        global $langs;

        if ($translate)
        {
            if (is_array($parameters)) {
                $param1 = isset($parameters[0]) ? $parameters[0] : '';
                $param2 = isset($parameters[1]) ? $parameters[1] : '';
                $param3 = isset($parameters[2]) ? $parameters[2] : '';
                $param4 = isset($parameters[3]) ? $parameters[3] : '';
            }
            else {
                $param1 = $parameters;
                $param2 = $param3 = $param4 = '';
            }
            $message = $langs->trans($message, $param1, $param2, $param3, $param4);
        }

        setEventMessage($message, 'errors');
    }
}

/**
 * Add a warning message to session.
 * Note: message rendering will be done by print_footer() or more exactly llxFooter() function
 *
 * @param     $message       Message
 * @param     $parameters    Message parameter or an array of message parameters (max 4)
 * @param     $translate     Translate the message or not
 */
if (! function_exists('warning_message'))
{
    function warning_message($message, $parameters = array(), $translate = true)
    {
        global $langs;

        if ($translate)
        {
            if (is_array($parameters)) {
                $param1 = isset($parameters[0]) ? $parameters[0] : '';
                $param2 = isset($parameters[1]) ? $parameters[1] : '';
                $param3 = isset($parameters[2]) ? $parameters[2] : '';
                $param4 = isset($parameters[3]) ? $parameters[3] : '';
            }
            else {
                $param1 = $parameters;
                $param2 = $param3 = $param4 = '';
            }
            $message = $langs->trans($message, $param1, $param2, $param3, $param4);
        }

        setEventMessage($message, 'warnings');
    }
}

/**
 * Translate a string & print it
 *
 * @param     $str       String to translate
 * @param     $print     Print the translated string if true or return it if false
 */
if (! function_exists('print_trans'))
{
    function print_trans($str, $print = true)
    {
        global $langs;

        if ($print) {
            echo $langs->trans($str);
        }
        else {
            return $langs->trans($str);
        }
    }
}
