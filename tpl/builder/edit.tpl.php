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
 * The following variables are required for this template:
 * $module_folder
 * $module_object
 * $module_class
 * $current_path
 * $custom_dir_path
 * $action
 * $file
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

global $db, $langs, $conf;

$form = new Form($db);
$root_path = $custom_dir_path.'/'.$module_folder;

?>

<div id="containerlayout">
    <div id="ecm-layout-west" class="inline-block">
        <?php if (! empty($module_folder) && is_object($module_object)) { ?>
            <table class="liste allwidth noborderbottom">
                <?php
                    $setup_page_link = '#';
                    if (is_array($module_object->config_page_url) && ! empty($module_object->config_page_url)) {
                        $setup_page = explode('@', $module_object->config_page_url[0]);
                        $setup_page_link = dol_buildpath($module_folder.'/admin/'.$setup_page[0], 1);
                    }
                    $edit_module_link = dol_buildpath('damb/builder/edit.php?action=editfile&module='.$module_folder.'&file='.$module_folder.'/core/modules/'.$module_class['file'], 1);
                ?>
                <tr class="liste_titre">
                    <th><?php echo $langs->trans('ModuleInformations'); ?></th>
                    <th align="right">
                        <a href="<?php echo $edit_module_link; ?>"><?php echo img_edit($langs->trans('Edit'), 0, 'class="inline-block valignmiddle"'); ?></a>
                        <a href="<?php echo $setup_page_link; ?>" target="_blank"><?php echo img_picto($langs->trans('ModuleSettings'), 'setup.png', 'class="inline-block valignmiddle"'); ?></a>
                    </th>
                </tr>
                <tr>
                    <td><?php echo $langs->trans('ModuleName'); ?></td>
                    <td><?php echo $module_object->name; ?></td>
                </tr>
                <tr>
                    <td><?php echo $langs->trans('ModuleNumber'); ?></td>
                    <td><?php echo $module_object->numero; ?></td>
                </tr>
                <tr>
                    <td><?php echo $langs->trans('ModuleFamily'); ?></td>
                    <td><?php echo $module_object->family; ?></td>
                </tr>
                <tr>
                    <td><?php echo $langs->trans('ModuleVersion'); ?></td>
                    <td><?php echo $module_object->version; ?></td>
                </tr>
                <tr>
                    <td><?php echo $langs->trans('ModuleStatus'); ?></td>
                    <td>
                        <?php if (empty($conf->{$module_object->rights_class}->enabled)) { ?>
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
                    <th>
                        <a href="<?php echo (file_exists($root_path.'/core/boxes') ? dol_buildpath('damb/builder/edit.php?module='.$module_folder.'&path='.$root_path.'/core/boxes', 1) : '#'); ?>"><?php echo $langs->trans('Widgets'); ?></a>
                    </th>
                    <th align="right">
                        <a id="add_widget" href="<?php echo dol_buildpath('damb/builder/edit.php?module='.$module_folder, 1); ?>"><?php echo img_edit_add($langs->trans('AddWidget')); ?></a>
                    </th>
                </tr>
                <?php
                    // Get widgets
                    foreach ($module_object->boxes as $widget)
                    {
                        $widget_file = explode('@', $widget['file']);
                        $widget_file_name_no_ext = rtrim($widget_file[0], '.php');
                        $widget_class_name = ucfirst($widget_file_name_no_ext);
                        $widget_file_name = $widget_file_name_no_ext.'.php';
                        $widget_file_path = $module_folder.'/core/boxes/'.$widget_file_name;
                        dol_include_once($widget_file_path);
                        if (class_exists($widget_class_name))
                        {
                            $widget_object = new $widget_class_name($db);
                ?>
                    <tr>
                        <td><?php echo img_object('', $widget_object->boximg, 'class="inline-block valignmiddle" style="width: 16px;"').' '.$widget_file_name; ?></td>
                        <td align="right">
                            <a href="<?php echo dol_buildpath('damb/builder/edit.php?action=editfile&module='.$module_folder.'&file='.$widget_file_path, 1); ?>"><?php echo img_edit($langs->trans('Edit'), false, 'class="inline-block valignmiddle"'); ?></a>
                        </td>
                    </tr>
                <?php
                        }
                    }
                ?>
            </table>
            <br>
            <table class="liste allwidth noborderbottom">
                <tr class="liste_titre">
                    <th>
                        <a href="<?php echo (file_exists($root_path.'/bin') ? dol_buildpath('damb/builder/edit.php?module='.$module_folder.'&path='.$root_path.'/bin', 1) : '#'); ?>"><?php echo $langs->trans('Packages'); ?></a>
                    </th>
                    <th align="right">
                        <a href="<?php echo dol_buildpath('damb/builder/edit.php?action=buildpackage&module='.$module_folder, 1); ?>"><?php echo img_edit_add($langs->trans('BuildPackage')); ?></a>
                    </th>
                </tr>
                <?php foreach (directory_files_list($root_path.'/bin/*.zip', true) as $package) { ?>
                    <tr>
                        <td>
                            <a href="<?php echo dol_buildpath($module_folder.'/bin/'.$package, 1); ?>" title="<?php echo $langs->trans('Download'); ?>"><?php echo img_picto('', 'package.png@damb', 'class="inline-block valignmiddle"').' '.$package; ?></a>
                        </td>
                        <td align="right">
                            <a class="delete_package" data-name="<?php echo $package; ?>" href="<?php echo dol_buildpath('damb/builder/edit.php?action=deletepackage&module='.$module_folder.'&package='.$package, 1); ?>"><?php echo img_delete($langs->trans('Delete'), 'class="inline-block valignmiddle"'); ?></a>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            <table class="liste allwidth noborderbottom">
                <?php
                    // Retrieve modules list
                    $modules_list = array();
                    foreach (directory_files_list($custom_dir_path.'/*', false, true) as $path)
                    {
                        $class = get_module_class($path);
                        if (! empty($class))
                        {
                            $modules_list[] = array(
                                'path' => $path,
                                'class' => $class
                            );
                        }
                    }
                ?>
                <tr class="liste_titre">
                    <th><?php echo $langs->trans('ExternalModules').' (<strong>'.count($modules_list).'</strong>)'; ?></th>
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
                                            <a class="delete_module" data-name="<?php echo $module_folder_name; ?>" href="<?php echo dol_buildpath('damb/builder/edit.php?action=delete&module='.$module_folder_name, 1); ?>"><?php echo img_delete($langs->trans('Delete'), 'class="inline-block valignmiddle"'); ?></a>
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
                <input type="hidden" name="path" value="<?php echo (empty($current_path) ? $custom_dir_path.'/'.dirname($file) : $current_path); ?>">
                <input type="hidden" name="file" value="<?php echo $file; ?>">
                <?php
                    $content = file_get_contents($custom_dir_path.'/'.$file);
                    $format = pathinfo($file, PATHINFO_EXTENSION);
                    $formats = array(
                        'php'  => 'php',
                        'html' => 'html',
                        'json' => 'json',
                        'css'  => 'css',
                        'js'   => 'javascript'
                    );
                    $doleditor = new DolEditor('editfilecontent', $content, '', '300', 'Full', 'In', true, false, 'ace', 0, '99%', '');
                    echo $doleditor->Create(1, '', false, $langs->trans('File').' : '.$file, (isset($formats[$format]) ? $formats[$format] : 'text'));
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
                            <a id="new_page" href="<?php echo dol_buildpath('damb/builder/edit.php?module='.$module_folder.'&path='.$current_path, 1); ?>"><?php echo img_view($langs->trans('NewPage')); ?></a>
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
                                    $file_ext = pathinfo($file, PATHINFO_EXTENSION);
                                    $is_image = $is_dir ? false : in_array($file_ext, array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'ico', 'svg'));
                                    $is_txt = $is_dir || $is_image ? false : in_array($file_ext, array('php', 'txt', 'lang', 'json', 'sql', 'csv', 'xml', 'htm', 'html', 'xhtml', 'css', 'js'));
                                    $file_name = basename($file);
                                    $file_path = str_replace($custom_dir_path.'/', '', $file);
                                    $file_url = str_replace(DOL_DOCUMENT_ROOT, DOL_URL_ROOT, $file);
                                    $link = $is_dir ? dol_buildpath('damb/builder/edit.php?module='.$module_folder.'&path='.$file, 1) : ($is_image || ! $is_txt ? $file_url : dol_buildpath('damb/builder/edit.php?action=editfile&module='.$module_folder.'&file='.$file_path, 1));
                                ?>
                                    <li class="<?php echo $is_dir ? 'directory' : 'file'; ?>">
                                        <a href="<?php echo $link.($is_image ? '" class="view_image' : ''); ?>"><?php echo $file_name; ?></a>
                                        <div class="ecmjqft">
                                            <?php if ($is_txt) { ?>
                                                <?php if (preg_match('/^[a-zA-Z0-9]+\.(php|css.php|js.php)$/', $file_name)) { ?>
                                                    <a href="<?php echo $file_url; ?>" target="_blank"><?php echo img_picto($langs->trans('Preview'), 'play.png', 'class="inline-block valignmiddle"'); ?></a>
                                                <?php } ?>
                                                <a href="<?php echo $link; ?>"><?php echo img_edit($langs->trans('Edit'), false, 'class="inline-block valignmiddle"'); ?></a>
                                            <?php } ?>
                                            <?php if ($conf->global->DAMB_ALLOW_FILE_DELETE) { ?>
                                                <a class="delete_file" data-name="<?php echo $file_name; ?>" href="<?php echo dol_buildpath('damb/builder/edit.php?action=deletefile&module='.$module_folder.'&path='.dirname($file).'&file='.$file_path, 1); ?>"><?php echo img_delete($langs->trans('Delete'), 'class="inline-block valignmiddle"'); ?></a>
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
