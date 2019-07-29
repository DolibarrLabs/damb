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

// Load page & damb lib
dol_include_once('damb/lib/page.lib.php');
dol_include_once('damb/lib/damb.lib.php');
dol_include_once('damb/lib/dolistore.lib.php');

// Load module class
dol_include_once('damb/core/modules/modDAMB.class.php');

// Control access to page
control_access('$user->admin');

/**
 * View
 */

print_header('About', array('admin', 'damb@damb'));

print_subtitle('About', 'title_generic.png', 'link:modules_list');

print_admin_tabs('About');

$module = new modDAMB(NULL);

load_template('damb/tpl/about.tpl.php', array(
    'module_name' => $module->name,
    'module_desc' => $module->description,
    'module_picture' => 'module.png@damb',
    'module_version' => $module->version,
    'module_url' => 'https://www.dolistore.com/en/modules/1121-Advanced-Module-Builder.html',
    'author_name' => $module->editor_name,
    'author_url' => $module->editor_url,
    'author_email' => 'contact.axel.dev@gmail.com',
    'author_dolistore_url' => 'https://www.dolistore.com/en/search?orderby=position&orderway=desc&search_query=axel'
));

print_footer(true);
