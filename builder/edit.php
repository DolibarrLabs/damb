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

// Load Dolibarr environment (mandatory)
if (false === (@include_once '../../main.inc.php')) { // From htdocs directory
    require_once '../../../main.inc.php'; // From "custom" directory
}

// Load admin & files lib
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

// Load DolEditor class
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

// Load page & builder lib
dol_include_once('damb/lib/page.lib.php');
dol_include_once('damb/lib/builder.lib.php');

// Load SimpleImage & Zipper classes
dol_include_once('damb/class/builder/simple_image.class.php');
dol_include_once('damb/class/builder/zipper.class.php');

// Control access to page
control_access('$user->admin');

// Load translations
load_langs(array('admin', 'damb@damb'));

// Get parameters
$action = GETPOST('action', 'alpha');
$module = GETPOST('module', 'alpha');
$cancel = GETPOST('cancel', 'alpha');
$file = GETPOST('file', 'alpha');
$path = GETPOST('path', 'alpha');
$type = GETPOST('type', 'alpha');
$name = GETPOST('name', 'alpha');
$package = GETPOST('package', 'alpha');

// Get module class & object
global $dolibarr_main_document_root_alt;
$custom_dir_path = $dolibarr_main_document_root_alt;
if (! empty($module))
{
    global $db;

    $module_class = get_module_class($custom_dir_path.'/'.$module);
    dol_include_once($module.'/core/modules/'.$module_class['file']);
    $module_object = new $module_class['name']($db);
}

/**
 * Actions
 */

// Activate/Deactivate
if (in_array($action, array('activate', 'deactivate')) && ! empty($module))
{
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

    if ($conf->global->DAMB_ALLOW_MODULE_DELETE && dol_delete_dir_recursive($custom_dir_path.'/'.$module)) {
        success_message('ModuleDeleted', $module);
    }

    redirect('edit.php');
}

// New file/folder
else if ($action == 'newfile' && ! empty($module))
{
    $success = false;

    if (empty($path)) {
        $path = $custom_dir_path.'/'.$module;
    }

    if (file_exists($path.'/'.$name)) {
        $message = ($type == 'file' ? 'FileExists' : 'FolderExists');
        error_message($message, $name);
    }
    else if ($type == 'file' && file_put_contents($path.'/'.$name, '') !== false) {
        success_message('FileCreated', $name);
        $success = true;
    }
    else if ($type == 'folder' && mkdir($path.'/'.$name)) {
        success_message('FolderCreated', $name);
        $success = true;
    }

    if ($success) {
        @chmod($path.'/'.$name, 0777);
    }

    redirect('edit.php?module='.$module.'&path='.$path);
}

// New page
else if ($action == 'newpage' && ! empty($module))
{
    if (empty($path)) {
        $path = $custom_dir_path.'/'.$module;
    }

    $page_name = strtolower($name);
    $page_path = $path.'/'.rtrim($page_name, '.php').'.php';

    if (file_exists($page_path)) {
        error_message('PageExists', $page_path);
    }
    else {
        $source_path = dol_buildpath('damb');
        $include_prefix = '';
        foreach (array_reverse(explode(DIRECTORY_SEPARATOR, $path)) as $folder) {
            if ($folder == $module) {
                break;
            }
            $include_prefix.= '../';
        }
        $data = array(
            'include_prefix' => $include_prefix,
            'module_folder' => $module,
            'lang_file' => $module,
            'page_title' => $name
        );
        $template = get_template($source_path.'/tpl/module/page.tpl.php', $data);
        file_put_contents($page_path, $template);
        if (file_exists($page_path)) {
            @chmod($page_path, 0777);
            success_message('PageCreated', $page_path);
        }
        else {
            warning_message('WritePermissionDenied', $page_path);
        }
    }

    redirect('edit.php?module='.$module.'&path='.$path);
}

// Delete file
else if ($action == 'deletefile' && ! empty($module))
{
    global $conf;

    if ($conf->global->DAMB_ALLOW_FILE_DELETE && ! empty($file))
    {
        $file_path = $custom_dir_path.'/'.$file;

        if (is_dir($file_path))
        {
            if (dol_delete_dir_recursive($file_path)) {
                success_message('FolderDeleted', basename($file));
            }
        }
        else if (unlink($file_path)) {
            success_message('FileDeleted', basename($file));
        }
    }

    redirect('edit.php?module='.$module.'&path='.$path);
}

// Save file
else if ($action == 'savefile' && ! empty($module) && empty($cancel))
{
    $content = GETPOST('editfilecontent', 'none');

    if (! empty($file))
    {
        $file_path = $custom_dir_path.'/'.$file;

        if (file_put_contents($file_path, $content) !== false) {
            success_message('FileSaved', basename($file));
        }
    }

    redirect('edit.php?module='.$module.'&path='.$path);
}

// Build package
else if ($action == 'buildpackage' && ! empty($module) && is_object($module_object))
{
    // Get package name
    $package_name = 'module_'.$module.'-'.$module_object->version.'.zip';
    $package_folder = $custom_dir_path.'/'.$module.'/bin';
    $package_file = $package_folder.'/'.$package_name;

    // Check if ZipArchive class exist
    if (! class_exists('ZipArchive'))
    {
        error_message('ZipArchiveNotFound');
    }
    // Check if package file already exist
    else if (file_exists($package_file))
    {
        error_message('PackageExists', $package_name);
    }
    else
    {
        // Create packages folder if not exists
        @mkdir($package_folder);
        @chmod($package_folder, 0777);

        // Set current directory path to 'dolibarr/custom' so we can zip the module
        chdir($custom_dir_path);

        // Zip/Package module
        $zipper = new Zipper();
        $result = $zipper->create($package_file, $module, array('bin', '.git', '.gitignore'));

        if ($result && file_exists($package_file))
        {
            // Set file permissions
            @chmod($package_file, 0777);

            // Set success message
            success_message('PackageCreated', $package_name);
        }
    }

    redirect('edit.php?module='.$module);
}

// Delete package
else if ($action == 'deletepackage' && ! empty($module) && ! empty($package))
{
    if (unlink($custom_dir_path.'/'.$module.'/bin/'.$package)) {
        success_message('PackageDeleted', $package);
    }

    redirect('edit.php?module='.$module);
}

// Add widget
else if ($action == 'addwidget' && ! empty($module) && ! empty($name) && isset($_FILES['picture']))
{
    global $conf;

    // Get widget data
    $source_path = dol_buildpath('damb');
    $module_path = $custom_dir_path.'/'.$module;
    $widget_name = sanitize_string(strtolower($name));
    $widget_folder = $module_path.'/core/boxes';
    $widget_file_name = $widget_name.'.php';
    $widget_file = $widget_folder.'/'.$widget_file_name;
    $widget_picture = $widget_name.'.'.pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);

    // Check if widget already exist
    if (file_exists($widget_file))
    {
        error_message('WidgetExists', $widget_file_name);
    }
    else
    {
        // Create widgets folder if not exists
        @mkdir($widget_folder);
        @chmod($widget_folder, 0777);

        // Upload widget picture
        $picture_target_file = $module_path.'/img/'.'object_'.$widget_picture;
        move_uploaded_file($_FILES['picture']['tmp_name'], $picture_target_file);

        // Resize widget picture
        $image = new SimpleImage();
        $image->load($picture_target_file);
        $image->resize(16, 16);
        $image->save($picture_target_file, $image->getImageType());

        // Create widget class
        $widget_class_data = array(
            'module_folder' => $module,
            'widget_class_name' => ucfirst($widget_name),
            'widget_lang_file' => $module.'@'.$module,
            'widget_label' => $name,
            'widget_picture' => $widget_picture,
            'widget_position' => 1,
            'enable_widget' => 1,
            'widget_title' => $name
        );
        $template = get_template($source_path.'/tpl/module/core/widget_class.tpl.php', $widget_class_data);
        file_put_contents($widget_file, $template);
        @chmod($widget_file, 0777);

        // Copy widget lib
        $widget_lib = $module_path.'/lib/widget.lib.php';
        if (! file_exists($widget_lib)) {
            copy($source_path.'/lib/widget.lib.php', $widget_lib);
            @chmod($widget_lib, 0777);
        }

        // Add widget to module class
        if (! empty($module_class))
        {
            $module_class_file = $module_path.'/core/modules/'.$module_class['file'];
            $module_class_content = file_get_contents($module_class_file);
            $module_class_new_content = preg_replace('/public function __construct\(\$db\)(.*?)\{/s', 'public function __construct($db)'."\n    {\n        ".'// '.$name."\n        ".'$this->boxes[] = array(\'file\' => \''.$widget_name.'@'.$module.'\', \'note\' => \'\', \'enabledbydefaulton\' => \'Home\');'."\n", $module_class_content);
            file_put_contents($module_class_file, $module_class_new_content);
            // Activate widget
            if ($conf->{$module_object->rights_class}->enabled) {
                $module_object->boxes[] = array('file' => $widget_name.'@'.$module, 'note' => '', 'enabledbydefaulton' => 'Home');
                $module_object->insert_boxes();
            }
        }

        // Set success message
        success_message('WidgetAdded', $widget_file_name);
    }

    redirect('edit.php?module='.$module);
}

/**
 * View
 */

$css_files = array(
    'damb/css/builder/edit.css.php'
);
$js_files = array(
    'damb/js/builder/edit.js.php',
    'includes/ace/ace.js',
    'includes/ace/ext-statusbar.js'
);

if (function_exists('version_compare') && version_compare(DOL_VERSION, '11.0.0') >= 0) { // Fix ace js files path for dolibarr 11.x
    $js_files = array(
        'damb/js/builder/edit.js.php',
        'includes/ace/src/ace.js',
        'includes/ace/src/ext-statusbar.js',
        'includes/ace/src/ext-language_tools.js'
    );
}

print_header('ModuleBuilder', array(), $css_files, $js_files);

print_subtitle('ModuleBuilder', 'title_setup.png');

$tabs = array(
    array('title' => 'NewModule', 'url' => 'damb/builder/new.php'),
    array('title' => 'EditModule', 'url' => 'damb/builder/edit.php', 'active' => true)
);
$settings_link = '<a href="'.dol_buildpath('damb/admin/setup.php', 1).'" target="_blank" class="inline-block valignmiddle paddingtopbottom">'.img_picto(print_trans('BuilderSettings', false), 'setup.png').'</a>';
$modules_list_link = '<a href="'.DOL_URL_ROOT.'/admin/modules.php" target="_blank" class="inline-block valignmiddle paddingtopbottom">'.img_picto(print_trans('ModulesList', false), 'list.png').'</a>';
$widgets_list_link = '<a href="'.DOL_URL_ROOT.'/admin/boxes.php" target="_blank" class="inline-block valignmiddle paddingtopbottom">'.img_picto(print_trans('WidgetsList', false), 'stats.png').'</a>';
print_tabs($tabs, 'AdvancedModuleBuilder', 'module.png@damb', -1, $widgets_list_link.' '.$modules_list_link.' '.$settings_link);

load_template('damb/tpl/builder/edit.tpl.php', array(
    'module_folder' => $module,
    'module_object' => (isset($module_object) ? $module_object : null),
    'module_class' => (isset($module_class) ? $module_class : null),
    'current_path' => $path,
    'custom_dir_path' => $custom_dir_path,
    'action' => $action,
    'file' => $file
));

print_footer(true);
