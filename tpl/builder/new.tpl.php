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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

global $db, $langs;

$form = new Form($db);

?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />
    <input type="hidden" name="action" value="create" />

    <fieldset>
        <legend class="bold"><?php echo $langs->trans('ModuleConfiguration'); ?></legend>
        <div class="fichecenter">
            <div class="fichehalfleft">
                <table class="border allwidth">
                    <tr>
                        <td class="noborderbottom"><?php echo $langs->trans('ModuleName'); ?></td>
                        <td class="noborderbottom">
                            <input type="text" class="flat" name="name" placeholder="MyModule" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="noborderbottom"><?php echo $langs->trans('ModuleVersion').$form->textwithpicto(' ', $langs->trans('ModuleVersionHelp')); ?></td>
                        <td class="noborderbottom">
                            <input type="text" class="flat" name="version" placeholder="1.0.0" value="1.0.0" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="noborderbottom"><?php echo $langs->trans('ModuleNumber').$form->textwithpicto(' ', $langs->trans('ModuleNumberHelp')); ?></td>
                        <td class="noborderbottom">
                            <input type="number" class="flat" name="number" placeholder="500000" min="1" max="2147483647" value="<?php echo rand(500, 1000).'000'; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="noborderbottom"><?php echo $langs->trans('ModuleFamily'); ?></td>
                        <td class="noborderbottom">
                            <select class="flat" name="family">
                                <option value="hr"><?php echo $langs->trans('ModuleFamilyHr'); ?></option>
                                <option value="crm"><?php echo $langs->trans('ModuleFamilyCrm'); ?></option>
                                <option value="srm"><?php echo $langs->trans('ModuleFamilySrm'); ?></option>
                                <option value="financial"><?php echo $langs->trans('ModuleFamilyFinancial'); ?></option>
                                <option value="products"><?php echo $langs->trans('ModuleFamilyProducts'); ?></option>
                                <option value="projects"><?php echo $langs->trans('ModuleFamilyProjects'); ?></option>
                                <option value="ecm"><?php echo $langs->trans('ModuleFamilyECM'); ?></option>
                                <option value="technic"><?php echo $langs->trans('ModuleFamilyTechnic'); ?></option>
                                <option value="portal"><?php echo $langs->trans('ModuleFamilyPortal'); ?></option>
                                <option value="interface"><?php echo $langs->trans('ModuleFamilyInterface'); ?></option>
                                <option value="base"><?php echo $langs->trans('ModuleFamilyBase'); ?></option>
                                <option value="other" selected><?php echo $langs->trans('Other'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="noborderbottom"><?php echo $langs->trans('ModulePosition'); ?></td>
                        <td class="noborderbottom">
                            <input type="number" class="flat" name="position" placeholder="500" min="1" value="500" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="noborderbottom"><?php echo $langs->trans('ModuleRightsClass').$form->textwithpicto(' ', $langs->trans('ModuleRightsClassHelp')); ?></td>
                        <td class="noborderbottom">
                            <input type="text" class="flat" name="rights_class" placeholder="mymodule" required>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="fichehalfright">
                <div class="ficheaddleft">
                    <table class="border allwidth">
                        <tr>
                            <td class="noborderbottom"><?php echo $langs->trans('ModuleFolderName').$form->textwithpicto(' ', $langs->trans('ModuleFolderNameHelp')); ?></td>
                            <td class="noborderbottom">
                                <input type="text" class="flat" name="folder_name" placeholder="mymodule" required>
                            </td>
                        </tr>
                        <tr>
                            <td class="noborderbottom"><?php echo $langs->trans('ModulePicture').$form->textwithpicto(' ', $langs->trans('ModulePictureHelp')); ?></td>
                            <td class="noborderbottom">
                                <input type="file" accept="image/*" class="flat" name="picture" required>
                            </td>
                        </tr>
                        <tr>
                            <td class="noborderbottom" colspan="2">
                                <input type="checkbox" class="flat valignmiddle" name="add_changelog" id="add_changelog" value="1" checked>
                                <label for="add_changelog"><?php echo $langs->trans('AddChangelog').$form->textwithpicto(' ', $langs->trans('AddChangelogHelp')); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td class="noborderbottom" colspan="2">
                                <input type="checkbox" class="flat valignmiddle" name="add_extrafields" id="add_extrafields" value="1">
                                <label for="add_extrafields"><?php echo $langs->trans('AddExtrafields').$form->textwithpicto(' ', $langs->trans('AddExtrafieldsHelp')); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td class="noborderbottom" colspan="2">
                                <input type="checkbox" class="flat valignmiddle" name="add_num_models" id="add_num_models" value="1">
                                <label for="add_num_models"><?php echo $langs->trans('AddNumberingModels').$form->textwithpicto(' ', $langs->trans('AddNumberingModelsHelp')); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td class="noborderbottom" colspan="2">
                                <input type="checkbox" class="flat valignmiddle" name="add_doc_models" id="add_doc_models" value="1">
                                <label for="add_doc_models"><?php echo $langs->trans('AddDocumentModels').$form->textwithpicto(' ', $langs->trans('AddDocumentModelsHelp')); ?></label>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </fieldset>

    <br>

    <fieldset id="num_models_config" style="display: none;">
        <legend class="bold"><?php echo $langs->trans('NumberingModelsConfiguration'); ?></legend>
        <table class="border allwidth">
            <tr>
                <td class="noborderbottom"><?php echo $langs->trans('TableName').$form->textwithpicto(' ', $langs->trans('TableNameHelp')); ?></td>
                <td class="noborderbottom">
                    <input type="text" class="flat" name="num_models_table_name" placeholder="mysqltable" disabled required>
                </td>
            </tr>
            <tr>
                <td class="noborderbottom"><?php echo $langs->trans('TableFieldName').$form->textwithpicto(' ', $langs->trans('TableFieldNameHelp')); ?></td>
                <td class="noborderbottom">
                    <input type="text" class="flat" name="num_models_table_field" placeholder="ref" disabled required>
                </td>
            </tr>
            <tr>
                <td class="noborderbottom"><?php echo $langs->trans('NumModelPrefix').$form->textwithpicto(' ', $langs->trans('NumModelPrefixHelp')); ?></td>
                <td class="noborderbottom">
                    <input type="text" class="flat" name="num_models_prefix" placeholder="PR" disabled required>
                </td>
            </tr>
        </table>
    </fieldset>

    <div class="tabsAction" style="text-align: center;">
        <button type="submit" class="butAction"><?php echo $langs->trans('CreateModule'); ?></button>
    </div>
</form>
