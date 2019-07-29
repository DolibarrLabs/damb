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

if (! function_exists('add_constant'))
{
    /**
     * Add a constant to a module object instance
     *
     * @param   DolibarrModules   $module   module object instance
     * @param   string            $name     constant name
     * @param   string            $value    constant value
     * @param   string            $desc     constant description / note
     * @param   string            $type     constant type
     */
    function add_constant(&$module, $name, $value, $desc = '', $type = 'chaine')
    {
        $module->const[] = array(
            0 => $name,
            1 => $type,
            2 => $value,
            3 => $desc,
            4 => 1, // visiblity
            5 => 'current', // entity 'current' or 'allentities'
            6 => 0 // delete constant when module is disabled
        );
    }
}

// --------------------------------------------------------------------

if (! function_exists('add_widget'))
{
    /**
     * Add a widget to a module object instance
     *
     * @param   DolibarrModules   $module                  module object instance
     * @param   string            $widget_file             widget file
     * @param   string            $note                    widget note
     * @param   string            $enabled_by_default_on   where to enable the widget by default
     */
    function add_widget(&$module, $widget_file, $note = '', $enabled_by_default_on = 'Home')
    {
        $module->boxes[] = array(
            'file' => $widget_file,
            'note' => $note,
            'enabledbydefaulton' => $enabled_by_default_on
        );
    }
}

// --------------------------------------------------------------------

if (! function_exists('add_permission'))
{
    /**
     * Add a permission to a module object instance
     *
     * @param   DolibarrModules   $module               module object instance
     * @param   string            $name                 permission name
     * @param   string            $desc                 permission description
     * @param   string            $type                 permission type: 'r', 'c', 'm', 'd'
     * @param   int               $enabled_by_default   enable the permission by default for all users
     */
    function add_permission(&$module, $name, $desc = '', $type = '', $enabled_by_default = 1)
    {
        $id = (int)$module->number + (is_array($module->rights) ? count($module->rights) : 0) + 1;

        $module->rights[] = array(
            0 => $id,
            1 => $desc,
            2 => $type,
            3 => $enabled_by_default,
            4 => $name
        );
    }
}

// --------------------------------------------------------------------

if (! function_exists('add_subpermission'))
{
    /**
     * Add a subpermission to a module object instance
     *
     * @param   DolibarrModules   $module               module object instance
     * @param   string            $perm_name            permission name
     * @param   string            $subperm_name         subpermission name
     * @param   string            $desc                 permission description
     * @param   string            $type                 permission type: 'r', 'c', 'm', 'd'
     * @param   int               $enabled_by_default   enable the permission by default for all users
     */
    function add_subpermission(&$module, $perm_name, $subperm_name, $desc = '', $type = '', $enabled_by_default = 1)
    {
        $id = (int)$module->number + (is_array($module->rights) ? count($module->rights) : 0) + 1;

        $module->rights[] = array(
            0 => $id,
            1 => $desc,
            2 => $type,
            3 => $enabled_by_default,
            4 => $perm_name,
            5 => $subperm_name
        );
    }
}

// --------------------------------------------------------------------

if (! function_exists('add_menu'))
{
    /**
     * Add a menu to a module object instance
     *
     * @param   DolibarrModules   $module       module object instance
     * @param   string            $type         menu type 'top' or 'left'
     * @param   string            $fk_menu      where to insert menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode of parent menu
     * @param   string            $main_menu    main/top menu name
     * @param   string            $left_menu    left menu name
     * @param   string            $title        menu title
     * @param   string            $url          target page url
     * @param   int               $position     menu position
     * @param   string            $enabled      define condition to show or hide menu entry. Use '$conf->monmodule->enabled' if entry must be visible if module is enabled.
     * @param   string            $perms        use 'perms'=>'$user->rights->monmodule->level1->level2' if you want your menu with a permission rules
     * @param   string            $target       menu target, leave empty or use '_blank' to open in a new window / tab
     */
    function add_menu(&$module, $type, $fk_menu, $main_menu, $left_menu, $title, $url, $position, $enabled = '1', $perms = '1', $target = '')
    {
        $module->menu[] = array(
            'fk_menu'  => $fk_menu,
            'type'     => $type,
            'titre'    => $title,
            'mainmenu' => $main_menu,
            'leftmenu' => $left_menu,
            'url'      => $url,
            'langs'    => $module->langfiles[0],
            'position' => $position,
            'enabled'  => $enabled,
            'perms'    => $perms,
            'target'   => $target,
            'user'     => 2 // 0=Menu for internal users, 1=external users, 2=both
        );
    }
}

// --------------------------------------------------------------------

if (! function_exists('add_top_menu'))
{
    /**
     * Add a top menu entry to a module object instance
     *
     * @param   DolibarrModules   $module    module object instance
     * @param   string            $name      menu name (should be the same as the module folder name, & the same as the menu picture file *.png)
     * @param   string            $title     menu title
     * @param   string            $url       target page url
     * @param   string            $perms     should anyone see & use the menu or use conditions like '$user->rights->monmodule->level1->level2'
     * @param   string            $enabled   should the menu be always enabled or use conditions like '$conf->monmodule->enabled'
     * @param   string            $target    menu target, leave empty or use '_blank' to open in a new window / tab
     * @param   int               $position  menu position
     */
    function add_top_menu(&$module, $name, $title, $url, $perms = '1', $enabled = '1', $target = '', $position = 100)
    {
        add_menu($module, 'top', 0, $name, '', $title, $url, $position, $enabled, $perms, $target);
    }
}

// --------------------------------------------------------------------

if (! function_exists('add_left_menu'))
{
    /**
     * Add a left menu entry to a module object instance
     *
     * @param   DolibarrModules   $module       module object instance
     * @param   string            $main_menu    main/top menu name where to insert
     * @param   string            $name         menu name (codename for further use)
     * @param   string            $title        menu title
     * @param   string            $url          target page url
     * @param   string            $perms        should anyone see & use the menu or use conditions like '$user->rights->monmodule->level1->level2'
     * @param   string            $enabled      should the menu be always enabled or use conditions like '$conf->monmodule->enabled'
     * @param   string            $target       menu target, leave empty or use '_blank' to open in a new window / tab
     * @param   int               $position     menu position
     */
    function add_left_menu(&$module, $main_menu, $name, $title, $url, $perms = '1', $enabled = '1', $target = '', $position = 100)
    {
        add_menu($module, 'left', 'fk_mainmenu='.$main_menu, $main_menu, $name, $title, $url, $position, $enabled, $perms, $target);
    }
}

// --------------------------------------------------------------------

if (! function_exists('add_left_submenu'))
{
    /**
     * Add a left submenu entry to a module object instance
     *
     * @param   DolibarrModules   $module       module object instance
     * @param   string            $main_menu    main/top menu name where to insert
     * @param   string            $left_menu    left menu name where to insert
     * @param   string            $name         menu name (codename for further use)
     * @param   string            $title        menu title
     * @param   string            $url          target page url
     * @param   string            $perms        should anyone see & use the menu or use conditions like '$user->rights->monmodule->level1->level2'
     * @param   string            $enabled      should the menu be always enabled or use conditions like '$conf->monmodule->enabled'
     * @param   string            $target       menu target, leave empty or use '_blank' to open in a new window / tab
     * @param   int               $position     menu position
     */
    function add_left_submenu(&$module, $main_menu, $left_menu, $name, $title, $url, $perms = '1', $enabled = '1', $target = '', $position = 100)
    {
        add_menu($module, 'left', 'fk_mainmenu='.$main_menu.',fk_leftmenu='.$left_menu, $main_menu, $name, $title, $url, $position, $enabled, $perms, $target);
    }
}

// --------------------------------------------------------------------

if (! function_exists('add_dictionary'))
{
    /**
     * Add a dictionary to a module object instance
     *
     * @param   DolibarrModules   $module                 module object instance
     * @param   string            $table_name             table name without prefix
     * @param   string            $table_label            table label
     * @param   string            $select_fields          select statement fields, e.: 'rowid, code, label, active'
     * @param   string            $table_sort             sort field & order, e.: 'label ASC'
     * @param   string            $fields_to_show         fields to show on dict page (no spaces), e.: 'code,label'
     * @param   string            $fields_to_update       fields to update on dict page (no spaces), e.: 'code,label'
     * @param   string            $fields_to_insert       fields to insert on dict page (no spaces), e.: 'code,label'
     * @param   string            $table_pk_field         table primary key field
     * @param   array             $fields_help            fields help summary or link, e.: array('code' => 'summary..', 'label' => 'summary..')
     */
    function add_dictionary(&$module, $table_name, $table_label, $select_fields = 'rowid, label, active', $table_sort = 'label ASC', $fields_to_show = 'label', $fields_to_update = 'label', $fields_to_insert = 'label', $table_pk_field = 'rowid', $fields_help = array())
    {
        global $conf;

        $dict_table = MAIN_DB_PREFIX.$table_name;
        $modulepart = str_replace('_', '', $module->rights_class);

        if (! isset($module->dictionaries['langs'])) {
            $module->dictionaries['langs'] = $module->langfiles[0];
        }

        $module->dictionaries['tabname'][]        = $dict_table;
        $module->dictionaries['tablib'][]         = $table_label;
        $module->dictionaries['tabsql'][]         = 'SELECT '.$select_fields.' FROM '.$dict_table;
        $module->dictionaries['tabsqlsort'][]     = $table_sort;
        $module->dictionaries['tabfield'][]       = $fields_to_show;
        $module->dictionaries['tabfieldvalue'][]  = $fields_to_update;
        $module->dictionaries['tabfieldinsert'][] = $fields_to_insert;
        $module->dictionaries['tabrowid'][]       = $table_pk_field;
        $module->dictionaries['tabcond'][]        = $conf->$modulepart->enabled;
        $module->dictionaries['tabhelp'][]        = $fields_help;
    }
}

// --------------------------------------------------------------------

if (! function_exists('add_cron_job'))
{
    /**
     * Add a cron job to a module object instance
     *
     * @param   DolibarrModules   $module                Module object instance
     * @param   string            $label                 Job label
     * @param   string            $type                  Job type, possible values: 'command', 'method'
     * @param   string            $command               Job shell command (if $type = 'command')
     * @param   string            $class                 Job class (if $type = 'method'), e.: '/mymodule/class/myobject.class.php'
     * @param   string            $object_name           Object name (if $type = 'method'), e.: 'MyObject'
     * @param   string            $object_method         Object method (if $type = 'method'), e.: 'doScheduledJob'
     * @param   string            $method_parameters     Method parameters (if $type = 'method'), e.: 'param1, param2'
     * @param   string            $comment               Job comment
     * @param   int               $frequency             Job frequency or execution time, e.: 2 (if $frequency_unit = 3600 it will be considered as every 2 hours)
     * @param   int               $frequency_unit        Job frequency unit, e.: 3600 (1 hour), 3600*24 (1 day), 3600*24*7 (1 week)
     * @param   int               $status                Job status at module installation: 0 = disabled, 1 = enabled
     * @param   int               $priority              Job priority (number from 0 to 100)
     */
    function add_cron_job(&$module, $label, $type, $command = '', $class = '', $object_name = '', $object_method = '', $method_parameters = '', $comment = '', $frequency = 2, $frequency_unit = 3600, $status = 0, $priority = 0)
    {
        $module->cronjobs[] = array(
            'label'         => $label,
            'jobtype'       => $type,
            'class'         => $class,
            'objectname'    => $object_name,
            'method'        => $object_method,
            'command'       => $command,
            'parameters'    => $method_parameters,
            'comment'       => $comment,
            'frequency'     => $frequency,
            'unitfrequency' => $frequency_unit,
            'status'        => $status,
            'priority'      => $priority,
            'test'          => true
        );
    }
}

// --------------------------------------------------------------------

if (! function_exists('add_cron_command'))
{
    /**
     * Add a cron job using a command to a module object instance
     *
     * @param   DolibarrModules   $module                Module object instance
     * @param   string            $label                 Job label
     * @param   string            $command               Job shell command
     * @param   int               $frequency             Job frequency or execution time, e.: 2 (if $frequency_unit = 3600 it will be considered as every 2 hours)
     * @param   int               $frequency_unit        Job frequency unit, e.: 3600 (1 hour), 3600*24 (1 day), 3600*24*7 (1 week)
     * @param   string            $comment               Job comment
     * @param   int               $priority              Job priority (number from 0 to 100)
     * @param   int               $status                Job status at module installation: 0 = disabled, 1 = enabled
     */
    function add_cron_command(&$module, $label, $command, $frequency, $frequency_unit, $comment = '', $priority = 0, $status = 1)
    {
        add_cron_job($module, $label, 'command', $command, '', '', '', '', $comment, $frequency, $frequency_unit, $status, $priority);
    }
}

// --------------------------------------------------------------------

if (! function_exists('add_cron_method'))
{
    /**
     * Add a cron job using a method to a module object instance
     *
     * @param   DolibarrModules   $module                Module object instance
     * @param   string            $label                 Job label
     * @param   string            $class                 Job class, e.: '/mymodule/class/myobject.class.php'
     * @param   string            $object_name           Object name, e.: 'MyObject'
     * @param   string            $object_method         Object method, e.: 'doScheduledJob'
     * @param   string            $method_parameters     Method parameters, e.: 'param1, param2'
     * @param   int               $frequency             Job frequency or execution time, e.: 2 (if $frequency_unit = 3600 it will be considered as every 2 hours)
     * @param   int               $frequency_unit        Job frequency unit, e.: 3600 (1 hour), 3600*24 (1 day), 3600*24*7 (1 week)
     * @param   string            $comment               Job comment
     * @param   int               $priority              Job priority (number from 0 to 100)
     * @param   int               $status                Job status at module installation: 0 = disabled, 1 = enabled
     */
    function add_cron_method(&$module, $label, $class, $object_name, $object_method, $method_parameters, $frequency, $frequency_unit, $comment = '', $priority = 0, $status = 1)
    {
        add_cron_job($module, $label, 'method', '', $class, $object_name, $object_method, $method_parameters, $comment, $frequency, $frequency_unit, $status, $priority);
    }
}

// --------------------------------------------------------------------

if (! function_exists('set_doc_model'))
{
    /**
     * Set default document model
     *
     * @param   DolibarrModules   $module         Module object instance
     * @param   string            $name           Model name
     * @param   string            $type           Model type (should be filled in case of submodules)
     * @param   string            $const_name     Model constant name
     */
    function set_doc_model(&$module, $name, $type = '', $const_name = '')
    {
        if (empty($type)) {
            $type = $module->rights_class;
        }

        if (empty($const_name)) {
            $const_name = strtoupper($module->rights_class).'_ADDON_PDF';
        }

        add_constant($module, $const_name, $name);
        delDocumentModel($name, $type);
        addDocumentModel($name, $type);
    }
}

// --------------------------------------------------------------------

if (! function_exists('set_num_model'))
{
    /**
     * Set default numbering model
     *
     * @param   DolibarrModules   $module         Module object instance
     * @param   string            $name           Model name
     * @param   string            $const_name     Model constant name
     */
    function set_num_model(&$module, $name, $const_name = '')
    {
        if (empty($const_name)) {
            $const_name = strtoupper($module->rights_class).'_ADDON';
        }

        add_constant($module, $const_name, $name);
    }
}

// --------------------------------------------------------------------

if (! function_exists('enable_module_for_external_users'))
{
    /**
     * Enable module for external users
     *
     * @param   string   $module_rights_class   Module rights class
     */
    function enable_module_for_external_users($module_rights_class)
    {
        global $db, $conf;

        if (empty($conf->global->MAIN_MODULES_FOR_EXTERNAL) || strpos($conf->global->MAIN_MODULES_FOR_EXTERNAL, $module_rights_class) === false) {
            $value = empty($conf->global->MAIN_MODULES_FOR_EXTERNAL) ? $module_rights_class : join(',', array($conf->global->MAIN_MODULES_FOR_EXTERNAL, $module_rights_class));
            dolibarr_set_const($db, 'MAIN_MODULES_FOR_EXTERNAL', $value, 'chaine', 1, '', $conf->entity);
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('disable_module_for_external_users'))
{
    /**
     * Disable module for external users
     *
     * @param   string   $module_rights_class   Module rights class
     */
    function disable_module_for_external_users($module_rights_class)
    {
        global $db, $conf;

        if (! empty($conf->global->MAIN_MODULES_FOR_EXTERNAL))
        {
            $modules_list = explode(',', $conf->global->MAIN_MODULES_FOR_EXTERNAL);
            $found = false;
            foreach ($modules_list as $key => $value)
            {
                if ($value == $module_rights_class) {
                    unset($modules_list[$key]);
                    $found = true;
                }
            }
            if ($found) {
                $value = empty($modules_list) ? '' : join(',', $modules_list);
                dolibarr_set_const($db, 'MAIN_MODULES_FOR_EXTERNAL', $value, 'chaine', 1, '', $conf->entity);
            }
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('get_constant_name'))
{
    /**
     * Returns module constant name
     *
     * @param   DolibarrModules   $module   module object instance
     */
    function get_constant_name(&$module)
    {
        $class_name = preg_replace('/^mod/i', '', get_class($module));

        return 'MAIN_MODULE_'.strtoupper($class_name);
    }
}
