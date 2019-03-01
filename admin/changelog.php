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

// Load page lib
dol_include_once('damb/lib/page.lib.php');

// Control access to page
control_access('$user->admin');

/**
 * View
 */

print_header('Changelog', array('admin', 'damb@damb'), array('damb/css/changelog.css'));

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?mainmenu=home">'.print_trans('BackToModuleList', false).'</a>';
print_subtitle('Changelog', 'title_generic.png', $linkback);

$tabs = array(
    array('title' => 'Setup', 'url' => 'damb/admin/setup.php?mainmenu=home'),
    array('title' => 'Changelog', 'url' => 'damb/admin/changelog.php?mainmenu=home', 'active' => true),
    array('title' => 'About', 'url' => 'damb/admin/about.php?mainmenu=home')
);
print_tabs($tabs, 'AdvancedModuleBuilder', 'package.png@damb', -1);

load_template('damb/tpl/changelog.tpl.php', array(
    'changelog_file' => dol_buildpath('damb/changelog.json')
));

print_footer(true);
