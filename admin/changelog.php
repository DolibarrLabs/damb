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

// Control access to page
control_access('$user->admin');

/**
 * View
 */

print_header('Changelog', array('admin', 'damb@damb'), array('damb/css/changelog.css'));

print_subtitle('Changelog', 'title_generic.png', 'link:modules_list');

print_admin_tabs('Changelog');

load_template('damb/tpl/changelog.tpl.php', array(
    'changelog_file' => dol_buildpath('damb/changelog.json')
));

print_footer(true);
