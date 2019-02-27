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

/**
 * Add a constant to a module object instance
 *
 * @param     $module   module object instance
 * @param     $name     constant name
 * @param     $value    constant value
 * @param     $desc     constant description / note
 * @param     $type     constant type
 */
if (! function_exists('add_constant'))
{
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

/**
 * Add a widget to a module object instance
 *
 * @param     $module                    module object instance
 * @param     $widget_file               widget file
 * @param     $note                      widget note
 * @param     $enabled_by_default_on     where to enable the widget by default
 */
if (! function_exists('add_widget'))
{
    function add_widget(&$module, $widget_file, $note = '', $enabled_by_default_on = 'Home')
    {
        $module->boxes[] = array(
            'file' => $widget_file,
            'note' => $note,
            'enabledbydefaulton' => $enabled_by_default_on
        );
    }
}

/**
 * Add a permission to a module object instance
 *
 * @param     $module                 module object instance
 * @param     $name                   permission name
 * @param     $desc                   permission description
 * @param     $type                   permission type: 'r', 'c', 'm', 'd'
 * @param     $enabled_by_default     enable the permission by default for all users
 */
if (! function_exists('add_permission'))
{
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

/**
 * Add a sub permission to a module object instance
 *
 * @param     $module                 module object instance
 * @param     $perm_name              permission name
 * @param     $subperm_name           sub permission name
 * @param     $desc                   permission description
 * @param     $type                   permission type: 'r', 'c', 'm', 'd'
 * @param     $enabled_by_default     enable the permission by default for all users
 */
if (! function_exists('add_sub_permission'))
{
    function add_sub_permission(&$module, $perm_name, $subperm_name, $desc = '', $type = '', $enabled_by_default = 1)
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

/**
 * Add a menu to a module object instance
 *
 * @param     $module       module object instance
 * @param     $type         menu type 'top' or 'left'
 * @param     $fk_menu      where to insert menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode of parent menu
 * @param     $main_menu    main/top menu name
 * @param     $left_menu    left menu name
 * @param     $title        menu title
 * @param     $url          target page url
 * @param     $position     menu position
 * @param     $enabled      define condition to show or hide menu entry. Use '$conf->monmodule->enabled' if entry must be visible if module is enabled.
 * @param     $perms        use 'perms'=>'$user->rights->monmodule->level1->level2' if you want your menu with a permission rules
 * @param     $target       menu target, leave empty or use '_blank' to open in a new window / tab
 */
if (! function_exists('add_menu'))
{
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

/**
 * Add a top menu entry to a module object instance
 *
 * @param     $module    module object instance
 * @param     $name      menu name (should be the same as the module folder name, & the same as the menu picture file *.png)
 * @param     $title     menu title
 * @param     $url       target page url
 * @param     $perms     should anyone see & use the menu or use conditions like '$user->rights->monmodule->level1->level2'
 * @param     $enabled   should the menu be always enabled or use conditions like '$conf->monmodule->enabled'
 * @param     $target    menu target, leave empty or use '_blank' to open in a new window / tab
 * @param     $position  menu position
 */
if (! function_exists('add_top_menu'))
{
    function add_top_menu(&$module, $name, $title, $url, $perms = '1', $enabled = '1', $target = '', $position = 100)
    {
        add_menu($module, 'top', 0, $name, '', $title, $url, $position, $enabled, $perms, $target);
    }
}

/**
 * Add a left menu entry to a module object instance
 *
 * @param     $module       module object instance
 * @param     $main_menu    main/top menu name where to insert
 * @param     $name         menu name (codename for further use)
 * @param     $title        menu title
 * @param     $url          target page url
 * @param     $perms        should anyone see & use the menu or use conditions like '$user->rights->monmodule->level1->level2'
 * @param     $enabled      should the menu be always enabled or use conditions like '$conf->monmodule->enabled'
 * @param     $target       menu target, leave empty or use '_blank' to open in a new window / tab
 * @param     $position     menu position
 */
if (! function_exists('add_left_menu'))
{
    function add_left_menu(&$module, $main_menu, $name, $title, $url, $perms = '1', $enabled = '1', $target = '', $position = 100)
    {
        add_menu($module, 'left', 'fk_mainmenu='.$main_menu, $main_menu, $name, $title, $url, $position, $enabled, $perms, $target);
    }
}

/**
 * Add a left sub menu entry to a module object instance
 *
 * @param     $module       module object instance
 * @param     $main_menu    main/top menu name where to insert
 * @param     $left_menu    left menu name where to insert
 * @param     $name         menu name (codename for further use)
 * @param     $title        menu title
 * @param     $url          target page url
 * @param     $perms        should anyone see & use the menu or use conditions like '$user->rights->monmodule->level1->level2'
 * @param     $enabled      should the menu be always enabled or use conditions like '$conf->monmodule->enabled'
 * @param     $target       menu target, leave empty or use '_blank' to open in a new window / tab
 * @param     $position     menu position
 */
if (! function_exists('add_left_sub_menu'))
{
    function add_left_sub_menu(&$module, $main_menu, $left_menu, $name, $title, $url, $perms = '1', $enabled = '1', $target = '', $position = 100)
    {
        add_menu($module, 'left', 'fk_mainmenu='.$main_menu.',fk_leftmenu='.$left_menu, $main_menu, $name, $title, $url, $position, $enabled, $perms, $target);
    }
}

/**
 * Add a dictionary to a module object instance
 *
 * @param     $module                 module object instance
 * @param     $table_name             table name without prefix
 * @param     $table_label            table label
 * @param     $select_fields          select statement fields, e.: 'rowid, code, label, active'
 * @param     $table_sort             sort field & order, e.: 'label ASC'
 * @param     $fields_to_show         fields to show on dict page (no spaces), e.: 'code,label'
 * @param     $fields_to_update       fields to update on dict page (no spaces), e.: 'code,label'
 * @param     $fields_to_insert       fields to insert on dict page (no spaces), e.: 'code,label'
 * @param     $table_pk_field         table primary key field
 * @param     $fields_help            fields help summary or link, e.: array('code' => 'summary..', 'label' => 'summary..')
 */
if (! function_exists('add_dictionary'))
{
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

/**
 * Add a cron job to a module object instance
 *
 * @param     $module                Module object instance
 * @param     $label                 Job label
 * @param     $type                  Job type, possible values: 'command', 'method'
 * @param     $command               Job shell command (if $type = 'command')
 * @param     $class                 Job class (if $type = 'method'), e.: '/mymodule/class/myobject.class.php'
 * @param     $object_name           Object name (if $type = 'method'), e.: 'MyObject'
 * @param     $object_method         Object method (if $type = 'method'), e.: 'doScheduledJob'
 * @param     $method_parameters     Method parameters (if $type = 'method'), e.: 'param1, param2'
 * @param     $comment               Job comment
 * @param     $frequency             Job frequency or execution time, e.: 2 (if $frequency_unit = 3600 it will be considered as every 2 hours)
 * @param     $frequency_unit        Job frequency unit, e.: 3600 (1 hour), 3600*24 (1 day), 3600*24*7 (1 week)
 * @param     $status                Job status at module installation: 0 = disabled, 1 = enabled
 * @param     $priority              Job priority (number from 0 to 100)
 */
if (! function_exists('add_cron_job'))
{
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

/**
 * Add a cron job using a command to a module object instance
 *
 * @param     $module                Module object instance
 * @param     $label                 Job label
 * @param     $command               Job shell command
 * @param     $frequency             Job frequency or execution time, e.: 2 (if $frequency_unit = 3600 it will be considered as every 2 hours)
 * @param     $frequency_unit        Job frequency unit, e.: 3600 (1 hour), 3600*24 (1 day), 3600*24*7 (1 week)
 * @param     $comment               Job comment
 * @param     $priority              Job priority (number from 0 to 100)
 * @param     $status                Job status at module installation: 0 = disabled, 1 = enabled
 */
if (! function_exists('add_cron_command'))
{
    function add_cron_command(&$module, $label, $command, $frequency, $frequency_unit, $comment = '', $priority = 0, $status = 1)
    {
        add_cron_job($module, $label, 'command', $command, '', '', '', '', $comment, $frequency, $frequency_unit, $status, $priority);
    }
}

/**
 * Add a cron job using a method to a module object instance
 *
 * @param     $module                Module object instance
 * @param     $label                 Job label
 * @param     $class                 Job class, e.: '/mymodule/class/myobject.class.php'
 * @param     $object_name           Object name, e.: 'MyObject'
 * @param     $object_method         Object method, e.: 'doScheduledJob'
 * @param     $method_parameters     Method parameters, e.: 'param1, param2'
 * @param     $frequency             Job frequency or execution time, e.: 2 (if $frequency_unit = 3600 it will be considered as every 2 hours)
 * @param     $frequency_unit        Job frequency unit, e.: 3600 (1 hour), 3600*24 (1 day), 3600*24*7 (1 week)
 * @param     $comment               Job comment
 * @param     $priority              Job priority (number from 0 to 100)
 * @param     $status                Job status at module installation: 0 = disabled, 1 = enabled
 */
if (! function_exists('add_cron_method'))
{
    function add_cron_method(&$module, $label, $class, $object_name, $object_method, $method_parameters, $frequency, $frequency_unit, $comment = '', $priority = 0, $status = 1)
    {
        add_cron_job($module, $label, 'method', '', $class, $object_name, $object_method, $method_parameters, $comment, $frequency, $frequency_unit, $status, $priority);
    }
}

/**
 * Set default document model
 *
 * @param     $module           Module object instance
 * @param     $name             Model name
 * @param     $type             Model type (should be filled in case of submodules)
 * @param     $const_prefix     Prefix to use for constant name (should be filled in case of submodules)
 */
if (! function_exists('set_doc_model'))
{
    function set_doc_model(&$module, $name, $type = '', $const_prefix = '')
    {
        if (empty($type)) {
            $type = $module->rights_class;
        }

        if (empty($const_prefix)) {
            $const_prefix = strtoupper($module->rights_class);
        }

        add_constant($module, $const_prefix.'_ADDON_PDF', $name);
        delDocumentModel($name, $type);
        addDocumentModel($name, $type);
    }
}

/**
 * Set default numbering model
 *
 * @param     $module           Module object instance
 * @param     $name             Model name
 * @param     $const_prefix     Prefix to use for constant name (should be filled in case of submodules)
 */
if (! function_exists('set_num_model'))
{
    function set_num_model(&$module, $name, $const_prefix = '')
    {
        add_constant($module, $const_prefix.'_ADDON', $name);
    }
}

/**
 * Enable module for external users
 *
 * @param     $module_rights_class     Module rights class
 */
if (! function_exists('enable_module_for_external_users'))
{
    function enable_module_for_external_users($module_rights_class)
    {
        global $db, $conf;

        if (empty($conf->global->MAIN_MODULES_FOR_EXTERNAL) || strpos($conf->global->MAIN_MODULES_FOR_EXTERNAL, $module_rights_class) === false) {
            $value = empty($conf->global->MAIN_MODULES_FOR_EXTERNAL) ? $module_rights_class : join(',', array($conf->global->MAIN_MODULES_FOR_EXTERNAL, $module_rights_class));
            dolibarr_set_const($db, 'MAIN_MODULES_FOR_EXTERNAL', $value, 'chaine', 1, '', $conf->entity);
        }
    }
}

/**
 * Disable module for external users
 *
 * @param     $module_rights_class     Module rights class
 */
if (! function_exists('disable_module_for_external_users'))
{
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
