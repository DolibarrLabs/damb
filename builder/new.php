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

// Load page & builder lib
dol_include_once('damb/lib/page.lib.php');
dol_include_once('damb/lib/builder.lib.php');

// Load SimpleImage class
dol_include_once('damb/class/builder/simple_image.class.php');

// Control access to page
control_access('$user->admin');

// Load translations
load_langs(array('admin', 'damb@damb'));

// Get parameters
$action = GETPOST('action', 'alpha');

/**
 * Actions
 */

if ($action == 'create')
{
    // Get data
    $module_name = GETPOST('name', 'alpha');
    $module_picture_extension = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
    $data = array(
        'module_name' => $module_name,
        'module_version' => GETPOST('version', 'alpha'),
        'module_number' => GETPOST('number', 'int'),
        'module_family' => GETPOST('family', 'alpha'),
        'module_position' => GETPOST('position', 'int'),
        'module_rights_class' => GETPOST('rights_class', 'alpha'),
        'module_folder' => GETPOST('folder_name', 'alpha'),
        'module_picture' => sanitize_string(strtolower($module_name)).'.'.$module_picture_extension
    );
    $add_extrafields = GETPOST('add_extrafields', 'int');
    $add_changelog = GETPOST('add_changelog', 'int');
    $add_num_models = GETPOST('add_num_models', 'int');
    $add_doc_models = GETPOST('add_doc_models', 'int');
    $module_path = DOL_DOCUMENT_ROOT.'/custom/'.$data['module_folder'];
    $source_path = dol_buildpath('damb');
    $module_folders = array(
        'admin',
        'class',
        'core/modules',
        'css',
        'js',
        'img',
        'sql',
        'tpl',
        'lib'
    );
    $translations = array(
        'en_US',
        'fr_FR'
    );
    foreach ($translations as $translation_name) {
        $module_folders[] = 'langs/'.$translation_name;
    }

    // Create module folders if not exist
    if (! mkdir_r($module_folders, 0777, $module_path))
    {
        // Set error message
        error_message('ModuleExists', $module_path);
    }
    else
    {
        // Upload module picture
        $picture_target_dir = $module_path.'/img/';
        $picture_target_file = $picture_target_dir.$data['module_picture'];
        move_uploaded_file($_FILES['picture']['tmp_name'], $picture_target_file);

        // Add mini picture
        $image = new SimpleImage();
        $image->load($picture_target_file);
        $image->resize(16, 16);
        $image->save($picture_target_dir.'object_'.$data['module_picture'], $image->getImageType());

        // Add menu icon (this is mandatory starting from Dolibarr 8)
        copy($picture_target_file, $picture_target_dir.pathinfo($data['module_picture'], PATHINFO_FILENAME).'_over.'.$module_picture_extension);

        // Create module class
        global $conf;
        $lang_file = $data['module_folder']; // module folder name used as lang file name
        $module_class_data = array(
            'module_class_name' => sanitize_string(ucfirst($module_name)),
            'module_setup_page' => 'setup.php',
            'module_desc' => $module_name.'Description',
            'author_name' => $conf->global->DAMB_AUTHOR_NAME,
            'author_url' => $conf->global->DAMB_AUTHOR_URL,
            'module_dolibarr_min' => '3, 8',
            'module_php_min' => '4, 0',
            'module_depends' => '',
            'module_required_by' => '',
            'module_conflict_with' => '',
            'module_lang_files' => "'".$lang_file.'@'.$data['module_folder']."'"
        );
        $template = get_template($source_path.'/tpl/module/core/module_class.tpl.php', array_merge($data, $module_class_data));
        file_put_contents($module_path.'/core/modules/mod'.$module_class_data['module_class_name'].'.class.php', $template);

        // Create setup page
        $more_tabs = '';
        if ($add_extrafields) {
            $more_tabs.= "array('title' => 'ExtraFields', 'url' => '".$data['module_folder']."/admin/extrafields.php?mainmenu=home'),\n    ";
        }
        if ($add_changelog) {
            $more_tabs.= "array('title' => 'Changelog', 'url' => '".$data['module_folder']."/admin/changelog.php?mainmenu=home'),\n    ";
        }
        $settings = ($add_num_models || $add_doc_models ? '' : "print_trans('NoSetupAvailable');");
        if ($add_num_models) {
            $settings.= "print_num_models('', '".sanitize_string(strtoupper($module_name))."_ADDON');\n";
        }
        if ($add_doc_models) {
            if ($add_num_models) $settings.= "\n";
            $settings.= "print_doc_models('', '', '".sanitize_string(strtoupper($module_name))."_ADDON_PDF', '".$data['module_picture']."@".$data['module_folder']."');\n";
        }
        $setup_page_data = array(
            'module_name' => $module_name,
            'module_picture' => $data['module_picture'],
            'module_folder' => $data['module_folder'],
            'lang_file' => $lang_file,
            'more_tabs' => $more_tabs,
            'settings' => $settings
        );
        $template = get_template($source_path.'/tpl/module/admin/setup_page.tpl.php', $setup_page_data);
        file_put_contents($module_path.'/admin/setup.php', $template);

        // Create about page
        $about_page_data = array(
            'module_name' => $module_name,
            'module_picture' => $data['module_picture'],
            'module_class_name' => 'mod'.$module_class_data['module_class_name'],
            'module_folder' => $data['module_folder'],
            'lang_file' => $lang_file,
            'more_tabs' => $more_tabs,
            'author_email' => $conf->global->DAMB_AUTHOR_EMAIL,
            'author_dolistore_url' => $conf->global->DAMB_AUTHOR_DOLISTORE_URL
        );
        $template = get_template($source_path.'/tpl/module/admin/about_page.tpl.php', $about_page_data);
        file_put_contents($module_path.'/admin/about.php', $template);

        // Copy about template
        copy($source_path.'/tpl/about.tpl.php', $module_path.'/tpl/about.tpl.php');

        // Create extrafields page
        if ($add_extrafields)
        {
            $element_type = sanitize_string(strtolower($module_name));
            $extrafields_page_data = array(
                'module_name' => $module_name,
                'module_picture' => $data['module_picture'],
                'module_folder' => $data['module_folder'],
                'lang_file' => $lang_file,
                'element_type' => $element_type,
                'text_object' => $module_name,
                'more_tabs' => ($add_changelog ? "array('title' => 'Changelog', 'url' => '".$data['module_folder']."/admin/changelog.php?mainmenu=home'),\n    " : '')
            );
            $template = get_template($source_path.'/tpl/module/admin/extrafields_page.tpl.php', $extrafields_page_data);
            file_put_contents($module_path.'/admin/extrafields.php', $template);
            // Create extrafields sql files
            $extrafields_table_data = array(
                'current_year' => date('Y'),
                'author_name' => $module_class_data['author_name'],
                'table_name' => $element_type.'_extrafields' // without prefix (llx_)
            );
            $template = get_template($source_path.'/tpl/module/sql/extrafields.tpl.sql', $extrafields_table_data);
            file_put_contents($module_path.'/sql/llx_'.$extrafields_table_data['table_name'].'.sql', $template);
            $template = get_template($source_path.'/tpl/module/sql/extrafields.tpl.key.sql', $extrafields_table_data);
            file_put_contents($module_path.'/sql/llx_'.$extrafields_table_data['table_name'].'.key.sql', $template);
        }

        // Create changelog page
        if ($add_changelog)
        {
            $changelog_page_data = array(
                'module_name' => $module_name,
                'module_picture' => $data['module_picture'],
                'module_folder' => $data['module_folder'],
                'lang_file' => $lang_file,
                'more_tabs' => ($add_extrafields ? "array('title' => 'ExtraFields', 'url' => '".$data['module_folder']."/admin/extrafields.php?mainmenu=home'),\n    " : '')
            );
            $template = get_template($source_path.'/tpl/module/admin/changelog_page.tpl.php', $changelog_page_data);
            file_put_contents($module_path.'/admin/changelog.php', $template);
            // Create changelog.json
            $changelog_json_data = array(
                'version' => $data['module_version'],
                'current_date' => date('Y/m/d')
            );
            $template = get_template($source_path.'/tpl/module/changelog.tpl.json', $changelog_json_data);
            file_put_contents($module_path.'/changelog.json', $template);
            // Copy changelog template
            copy($source_path.'/tpl/changelog.tpl.php', $module_path.'/tpl/changelog.tpl.php');
            // Copy changelog css
            copy($source_path.'/css/changelog.css', $module_path.'/css/changelog.css');
        }

        // Create lang files
        foreach ($translations as $translation_name) {
            $lang_data = array(
                'module_name' => strtoupper($module_name),
                'current_year' => date('Y'),
                'author_name' => $module_class_data['author_name'],
                'module_name_translation' => 'Module'.$data['module_number'].'Name = '.$module_name,
                'module_desc_translation' => 'Module'.$data['module_number'].'Desc = '.$module_name,
                'permissions_translation' => '', // TODO
                'menus_translation' => '' // TODO
            );
            $template = get_template($source_path.'/tpl/module/langs/'.$translation_name.'.tpl.lang', $lang_data);
            file_put_contents($module_path.'/langs/'.$translation_name.'/'.$translation_name.'.lang', $template);
        }

        // Copy libraries
        $libs = array(
            'module.lib.php',
            'page.lib.php',
            'setup.lib.php'
        );
        foreach ($libs as $lib_name) {
            copy($source_path.'/lib/'.$lib_name, $module_path.'/lib/'.$lib_name);
        }

        // Create numbering models
        // Create document models
        /*
        $num_models_table_name = GETPOST('num_models_table_name', 'alpha');
        $num_models_table_field = GETPOST('num_models_table_field', 'alpha');
        $num_models_prefix = GETPOST('num_models_prefix', 'alpha');
        */

        // Set files/folders permissions
        chmod_r($module_path, 0777, 0777);

        // Set success message
        success_message('ModuleCreated', array($module_name, $module_path));
    }
}

/**
 * View
 */

print_header('ModuleBuilder', array(), array(), array('damb/js/builder/new.js'));

print_subtitle('ModuleBuilder', 'title_setup.png');

$tabs = array(
    array('title' => 'NewModule', 'url' => 'damb/builder/new.php', 'active' => true),
    array('title' => 'EditModule', 'url' => 'damb/builder/edit.php'),
    array('title' => 'DeployModule', 'url' => 'damb/builder/deploy.php')
);
$settings_link = '<a href="'.dol_buildpath('damb/admin/setup.php', 1).'" target="_blank" class="inline-block paddingtopbottom">'.img_picto(print_trans('BuilderSettings', false), 'setup.png').'</a>';
print_tabs($tabs, 'AdvancedModuleBuilder', 'package.png@damb', -1, $settings_link);

load_template('damb/tpl/builder/new.tpl.php');

print_footer(true);
