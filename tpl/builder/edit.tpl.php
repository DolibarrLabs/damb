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

/**
 * The following variables are required for this template:
 * $module_folder
 * $module_path
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

global $db, $langs, $conf;

$form = new Form($db);
$root_path = DOL_DOCUMENT_ROOT.'/custom/'.$module_folder;

?>

<div id="containerlayout">
    <div id="ecm-layout-west" class="inline-block">
        <?php if (! empty($module_folder)) { ?>
            <table class="liste allwidth noborderbottom">
                <tr class="liste_titre">
                    <th><?php echo $langs->trans('ModuleInformations'); ?></th>
                    <th align="right">
                        <a href="<?php echo dol_buildpath($module_folder.'/admin/setup.php', 1); ?>" target="_blank"><?php echo img_picto($langs->trans('ModuleSettings'), 'setup.png'); ?></a>
                    </th>
                </tr>
                <?php
                    $module_class = get_module_class($root_path);
                    dol_include_once($module_folder.'/core/modules/'.$module_class['file']);
                    $module = new $module_class['name']($db);
                ?>
                <tr>
                    <td><?php echo $langs->trans('ModuleName'); ?></td>
                    <td><?php echo $module->name; ?></td>
                </tr>
                <tr>
                    <td><?php echo $langs->trans('ModuleNumber'); ?></td>
                    <td><?php echo $module->numero; ?></td>
                </tr>
                <tr>
                    <td><?php echo $langs->trans('ModuleFamily'); ?></td>
                    <td><?php echo $module->family; ?></td>
                </tr>
                <tr>
                    <td><?php echo $langs->trans('ModuleVersion'); ?></td>
                    <td><?php echo $module->version; ?></td>
                </tr>
                <tr>
                    <td><?php echo $langs->trans('ModuleStatus'); ?></td>
                    <td>
                        <?php if (empty($conf->{$module->rights_class}->enabled)) { ?>
                            <a href="<?php echo dol_buildpath('damb/builder/edit.php?action=activate&module='.$module_folder, 1); ?>"><?php echo img_picto($langs->trans('Disabled'), 'switch_off'); ?></a>
                        <?php } else { ?>
                            <a href="<?php echo dol_buildpath('damb/builder/edit.php?action=deactivate&module='.$module_folder, 1); ?>"><?php echo img_picto($langs->trans('Enabled'), 'switch_on'); ?></a>
                        <?php } ?>
                    </td>
                </tr>
            </table>
            <br>
            <table class="liste allwidth noborderbottom">
                <tr class="liste_titre">
                    <th><?php echo $langs->trans('Widgets'); ?></th>
                    <th align="right">
                        <a href="#"><?php echo img_edit_add($langs->trans('AddWidget')); ?></a>
                    </th>
                </tr>
                <?php // TODO: get widgets list ?>
            </table>
            <br>
            <table class="liste allwidth noborderbottom">
                <tr class="liste_titre">
                    <th><?php echo $langs->trans('Packages'); ?></th>
                    <th align="right">
                        <a href="#"><?php echo img_edit_add($langs->trans('BuildPackage')); ?></a>
                    </th>
                </tr>
                <?php // TODO: get packages list ?>
            </table>
        <?php } else { ?>
            <table class="liste allwidth noborderbottom">
                <tr class="liste_titre">
                    <th><?php echo $langs->trans('Modules'); ?></th>
                    <th align="right">
                        <a href="<?php echo dol_buildpath('damb/builder/edit.php', 1); ?>"><?php echo img_picto($langs->trans('Refresh'), 'refresh.png'); ?></a>
                    </th>
                </tr>
                <tr>
                    <td colspan="2">
                        <ul class="ecmjqft">
                            <?php
                                foreach (directory_files_list(DOL_DOCUMENT_ROOT.'/custom/*') as $module_path)
                                {
                                    $module_class = get_module_class($module_path);
                                    if (! empty($module_class))
                                    {
                                        $module_folder_name = basename($module_path);
                                        dol_include_once($module_folder_name.'/core/modules/'.$module_class['file']);
                                        $module = new $module_class['name']($db);
                            ?>
                                <li class="directory">
                                    <a href="<?php echo dol_buildpath('damb/builder/edit.php?module='.$module_folder_name, 1); ?>"><?php echo $module_folder_name; ?></a>
                                    <div class="ecmjqft">
                                        <?php
                                            // Module Info
                                            $htmltooltip = '<b>'.$langs->trans('ModuleName').'</b>: '.$module->name.'<br>';
                                            $htmltooltip.= '<b>'.$langs->trans('ModuleNumber').'</b>: '.$module->numero.'<br>';
                                            $htmltooltip.= '<b>'.$langs->trans('ModuleFamily').'</b>: '.$module->family.'<br>';
                                            $htmltooltip.= '<b>'.$langs->trans('ModuleVersion').'</b>: '.$module->version;
                                            echo $form->textwithpicto('', $htmltooltip, 1, 'info');
                                        ?>
                                        <?php if ($conf->global->DAMB_ALLOW_MODULE_DELETE) { ?>
                                            <a class="delete_module" href="<?php echo dol_buildpath('damb/builder/edit.php?action=delete&module='.$module_folder_name, 1); ?>"><?php echo img_delete($langs->trans('DeleteModule'), 'class="inline-block valignmiddle"'); ?></a>
                                        <?php } ?>
                                    </div>
                                </li>
                            <?php
                                    }
                                }
                            ?>
                        </ul>
                    </td>
                </tr>
            </table>
        <?php } ?>
    </div>
    <div id="ecm-layout-center" class="inline-block">
        <table class="liste allwidth noborderbottom">
            <tr class="liste_titre">
                <th>
                    <?php if (! empty($module_folder) && ! empty($module_path) && $module_path != $root_path) { ?>
                        <a href="<?php echo dol_buildpath('damb/builder/edit.php?module='.$module_folder.'&path='.dirname($module_path), 1); ?>"><?php echo img_previous(); ?></a>
                    <?php } ?>
                    <?php echo $langs->trans('Files'); ?>
                </th>
                <th align="right">
                    <a id="new_file" href="<?php echo dol_buildpath('damb/builder/edit.php?module='.$module_folder.'&path='.$module_path, 1); ?>"><?php echo img_edit_add($langs->trans('NewFile')); ?></a>
                </th>
            </tr>
            <tr>
                <?php if (empty($module_folder)) { ?>
                    <td class="opacitymedium" colspan="2"><?php echo $langs->trans('SelectModule'); ?></td>
                <?php } else { ?>
                    <td colspan="2">
                        <ul class="ecmjqft">
                            <?php foreach (directory_files_list((empty($module_path) ? $root_path : $module_path).'/*') as $file) { ?>
                                <li class="nocellnopadd">
                                    <?php if (is_dir($file)) { ?>
                                        <?php echo img_picto_common('', 'treemenu/folder2.png').' '; ?>
                                        <a href="<?php echo dol_buildpath('damb/builder/edit.php?module='.$module_folder.'&path='.$file, 1); ?>"><?php echo basename($file); ?></a>
                                    <?php } else { ?>
                                        <?php echo img_picto_common('', 'mime/text.png').' '.basename($file); ?>
                                        <?php if (! in_array(pathinfo($file, PATHINFO_EXTENSION), array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'ico'))) { ?>
                                            <div class="ecmjqft">
                                                <a href="#"><?php echo img_edit($langs->trans('EditFile')); ?></a>
                                            </div>
                                        <?php } ?>
                                    <?php } ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </td>
                <?php } ?>
            </tr>
        </table>
    </div>
</div>
