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

// Load Dolibarr environment
if (false === (@include '../../../main.inc.php')) { // From htdocs directory
    require '../../../../main.inc.php'; // From "custom" directory
}

global $langs;

$langs->load('damb@damb');

header('Content-Type: application/javascript');

?>

$(document).ready(function() {
    $('a.delete_module').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $('<div title="<?php echo $langs->trans('DeleteModule'); ?>"><?php echo img_help('', '').' '.$langs->trans('ConfirmDeleteModule'); ?></div>').dialog({
            autoOpen: true,
            open: function() {
                $(this).parent().find('button.ui-button:eq(2)').focus();
            },
            resizable: false,
            height: 200,
            width: 400,
            modal: true,
            closeOnEscape: false,
            buttons: {
                "<?php echo $langs->trans('Yes'); ?>": function() {
                    location.href = href;
                    $(this).dialog('close');
                },
                "<?php echo $langs->trans('No'); ?>": function() {
                    $(this).dialog('destroy');
                }
            }
        });
    });

    $('a#new_file').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $(`<div title="<?php echo $langs->trans('NewFile'); ?>">
            <br>
            <table class="paddingtopbottomonly allwidth">
                <tr>
                    <td><?php echo $langs->trans('FileType'); ?></td>
                    <td><input type="radio" name="type" value="file" id="file_radio" class="valignmiddle" checked /> <label for="file_radio"><?php echo $langs->trans('File'); ?></label> <input type="radio" name="type" value="folder" id="folder_radio" class="valignmiddle" /> <label for="folder_radio"><?php echo $langs->trans('Folder'); ?></label></td>
                </tr>
                <tr>
                    <td><?php echo $langs->trans('FileName'); ?></td>
                    <td><input type="text" name="name" value="" /></td>
                </tr>
            </table>
        </div>`).dialog({
            autoOpen: true,
            open: function() {
                $(this).find('input[name="name"]').focus();
            },
            resizable: false,
            height: 200,
            width: 400,
            modal: true,
            closeOnEscape: false,
            buttons: {
                "<?php echo $langs->trans('Create'); ?>": function() {
                    var type = $(this).find('input[name="type"]:checked').val();
                    var name = $(this).find('input[name="name"]').val();
                    location.href = href + '&action=new_file&type=' + type + '&name=' + name;
                    $(this).dialog('close');
                },
                "<?php echo $langs->trans('Cancel'); ?>": function() {
                    $(this).dialog('destroy');
                }
            }
        });
    });
});
