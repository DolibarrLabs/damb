<?php

/**
 * This file was generated by DAMB
 *
 * @copyright   Copyright (c) 2019 - 2020, AXeL-dev
 * @license     GPL
 * @link        https://github.com/AXeL-dev/damb
 */

/**
 * Print admin page(s) tabs
 * 
 * @param  string $active Active tab title
 */
function print_admin_tabs($active = 'Setup')
{
    $tabs = array(
        array('title' => 'Setup', 'url' => '${module_folder}/admin/setup.php?mainmenu=home'),
        ${more_tabs}array('title' => 'About', 'url' => '${module_folder}/admin/about.php?mainmenu=home')
    );

    foreach ($tabs as $key => $tab)
    {
        if ($tab['title'] == $active) {
            $tabs[$key]['active'] = true;
            break;
        }
    }

    print_tabs($tabs, '${module_name}', '${module_picture}@${module_folder}', -1);
}
