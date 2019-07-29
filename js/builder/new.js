
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

$(document).ready(function() {
    $('input[name="name"]').on('keyup input', function() {
        var name = $(this).val();
        var sanitizedName = name.replace(/\s/g, '').toLowerCase();
        $('input[name="rights_class"]').val(sanitizedName);
        $('input[name="folder_name"]').val(sanitizedName.replace(/_/g, ''));
    });

    $('#add_num_models').on('change', function() {
        if ($(this).is(':checked')) {
            $('#num_models_config').show();
            $('input[name^="num_models"]').removeAttr('disabled');
        }
        else {
            $('#num_models_config').hide();
            $('input[name^="num_models"]').removeAttr('disabled').attr('disabled', true);
        }
    });
});
