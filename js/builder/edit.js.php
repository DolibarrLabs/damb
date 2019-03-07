<?php

// Load Dolibarr environment
if (false === (@include '../../../main.inc.php')) {  // From htdocs directory
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
                $(this).parent().find("button.ui-button:eq(2)").focus();
            },
            resizable: false,
            height: 200,
            width: 400,
            modal: true,
            closeOnEscape: false,
            buttons: {
                "<?php echo $langs->trans('Yes'); ?>": function() {
                    location.href = href;
                    $(this).dialog("close");
                },
                "<?php echo $langs->trans('No'); ?>": function() {
                    $(this).dialog("close");
                }
            }
        });
    });
});
