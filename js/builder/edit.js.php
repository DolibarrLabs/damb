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
    $('.delete_module').on('click', function(e) {
        if (! confirm('<?php echo $langs->trans('ConfirmDeleteModule'); ?>')) {
            e.preventDefault();
        }
    });
});
