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

if (! function_exists('load_langs'))
{
    /**
     * Load lang files
     *
     * @param   string|array   $lang_files   String|Array of lang files to load
     */
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

// --------------------------------------------------------------------

if (! function_exists('print_header'))
{
    /**
     * Print page header
     *
     * @param   string         $title        Page title
     * @param   string|array   $lang_files   String|Array of lang files to load
     * @param   array          $css_files    Array of css files to include in page head
     * @param   array          $js_files     Array of js files to include in page head
     * @param   string         $head         Additional lines to include in page head
     */
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

// --------------------------------------------------------------------

if (! function_exists('print_footer'))
{
    /**
     * Print page footer.
     * Note: this function should be called after print_header() call.
     *
     * @param   boolean   $add_fiche_end   Call dol_fiche_end() before printing footer
     */
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

// --------------------------------------------------------------------

if (! function_exists('redirect'))
{
    /**
     * Redirect to specific url
     *
     * @param   string   $url   Url
     */
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

// --------------------------------------------------------------------

if (! function_exists('control_access'))
{
    /**
     * Control access
     *
     * @param   string   $access_permission   Access permission, e.: '$user->admin'
     */
    function control_access($access_permission)
    {
        if (! empty($access_permission) && ! verifCond($access_permission)) {
            accessforbidden();
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_subtitle'))
{
    /**
     * Print a subtitle
     *
     * @param   string   $title            subtitle title
     * @param   string   $picture          subtitle picture
     * @param   string   $morehtmlright    more HTML to show on the right
     */
    function print_subtitle($title, $picture = 'title_generic.png', $morehtmlright = '')
    {
        global $langs;

        // Handle custom $morehtmlright
        if ($morehtmlright == 'link:modules_list') {
            $morehtmlright = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?mainmenu=home">'.$langs->trans('BackToModuleList').'</a>';
        }
        else if (preg_match('/^link:(.*?):label:(.*?)$/', $morehtmlright, $values)) {
            $morehtmlright = '<a href="'.$values[1].'">'.$langs->trans($values[2]).'</a>';
        }

        echo load_fiche_titre($langs->trans($title), $morehtmlright, $picture);
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_tabs'))
{
    /**
     * Print tabs
     *
     * @param   array   $tabs   tabs array as [
     * array(
     *     'title'   => 'MyTab',
     *     'url'     => 'mymodule/page.php',
     *     'active'  => true,
     *     'enabled' => '$user->admin'
     * )]
     * @param   string   $title           tabs main title
     * @param   string   $picture         tabs picture (picture file should have the prefix 'object_')
     * @param   int      $noheader        -1 or 0=Add tab header, 1=no tab header. If you set this to 1, using dol_fiche_end() to close tab is not required.
     * @param   string   $morehtmlright   more html to display on the right of tabs
     * @param   string   $type            used to display tabs from other modules, e.: 'mymodule'
     * @param   Object   $object          also used to display tabs from other modules, e.: $myobject
     */
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

// --------------------------------------------------------------------

if (! function_exists('load_template'))
{
    /**
     * Include a template into the page
     *
     * @param   string    $template_path      template relative path, e.: 'mymodule/tpl/template.php'
     * @param   array     $template_params    template parameters, e.: array('param' => 'value')
     * @param   boolean   $use_require_once   avoids including the template many times on the same page
     */
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

// --------------------------------------------------------------------

if (! function_exists('success_message'))
{
    /**
     * Add a success message to session.
     * Note: message rendering will be done by print_footer() or more exactly llxFooter() function
     *
     * @param   string    $message      Message
     * @param   array     $parameters   Message parameter or an array of message parameters (max 4)
     * @param   boolean   $translate    Translate the message or not
     */
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

// --------------------------------------------------------------------

if (! function_exists('error_message'))
{
    /**
     * Add a error message to session.
     * Note: message rendering will be done by print_footer() or more exactly llxFooter() function
     *
     * @param   string    $message      Message
     * @param   array     $parameters   Message parameter or an array of message parameters (max 4)
     * @param   boolean   $translate    Translate the message or not
     */
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

// --------------------------------------------------------------------

if (! function_exists('warning_message'))
{
    /**
     * Add a warning message to session.
     * Note: message rendering will be done by print_footer() or more exactly llxFooter() function
     *
     * @param   string    $message      Message
     * @param   array     $parameters   Message parameter or an array of message parameters (max 4)
     * @param   boolean   $translate    Translate the message or not
     */
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

// --------------------------------------------------------------------

if (! function_exists('print_trans'))
{
    /**
     * Translate a string & print it
     *
     * @param   string    $str     String to translate
     * @param   boolean   $print   Print the translated string if true or return it if false
     */
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

// --------------------------------------------------------------------

if (! function_exists('print_alert'))
{
    /**
     * Print an alert message into the page
     *
     * @param   string    $message    Alert message
     * @param   string    $class      Alert div class
     */
    function print_alert($message, $class = 'error')
    {
        global $langs;


        echo '<div class="'.$class.'">';
        echo $langs->trans($message);
        echo '</div>';
    }
}
