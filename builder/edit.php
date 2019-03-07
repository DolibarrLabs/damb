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

// Load Dolibarr environment (mandatory)
if (false === (@include_once '../../main.inc.php')) { // From htdocs directory
    require_once '../../../main.inc.php'; // From "custom" directory
}

// Load admin & files lib
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

// Load page & builder lib
dol_include_once('damb/lib/page.lib.php');
dol_include_once('damb/lib/builder.lib.php');

// Control access to page
control_access('$user->admin');

// Load translations
load_langs(array('admin', 'damb@damb'));

// Get parameters
$action = GETPOST('action', 'alpha');
$module = GETPOST('module', 'alpha');
$file = GETPOST('file', 'alpha');
$path = GETPOST('path', 'alpha');
$type = GETPOST('type', 'alpha');
$name = GETPOST('name', 'alpha');

/**
 * Actions
 */

// Activate/Deactivate
if (in_array($action, array('activate', 'deactivate')) && ! empty($module))
{
    $module_class = get_module_class(DOL_DOCUMENT_ROOT.'/custom/'.$module);

    if (! empty($module_class))
    {
        if ($action == 'activate') {
            activateModule($module_class['name']);
        }
        else {
            unActivateModule($module_class['name']);
        }

        redirect('edit.php?module='.$module);
    }
}

// Delete module
else if ($action == 'delete' && ! empty($module))
{
    global $conf;

    if ($conf->global->DAMB_ALLOW_MODULE_DELETE && dol_delete_dir_recursive(DOL_DOCUMENT_ROOT.'/custom/'.$module)) {
        success_message('ModuleDeleted', $module);
    }

    redirect('edit.php');
}

// New file/folder
else if ($action == 'newfile' && ! empty($module))
{
    if (empty($path)) {
        $path = DOL_DOCUMENT_ROOT.'/custom/'.$module;
    }

    if ($type == 'file' && file_put_contents($path.'/'.$name, '') !== false) {
        success_message('FileCreated', $name);
    }
    else if ($type == 'folder' && mkdir($path.'/'.$name)) {
        success_message('FolderCreated', $name);
    }

    redirect('edit.php?module='.$module.'&path='.$path);
}

// Delete file
else if ($action == 'deletefile' && ! empty($module))
{
    global $conf;

    if ($conf->global->DAMB_ALLOW_FILE_DELETE && ! empty($file))
    {
        if (is_dir($file))
        {
            if (dol_delete_dir_recursive($file)) {
                success_message('FolderDeleted', basename($file));
            }
        }
        else if (unlink($file)) {
            success_message('FileDeleted', basename($file));
        }
    }

    redirect('edit.php?module='.$module.'&path='.$path);
}

/**
 * View
 */

print_header('ModuleBuilder', array(), array('damb/css/builder/edit.css.php'), array('damb/js/builder/edit.js.php'));

print_subtitle('ModuleBuilder', 'title_setup.png');

$tabs = array(
    array('title' => 'NewModule', 'url' => 'damb/builder/new.php'),
    array('title' => 'EditModule', 'url' => 'damb/builder/edit.php', 'active' => true)
);
$settings_link = '<a href="'.dol_buildpath('damb/admin/setup.php', 1).'" target="_blank" class="inline-block paddingtopbottom">'.img_picto(print_trans('BuilderSettings', false), 'setup.png').'</a>';
$modules_list_link = '<a href="'.dol_buildpath('damb/builder/edit.php', 1).'" class="inline-block paddingtopbottom">'.img_picto(print_trans('ModulesList', false), 'list.png').'</a>';
print_tabs($tabs, 'AdvancedModuleBuilder', 'package.png@damb', -1, (empty($module) ? $settings_link : $modules_list_link));

load_template('damb/tpl/builder/edit.tpl.php', array(
    'module_folder' => $module,
    'current_path' => $path
));

print_footer(true);
