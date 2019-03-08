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
 * $current_path
 * $action
 * $file
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
                <?php
                    $module_class = get_module_class($root_path);
                    dol_include_once($module_folder.'/core/modules/'.$module_class['file']);
                    $module = new $module_class['name']($db);
                    $setup_page_link = '#';
                    if (is_array($module->config_page_url) && ! empty($module->config_page_url)) {
                        $setup_page = explode('@', $module->config_page_url[0]);
                        $setup_page_link = dol_buildpath($module_folder.'/admin/'.$setup_page[0], 1);
                    }
                ?>
                <tr class="liste_titre">
                    <th><?php echo $langs->trans('ModuleInformations'); ?></th>
                    <th align="right">
                        <a href="<?php echo $setup_page_link; ?>" target="_blank"><?php echo img_picto($langs->trans('ModuleSettings'), 'setup.png'); ?></a>
                    </th>
                </tr>
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
                <?php
                    // Retrieve modules list
                    $modules_list = array();
                    foreach (directory_files_list(DOL_DOCUMENT_ROOT.'/custom/*', false, true) as $module_path)
                    {
                        $module_class = get_module_class($module_path);
                        if (! empty($module_class))
                        {
                            $modules_list[] = array(
                                'path' => $module_path,
                                'class' => $module_class
                            );
                        }
                    }
                ?>
                <tr class="liste_titre">
                    <th><?php echo $langs->trans('Modules').' (<strong>'.count($modules_list).'</strong>)'; ?></th>
                    <th align="right">
                        <a href="<?php echo dol_buildpath('damb/builder/edit.php', 1); ?>"><?php echo img_picto($langs->trans('Refresh'), 'refresh.png'); ?></a>
                    </th>
                </tr>
                <tr>
                    <td colspan="2">
                        <ul class="ecmjqft">
                            <?php
                                foreach ($modules_list as $module_data)
                                {
                                    $module_folder_name = basename($module_data['path']);
                                    dol_include_once($module_folder_name.'/core/modules/'.$module_data['class']['file']);
                                    $module = new $module_data['class']['name']($db);
                            ?>
                                <li class="directory">
                                    <a href="<?php echo dol_buildpath('damb/builder/edit.php?module='.$module_folder_name, 1); ?>"><?php echo $module_folder_name; ?></a>
                                    <div class="ecmjqft">
                                        <?php
                                            // Module Info
                                            $htmltooltip = '<b>'.$langs->trans('ModuleName').'</b>: '.$module->name.'<br>';
                                            $htmltooltip.= '<b>'.$langs->trans('ModuleNumber').'</b>: '.$module->numero.'<br>';
                                            $htmltooltip.= '<b>'.$langs->trans('ModuleFamily').'</b>: '.$module->family.'<br>';
                                            $htmltooltip.= '<b>'.$langs->trans('ModuleVersion').'</b>: '.$module->version.'<br>';
                                            $htmltooltip.= '<b>'.$langs->trans('ModuleStatus').'</b>: '.(empty($conf->{$module->rights_class}->enabled) ? img_picto($langs->trans('Disabled'), 'switch_off') : img_picto($langs->trans('Enabled'), 'switch_on'));
                                            echo $form->textwithpicto('', $htmltooltip, 1, 'info');
                                        ?>
                                        <?php if ($conf->global->DAMB_ALLOW_MODULE_DELETE) { ?>
                                            <a class="delete_module" href="<?php echo dol_buildpath('damb/builder/edit.php?action=delete&module='.$module_folder_name, 1); ?>"><?php echo img_delete($langs->trans('DeleteModule'), 'class="inline-block valignmiddle"'); ?></a>
                                        <?php } ?>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                    </td>
                </tr>
            </table>
        <?php } ?>
    </div>
    <div id="ecm-layout-center" class="inline-block">
        <?php if ($action == 'editfile' && ! empty($file)) { ?>
            <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
                <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>">
                <input type="hidden" name="action" value="savefile">
                <input type="hidden" name="module" value="<?php echo $module_folder; ?>">
                <input type="hidden" name="path" value="<?php echo (empty($current_path) ? dirname($file) : $current_path); ?>">
                <input type="hidden" name="file" value="<?php echo $file; ?>">
                <?php
                    $content = file_get_contents($file);
                    $doleditor = new DolEditor('editfilecontent', $content, '', '300', 'Full', 'In', true, false, 'ace', 0, '99%', '');
                    echo $doleditor->Create(1, '', false, $langs->trans('File').' : '.str_replace(DOL_DOCUMENT_ROOT.'/custom/', '', $file), 'html');
                ?>
                <br>
                <center>
                    <input type="submit" class="button buttonforacesave" id="savefile" name="savefile" value="<?php echo $langs->trans('Save'); ?>">
                    <input type="submit" class="button" name="cancel" value="<?php echo $langs->trans('Cancel'); ?>">
                </center>
            </form>
        <?php } else { ?>
            <table class="liste allwidth noborderbottom">
                <tr class="liste_titre">
                    <th>
                        <?php if (! empty($module_folder) && ! empty($current_path) && $current_path != $root_path) { ?>
                            <a href="<?php echo dol_buildpath('damb/builder/edit.php?module='.$module_folder.'&path='.dirname($current_path), 1); ?>"><?php echo img_previous(); ?></a>
                            <a href="<?php echo dol_buildpath('damb/builder/edit.php?module='.$module_folder, 1); ?>"><?php echo $langs->trans('Files'); ?></a>
                        <?php } else { ?>
                            <?php echo $langs->trans('Files'); ?>
                        <?php } ?>
                    </th>
                    <th align="right">
                        <?php if (! empty($module_folder)) { ?>
                            <a id="new_file" href="<?php echo dol_buildpath('damb/builder/edit.php?module='.$module_folder.'&path='.$current_path, 1); ?>"><?php echo img_edit_add($langs->trans('NewFile')); ?></a>
                        <?php } ?>
                    </th>
                </tr>
                <tr>
                    <?php if (empty($module_folder)) { ?>
                        <td class="opacitymedium" colspan="2"><?php echo $langs->trans('SelectModule'); ?></td>
                    <?php } else { ?>
                        <td colspan="2">
                            <ul class="ecmjqft">
                                <?php foreach (directory_files_list((empty($current_path) ? $root_path : $current_path).'/*') as $file) {
                                    $is_dir = is_dir($file);
                                    $is_image = $is_dir ? false : in_array(pathinfo($file, PATHINFO_EXTENSION), array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'ico'));
                                    $file_name = basename($file);
                                    $link = $is_dir ? dol_buildpath('damb/builder/edit.php?module='.$module_folder.'&path='.$file, 1) : ($is_image ? str_replace(DOL_DOCUMENT_ROOT, DOL_URL_ROOT, $file) : dol_buildpath('damb/builder/edit.php?action=editfile&module='.$module_folder.'&file='.$file, 1));
                                ?>
                                    <li class="<?php echo $is_dir ? 'directory' : 'file'; ?>">
                                        <a href="<?php echo $link.($is_image ? '" class="view_image' : ''); ?>"><?php echo $file_name; ?></a>
                                        <div class="ecmjqft">
                                            <?php if (! $is_dir && ! $is_image) { ?>
                                                <?php if (preg_match('/^[a-zA-Z0-9]+\.php$/', $file_name)) { ?>
                                                    <a href="<?php echo str_replace(DOL_DOCUMENT_ROOT, DOL_URL_ROOT, $file); ?>" target="_blank"><?php echo img_picto($langs->trans('View'), 'play.png', 'class="inline-block valignmiddle"'); ?></a>
                                                <?php } ?>
                                                <a href="<?php echo $link; ?>"><?php echo img_edit($langs->trans('Edit'), false, 'class="inline-block valignmiddle"'); ?></a>
                                            <?php } ?>
                                            <?php if ($conf->global->DAMB_ALLOW_FILE_DELETE) { ?>
                                                <a class="delete_file" href="<?php echo dol_buildpath('damb/builder/edit.php?action=deletefile&module='.$module_folder.'&path='.dirname($file).'&file='.$file, 1); ?>"><?php echo img_delete($langs->trans('Delete'), 'class="inline-block valignmiddle"'); ?></a>
                                            <?php } ?>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                        </td>
                    <?php } ?>
                </tr>
            </table>
        <?php } ?>
    </div>
</div>
