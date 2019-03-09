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
    // Delete module
    $('a.delete_module').on('click', function(e) {
        e.preventDefault();
        var link = $(this);
        var href = link.attr('href');
        $('<div></div>').dialog({
            title: '<?php echo $langs->trans('DeleteModule'); ?>',
            autoOpen: true,
            resizable: false,
            height: 200,
            width: 400,
            modal: true,
            closeOnEscape: false,
            open: function() {
                var module = link.parent().prev('a').text();
                $(this).html('<?php echo img_help('', '').' '.$langs->trans('Delete'); ?>' + ' <strong>' + module + '</strong> ?');
                $(this).parent().find('button.ui-button:eq(2)').focus();
            },
            close: function() {
                $(this).dialog('destroy');
            },
            buttons: {
                "<?php echo $langs->trans('Yes'); ?>": function() {
                    location.href = href;
                    $(this).dialog('close');
                },
                "<?php echo $langs->trans('No'); ?>": function() {
                    $(this).dialog('close');
                }
            }
        });
    });

    // Delete file
    $('a.delete_file').on('click', function(e) {
        e.preventDefault();
        var link = $(this);
        var href = link.attr('href');
        $('<div></div>').dialog({
            title: '<?php echo $langs->trans('DeleteFileFolder'); ?>',
            autoOpen: true,
            resizable: false,
            height: 200,
            width: 400,
            modal: true,
            closeOnEscape: false,
            open: function() {
                var file = link.parent().prev('a').text();
                $(this).html('<?php echo img_help('', '').' '.$langs->trans('Delete'); ?>' + ' <strong>' + file + '</strong> ?');
                $(this).parent().find('button.ui-button:eq(2)').focus();
            },
            close: function() {
                $(this).dialog('destroy');
            },
            buttons: {
                "<?php echo $langs->trans('Yes'); ?>": function() {
                    location.href = href;
                    $(this).dialog('close');
                },
                "<?php echo $langs->trans('No'); ?>": function() {
                    $(this).dialog('close');
                }
            }
        });
    });

    // Create new file
    $('a#new_file').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $(`<div>
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
            title: '<?php echo $langs->trans('NewFile'); ?>',
            autoOpen: true,
            resizable: false,
            height: 200,
            width: 400,
            modal: true,
            closeOnEscape: false,
            open: function() {
                $(this).find('input[name="name"]').focus();
            },
            close: function() {
                $(this).dialog('destroy');
            },
            buttons: {
                "<?php echo $langs->trans('Create'); ?>": function() {
                    var type = $(this).find('input[name="type"]:checked').val();
                    var name = $(this).find('input[name="name"]').val();
                    location.href = href + '&action=newfile&type=' + type + '&name=' + name;
                    $(this).dialog('close');
                },
                "<?php echo $langs->trans('Cancel'); ?>": function() {
                    $(this).dialog('close');
                }
            }
        });
    });

    // View image
    $('a.view_image').on('click', function(e) {
        e.preventDefault();
        var image_name = $(this).text();
        var image_src = $(this).attr('href');
        $('<div class="center"><img src="' + image_src + '"></div>').dialog({
            title: image_name,
            autoOpen: true,
            resizable: false,
            height: 'auto',
            width: 300,
            modal: true,
            closeOnEscape: false,
            open: function() {
                var dialog = $(this);
                $(this).find('img').on('load', function() {
                    var img_width = $(this).width();
                    var img_height = $(this).height();
                    if (img_width > 800) {
                        dialog.dialog('option', 'width', 800);
                    }
                    else if (img_width > 300) {
                        dialog.dialog('option', 'width', 'auto');
                    }
                    if (img_height > 500) {
                        dialog.dialog('option', 'height', 500);
                    }
                });
            },
            close: function() {
                $(this).dialog('destroy');
            },
            buttons: {
                "<?php echo $langs->trans('Close'); ?>": function() {
                    $(this).dialog('close');
                }
            }
        });
    });
});
