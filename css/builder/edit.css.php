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

header('Content-Type: text/css');

?>

.ecmjqft li.file {
    background: url(<?php echo DOL_URL_ROOT.'/theme/common/mime/text.png'; ?>) left top no-repeat;
}
