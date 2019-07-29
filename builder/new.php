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
    global $conf, $dolibarr_main_document_root_alt;

    // Get data
    $module_name = GETPOST('name', 'alpha');
    $module_name_toupper = sanitize_string(strtoupper($module_name));
    $module_name_tolower = sanitize_string(strtolower($module_name));
    $module_name_ucfirst = sanitize_string(ucfirst($module_name));
    $module_picture_extension = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
    $data = array(
        'module_name' => $module_name,
        'module_version' => GETPOST('version', 'alpha'),
        'module_number' => GETPOST('number', 'int'),
        'module_family' => GETPOST('family', 'alpha'),
        'module_position' => GETPOST('position', 'int'),
        'module_rights_class' => GETPOST('rights_class', 'alpha'),
        'module_folder' => GETPOST('folder_name', 'alpha'),
        'module_picture' => $module_name_tolower.'.'.$module_picture_extension
    );
    $add_extrafields = GETPOST('add_extrafields', 'int');
    $add_changelog = GETPOST('add_changelog', 'int');
    $add_num_models = GETPOST('add_num_models', 'int');
    $add_doc_models = GETPOST('add_doc_models', 'int');
    $module_path = $dolibarr_main_document_root_alt.'/'.$data['module_folder'];
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
    if ($add_num_models) {
        $module_folders[] = 'core/num_models';
    }
    if ($add_doc_models) {
        $module_folders[] = 'core/doc_models';
        $module_folders[] = 'core/modules/'.$data['module_folder'];
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
        $lang_file = $data['module_folder']; // module folder name used as lang file name
        $module_class_data = array(
            'module_class_name' => $module_name_ucfirst,
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

        // Create module lib
        $more_tabs = '';
        if ($add_extrafields) {
            $more_tabs.= "array('title' => 'ExtraFields', 'url' => '".$data['module_folder']."/admin/extrafields.php?mainmenu=home'),\n        ";
        }
        if ($add_changelog) {
            $more_tabs.= "array('title' => 'Changelog', 'url' => '".$data['module_folder']."/admin/changelog.php?mainmenu=home'),\n        ";
        }
        $module_lib_data = array(
            'module_name' => $module_name,
            'module_picture' => $data['module_picture'],
            'module_folder' => $data['module_folder'],
            'more_tabs' => $more_tabs
        );
        $template = get_template($source_path.'/tpl/module/lib/module_lib.tpl.php', $module_lib_data);
        file_put_contents($module_path.'/lib/'.$data['module_folder'].'.lib.php', $template);

        // Create setup page
        $settings = ($add_num_models || $add_doc_models ? '' : "print_trans('NoSetupAvailable');");
        $default_actions_parameters = '';
        if ($add_num_models) {
            $settings.= "print_subtitle('NumberingModels');\n";
            $settings.= "print_num_models('".$data['module_folder']."/core/num_models', '".$module_name_toupper."_ADDON');";
            $default_actions_parameters.= ", '".$module_name_toupper."_ADDON'";
        }
        if ($add_doc_models) {
            if ($add_num_models) $settings.= "\n\n";
            else $default_actions_parameters = "''";
            $settings.= "print_subtitle('DocumentModels');\n";
            $settings.= "print_doc_models('".$data['module_folder']."/core/doc_models', '".$data['module_folder']."', '".$module_name_toupper."_ADDON_PDF', '".$data['module_picture']."@".$data['module_folder']."');";
            $default_actions_parameters.= ", '".$module_name_tolower."', '".$module_name_toupper."_ADDON_PDF', '".$data['module_folder']."/core/doc_models', '".$data['module_folder']."'";
        }
        $setup_page_data = array(
            'module_folder' => $data['module_folder'],
            'lang_file' => $lang_file,
            'settings' => $settings,
            'default_actions_parameters' => $default_actions_parameters
        );
        $template = get_template($source_path.'/tpl/module/admin/setup_page.tpl.php', $setup_page_data);
        file_put_contents($module_path.'/admin/setup.php', $template);

        // Create about page
        $about_page_data = array(
            'module_picture' => $data['module_picture'],
            'module_class_name' => 'mod'.$module_class_data['module_class_name'],
            'module_folder' => $data['module_folder'],
            'lang_file' => $lang_file,
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
            $element_type = $module_name_tolower;
            $extrafields_page_data = array(
                'module_folder' => $data['module_folder'],
                'lang_file' => $lang_file,
                'element_type' => $element_type,
                'text_object' => $module_name
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
                'module_folder' => $data['module_folder'],
                'lang_file' => $lang_file
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
                'module_name' => $module_name_toupper,
                'current_year' => date('Y'),
                'author_name' => $module_class_data['author_name'],
                'module_name_translation' => $module_name.' = '.$module_name."\n".'Module'.$data['module_number'].'Name = '.$module_name,
                'module_desc_translation' => $module_name.'Description = '.$module_name."\n".'Module'.$data['module_number'].'Desc = '.$module_name
            );
            $template = get_template($source_path.'/tpl/module/langs/'.$translation_name.'.tpl.lang', $lang_data);
            file_put_contents($module_path.'/langs/'.$translation_name.'/'.$module_name_tolower.'.lang', $template);
        }

        // Copy libraries
        $libs = array(
            'module.lib.php',
            'page.lib.php',
            'setup.lib.php',
            'dolistore.lib.php'
        );
        foreach ($libs as $lib_name) {
            copy($source_path.'/lib/'.$lib_name, $module_path.'/lib/'.$lib_name);
        }

        // Create numbering models
        if ($add_num_models)
        {
            $num_models_data = array(
                'module_folder' => $data['module_folder'],
                'model_const_prefix' => $module_name_toupper,
                'table_name' => GETPOST('num_models_table_name', 'alpha'),
                'table_field_name' => GETPOST('num_models_table_field', 'alpha'),
                'model_prefix' => GETPOST('num_models_prefix', 'alpha'),
                'lang_file' => $lang_file,
                'module_name' => $module_name
            );
            $num_models = array(
                'marbre',
                'saphir'
            );
            foreach ($num_models as $model) {
                $template = get_template($source_path.'/tpl/module/core/num_models/'.$model.'.tpl.php', $num_models_data);
                file_put_contents($module_path.'/core/num_models/'.$model.'.php', $template);
            }
            // Copy numbering models class
            copy($source_path.'/class/module/num_model.class.php', $module_path.'/class/num_model.class.php');
        }

        // Create document models
        if ($add_doc_models)
        {
            $doc_models_data = array(
                'module_folder' => $data['module_folder'],
                'doc_model_class_name' => $module_name_ucfirst,
                'model_const_prefix' => $module_name_toupper
            );
            $doc_models = array(
                'pdf_azur',
                'pdf_crabe'
            );
            foreach ($doc_models as $model) {
                $template = get_template($source_path.'/tpl/module/core/doc_models/'.$model.'.tpl.php', $doc_models_data);
                file_put_contents($module_path.'/core/doc_models/'.$model.'.modules.php', $template);
            }
            // Create document models class
            $doc_model_class_data = array(
                'doc_model_class_name' => $doc_models_data['doc_model_class_name'],
                'doc_model_type' => $module_name_tolower
            );
            $template = get_template($source_path.'/tpl/module/core/doc_model_class.tpl.php', $doc_model_class_data);
            file_put_contents($module_path.'/core/modules/'.$data['module_folder'].'/modules_'.$data['module_folder'].'.php', $template);
        }

        // Set files/folders permissions
        chmod_r($module_path, 0777, 0777);

        // Set success message
        success_message('ModuleCreated', array($module_name, $module_path));

        // Redirect
        redirect('edit.php?module='.$data['module_folder']);
    }
}

/**
 * View
 */

print_header('ModuleBuilder', array(), array(), array('damb/js/builder/new.js'));

print_subtitle('ModuleBuilder', 'title_setup.png');

$tabs = array(
    array('title' => 'NewModule', 'url' => 'damb/builder/new.php', 'active' => true),
    array('title' => 'EditModule', 'url' => 'damb/builder/edit.php')
);
$settings_link = '<a href="'.dol_buildpath('damb/admin/setup.php', 1).'" target="_blank" class="inline-block valignmiddle paddingtopbottom">'.img_picto(print_trans('BuilderSettings', false), 'setup.png').'</a>';
print_tabs($tabs, 'AdvancedModuleBuilder', 'module.png@damb', -1, $settings_link);

load_template('damb/tpl/builder/new.tpl.php');

print_footer(true);
