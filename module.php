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
if (false === (@include_once '../main.inc.php')) { // From htdocs directory
    require_once '../../main.inc.php'; // From "custom" directory
}

// Load page lib
dol_include_once('damb/lib/page.lib.php');

// Control access to page
//control_access('$user->rights->damb->use');

/**
 * Actions
 */



/**
 * View
 */

print_header('AdvancedModuleBuilder', 'damb@damb');

print_subtitle('AdvancedModuleBuilder', 'title_setup.png');

$tabs = array(
    array('title' => 'Module', 'url' => 'damb/module.php', 'active' => true),
    array('title' => 'Widget', 'url' => 'damb/widget.php')
);
print_tabs($tabs, 'AdvancedModuleBuilder', 'package.png@damb');

load_template('damb/tpl/builder/module.tpl.php');

print_footer(true);
