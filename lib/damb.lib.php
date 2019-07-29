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
 * Print admin page(s) tabs
 * 
 * @param  string $active Active tab title
 */
function print_admin_tabs($active = 'Setup')
{
    $tabs = array(
        array('title' => 'Setup', 'url' => 'damb/admin/setup.php?mainmenu=home'),
        array('title' => 'Changelog', 'url' => 'damb/admin/changelog.php?mainmenu=home'),
        array('title' => 'About', 'url' => 'damb/admin/about.php?mainmenu=home')
    );

    foreach ($tabs as $key => $tab)
    {
        if ($tab['title'] == $active) {
            $tabs[$key]['active'] = true;
            break;
        }
    }

    print_tabs($tabs, 'AdvancedModuleBuilder', 'module.png@damb', -1);
}
