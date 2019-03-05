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

// Load page & setup lib
dol_include_once('damb/lib/page.lib.php');
dol_include_once('damb/lib/setup.lib.php');

// Control access to page
control_access('$user->admin');

// Get parameters
$action = GETPOST('action', 'alpha');

/**
 * Actions
 */

load_default_actions($action);

/**
 * View
 */

print_header('Setup', array('admin', 'damb@damb'));

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?mainmenu=home">'.print_trans('BackToModuleList', false).'</a>';
print_subtitle('Setup', 'title_setup.png', $linkback);

$tabs = array(
    array('title' => 'Setup', 'url' => 'damb/admin/setup.php?mainmenu=home', 'active' => true),
    array('title' => 'Changelog', 'url' => 'damb/admin/changelog.php?mainmenu=home'),
    array('title' => 'About', 'url' => 'damb/admin/about.php?mainmenu=home')
);
print_tabs($tabs, 'AdvancedModuleBuilder', 'package.png@damb', -1);

print_subtitle('AuthorSettings');

print_options(array(
    array('name' => 'DAMB_AUTHOR_NAME', 'type' => 'text', 'desc' => 'AuthorName'),
    array('name' => 'DAMB_AUTHOR_URL', 'type' => 'text', 'desc' => 'AuthorUrl'),
    array('name' => 'DAMB_AUTHOR_EMAIL', 'type' => 'text', 'desc' => 'AuthorEmail'),
    array('name' => 'DAMB_AUTHOR_DOLISTORE_URL', 'type' => 'text', 'desc' => 'AuthorDolistoreUrl')
));

print_footer(true);
