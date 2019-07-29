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

// Load Dolibarr environment
if (false === (@include '../../../main.inc.php')) { // From htdocs directory
    require '../../../../main.inc.php'; // From "custom" directory
}

global $langs;

$langs->load('damb@damb');

header('Content-Type: application/javascript');

?>

$(document).ready(function() {
    // Delete module/file/package
    $('a.delete_module, a.delete_file, a.delete_package').on('click', function(e) {
        e.preventDefault();
        var name = $(this).data('name');
        var href = $(this).attr('href');
        var lclass = $(this).attr('class');
        var title = '';
        if (lclass == 'delete_module') {
            title = '<?php echo $langs->trans('DeleteModule'); ?>';
        }
        else if (lclass == 'delete_file') {
            title = '<?php echo $langs->trans('DeleteFileFolder'); ?>';
        }
        else if (lclass == 'delete_package') {
            title = '<?php echo $langs->trans('DeletePackage'); ?>';
        }
        $('<div></div>').dialog({
            title: title,
            autoOpen: true,
            resizable: false,
            height: 200,
            width: 400,
            modal: true,
            closeOnEscape: false,
            open: function() {
                $(this).html('<?php echo img_help('', '').' '.$langs->trans('Delete'); ?>' + ' <strong>' + name + '</strong> ?');
                $(this).parent().find('button.ui-button:eq(2)').focus();
            },
            close: function() {
                $(this).dialog('destroy');
            },
            buttons: {
                "<?php echo $langs->transnoentities('Yes'); ?>": function() {
                    location.href = href;
                    $(this).dialog('close');
                },
                "<?php echo $langs->transnoentities('No'); ?>": function() {
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
            <form action="` + href + `" method="post">
                <input type="hidden" name="action" value="newfile" />
                <table class="paddingtopbottomonly allwidth">
                    <tr>
                        <td><?php echo $langs->trans('FileType'); ?></td>
                        <td><input type="radio" name="type" value="file" id="file_radio" class="valignmiddle" checked /> <label for="file_radio"><?php echo $langs->trans('File'); ?></label> <input type="radio" name="type" value="folder" id="folder_radio" class="valignmiddle" /> <label for="folder_radio"><?php echo $langs->trans('Folder'); ?></label></td>
                    </tr>
                    <tr>
                        <td><?php echo $langs->trans('FileName'); ?></td>
                        <td><input type="text" name="name" value="" required /></td>
                    </tr>
                </table>
                <input type="submit" style="display: none;" value="Submit" />
            </form>
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
                "<?php echo $langs->transnoentities('Create'); ?>": function() {
                    var form = $(this).find('form');
                    if (! form.checkValidity) {
                        form.find(':submit').click();
                    }
                    else {
                        form.submit();
                        $(this).dialog('close');
                    }
                },
                "<?php echo $langs->transnoentities('Cancel'); ?>": function() {
                    $(this).dialog('close');
                }
            }
        });
    });

    // Create new page
    $('a#new_page').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $(`<div>
            <br>
            <form action="` + href + `" method="post">
                <input type="hidden" name="action" value="newpage" />
                <table class="paddingtopbottomonly allwidth">
                    <tr>
                        <td><?php echo $langs->trans('PageName'); ?></td>
                        <td><input type="text" name="name" value="" placeholder="Index" required /></td>
                    </tr>
                </table>
                <input type="submit" style="display: none;" value="Submit" />
            </form>
        </div>`).dialog({
            title: '<?php echo $langs->trans('NewPage'); ?>',
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
                "<?php echo $langs->transnoentities('Create'); ?>": function() {
                    var form = $(this).find('form');
                    if (! form.checkValidity) {
                        form.find(':submit').click();
                    }
                    else {
                        form.submit();
                        $(this).dialog('close');
                    }
                },
                "<?php echo $langs->transnoentities('Cancel'); ?>": function() {
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
                "<?php echo $langs->transnoentities('Close'); ?>": function() {
                    $(this).dialog('close');
                }
            }
        });
    });

    // Add widget
    $('a#add_widget').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $(`<div>
            <form action="` + href + `" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="addwidget" />
                <table class="paddingtopbottomonly allwidth">
                    <tr>
                        <td><?php echo $langs->trans('WidgetName'); ?></td>
                        <td><input type="text" name="name" value="" placeholder="MyWidget" required /></td>
                    </tr>
                    <tr>
                        <td><?php echo $langs->trans('WidgetPicture'); ?></td>
                        <td><input type="file" name="picture" required /></td>
                    </tr>
                </table>
                <input type="submit" style="display: none;" value="Submit" />
            </form>
        </div>`).dialog({
            title: '<?php echo $langs->trans('AddWidget'); ?>',
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
                "<?php echo $langs->transnoentities('Create'); ?>": function() {
                    var form = $(this).find('form');
                    if (! form.checkValidity) {
                        form.find(':submit').click();
                    }
                    else {
                        form.submit();
                        $(this).dialog('close');
                    }
                },
                "<?php echo $langs->transnoentities('Cancel'); ?>": function() {
                    $(this).dialog('close');
                }
            }
        });
    });
});
