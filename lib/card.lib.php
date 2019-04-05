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

// --------------------------------------------------------------------

if (! function_exists('print_create_form'))
{
    /**
     * Print create form
     *
     * @param  object $form    Form object instance
     * @param  array  $fields  Array of fields as [
     * 'my_field' => array(
     *     'label'            (*) => 'MyField',
     *     'type'             (*) => 'text', // possible values: 'text', 'number', 'range', 'price', 'date', 'select', 'multiselect', 'textarea', 'texteditor', 'file' or plain html
     *     'values'           (*) => array(0 => 'value 1', 1 => 'value 2'), // for type select & multiselect only
     *     'size'                 => 8, // useful for text inputs
     *     'min'                  => 0, // work with number & range inputs
     *     'max'                  => 100, // for number & range inputs too
     *     'summary'              => 'Field summary..',
     *     'enabled'              => '$conf->module->enabled' // condition to enable field
     * )]
     * array keys with (*) are required
     * @param  object $object Card object instance
     * @param  string $summary Form table summary text
     * @param  string $action_name Form action name
     */
    function print_create_form($form, $fields, $object, $summary = '', $action_name = 'add')
    {
        global $langs;

        echo '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
        echo '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
        echo '<input type="hidden" name="mainmenu" value="' . $_SESSION ['mainmenu'] . '">';
        echo '<input type="hidden" name="action" value="' . $action_name . '">';

        dol_fiche_head();

        // Print table summary
        if (! empty($summary)) {
            echo $langs->trans($summary);
        }

        // Print table
        echo '<table class="border" width="100%">';

        foreach ($fields as $name => $field)
        {
            if (! isset($field['enabled']) || empty($field['enabled']) || verifCond($field['enabled']))
            {
                $value = GETPOST($name);
                $validation_rules = isset($field['validation_rules']) && ! empty($field['validation_rules']) ? explode('|', $field['validation_rules']) : array();

                // Print field
                echo '<tr>';
                echo '<td width="25%"'.(in_array('required', $validation_rules) ? ' class="fieldrequired"' : '').'>'.$langs->trans($field['label']).'</td>';
                echo '<td>';

                // Ref
                if ($field['type'] == 'ref')
                {
                    echo $langs->trans('Draft');
                }

                // Text, number, range, price
                else if (in_array($field['type'], array('text', 'number', 'range', 'price')))
                {
                    $class = 'flat';
                    if ($field['type'] == 'range') {
                        $class .= ' valignmiddle';
                    }
                    $type = $field['type'] == 'price' ? 'text' : $field['type'];
                    echo '<input type="'.$type.'"'.(isset($field['min']) ? ' min="'.$field['min'].'"' : '').(isset($field['max']) ? ' max="'.$field['max'].'"' : '').(isset($field['size']) ? ' size="'.$field['size'].'"' : '').' class="'.$class.'" name="'.$name.'" value="'.$value.'">'."\n";
                }

                // Date
                else if ($field['type'] == 'date')
                {
                    $value = GETPOSTDATE($name);
                    echo $form->select_date($value, $name, 0, 0, 1, '', 1, 1, 1);
                }

                // Select
                else if ($field['type'] == 'select')
                {
                    echo $form->selectarray($name, $field['values'], $value, 0, 0, 0, '', 1);
                }

                // Multi select
                else if ($field['type'] == 'multiselect')
                {
                    if (! is_array($value)) {
                        $value = explode(',', $value);
                    }
                    echo '<input type="hidden" name="field_type" value="multiselect" />'."\n";
                    echo $form->multiselectarray($name, $field['values'], $value, 0, 0, '', 1, '60%');
                }

                // Text Area
                else if ($field['type'] == 'textarea')
                {
                    echo $form->textArea($name, $value);
                }

                // Text Editor
                else if ($field['type'] == 'texteditor')
                {
                    echo $form->textEditor($name, $value);
                }

                // File
                else if ($field['type'] == 'file')
                {
                    echo $form->fileInput($name);
                }

                // Something else
                else
                {
                    echo $field['type'];
                }

                // Print field summary
                if (isset($field['summary']) && ! empty($field['summary']))
                {
                    if ($field['summary'] == strip_tags($field['summary'])) {
                        $field_summary = $langs->trans($field['summary']);
                        echo $form->textwithpicto(' ', $field_summary);
                    }
                    else {
                        echo $field['summary'];
                    }
                }

                echo '</td></tr>';
            }
        }

        global $db, $hookmanager, $action;

        // fetch optionals attributes and labels
        $extrafields = new ExtraFields($db);
        $extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

        // show attributes
        $parameters = array();
        $reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action);
        echo $hookmanager->resPrint;
        if (empty($reshook) && ! empty($extrafields->attribute_label)) {
            echo $object->showOptionals($extrafields, 'edit');
        }

        echo '</table>';

        dol_fiche_end();

        echo '<div class="center">';
        echo '<input type="submit" class="button" value="'.$langs->trans('Create').'">';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '<input type="button" class="button" value="'.$langs->trans('Cancel').'" onClick="javascript:history.go(-1)">';
        //echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        //echo '<input type="reset" class="button" value="'.$langs->trans('Reset').'">';
        echo '</div>';

        echo '</form>';
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_banner'))
{
    /**
     * Print card banner
     *
     * @param     $object         object
     * @param     $list_link      link to list
     * @param     $morehtmlleft   more html in the left
     */
    function print_banner($object, $list_link = '', $morehtmlleft = '')
    {
        global $langs;

        $morehtml = (empty($list_link) ? '' : '<a href="'.dol_buildpath($list_link, 1).'">'.$langs->trans('BackToList').'</a>');

        dol_banner_tab($object, 'ref', $morehtml, 1, 'ref', 'ref', '', '', 0, $morehtmlleft);

        echo '<div class="underbanner clearboth"></div>';
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_card_table'))
{
    /**
     * Print card table
     *
     * @param  object $form    Form object instance
     * @param  array  $fields  Array of fields as [
     * 'my_field' => array(
     *     'label'            (*) => 'MyField',
     *     'type'             (*) => 'text', // possible values: 'text', 'number', 'range', 'price', 'date', 'select', 'multiselect', 'textarea', 'texteditor', 'file' or plain html
     *     'value'            (*) => $object->getNomUrl(1), // used only when type is plain html
     *     'values'           (*) => array(0 => 'value 1', 1 => 'value 2'), // for type select & multiselect only
     *     'size'                 => 8, // useful for text inputs
     *     'min'                  => 0, // work with number & range inputs
     *     'max'                  => 100, // for number & range inputs too
     *     'editable'             => true, // field is editable or not
     *     'enabled'              => '$conf->module->enabled' // condition to enable field
     * )]
     * array keys with (*) are required
     * @param  object $object Card object instance
     * @param  string $action Action, ex: GETPOST('action')
     * @param  boolean $allow_edit Allow fields edit
     */
    function print_card_table($form, $fields, $object, $action, $allow_edit = true)
    {
        global $langs;

        // Print table
        echo '<table class="border" width="100%">';

        foreach ($fields as $name => $field)
        {
            if ($field['type'] == 'ref')
            {
                continue; // ignore ref fields
            }
            else if (! isset($field['enabled']) || empty($field['enabled']) || verifCond($field['enabled']))
            {
                $value = $object->$name;
                $is_editable = $allow_edit && $action == 'edit_'.$name && (! isset($field['editable']) || $field['editable']);

                // Print field
                echo '<tr>';
                echo '<td width="25%"><table class="nobordernopadding" width="100%"><tr>';
                echo '<td>' . $langs->trans($field['label']) . '</td>';

                if ($allow_edit && isset($field['editable']) && $field['editable'] && $action != 'edit_'.$name) {
                    echo '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=edit_'.$name.'&id=' . $object->id . '">' . img_edit($langs->trans('Modify'), 1) . '</a></td>';
                }
                echo '</tr></table></td>';
                echo '<td colspan="5">';

                // Open edit form
                if ($is_editable) {
                    $action_prefix = $field['type'] == 'date' ? 'set_date_' : 'set_';
                    echo '<form action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
                    echo '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
                    echo '<input type="hidden" name="action" value="' . $action_prefix . $name . '">';
                }

                // Text, number, range, price
                if (in_array($field['type'], array('text', 'number', 'range', 'price')))
                {
                    if ($is_editable)
                    {
                        $class = 'flat';
                        if ($field['type'] == 'range') {
                            $class .= ' valignmiddle';
                        }
                        $type = $field['type'] == 'price' ? 'text' : $field['type'];
                        echo '<input type="'.$type.'"'.(isset($field['min']) ? ' min="'.$field['min'].'"' : '').(isset($field['max']) ? ' max="'.$field['max'].'"' : '').(isset($field['size']) ? ' size="'.$field['size'].'"' : '').' class="'.$class.'" name="'.$name.'" value="'.$value.'">'."\n";
                    }
                    else if ($field['type'] == 'price')
                    {
                        echo price_with_currency($value);
                    }
                    else
                    {
                        echo $value;
                    }
                }

                // Date
                else if ($field['type'] == 'date')
                {
                    if ($is_editable)
                    {
                        echo $form->select_date($value, $name, 0, 0, 1, '', 1, 1, 1);
                    }
                    else
                    {
                        echo dol_print_date($value, 'daytext');
                    }
                }

                // Select
                else if ($field['type'] == 'select')
                {
                    if ($is_editable)
                    {
                        echo $form->selectarray($name, $field['values'], $value, 0, 0, 0, '', 1);
                    }
                    else
                    {
                        echo $field['values'][$value];
                    }
                }

                // Multi select
                else if ($field['type'] == 'multiselect')
                {
                    if ($is_editable)
                    {
                        if (! is_array($value)) {
                            $value = explode(',', $value);
                        }
                        echo '<input type="hidden" name="field_type" value="multiselect" />'."\n";
                        echo $form->multiselectarray($name, $field['values'], $value, 0, 0, '', 1, '60%');
                    }
                    else
                    {
                        echo $field['values'][$value];
                    }
                }

                // Text Area
                else if ($field['type'] == 'textarea')
                {
                    if ($is_editable)
                    {
                        echo $form->textArea($name, $value);
                    }
                    else
                    {
                        echo $value;
                    }
                }

                // Text Editor
                else if ($field['type'] == 'texteditor')
                {
                    if ($is_editable)
                    {
                        echo $form->textEditor($name, $value);
                    }
                    else
                    {
                        echo $value;
                    }
                }

                // File
                else if ($field['type'] == 'file')
                {
                    if ($is_editable)
                    {
                        echo $form->fileInput($name);
                    }
                    else
                    {
                        echo $value;
                    }
                }

                // Something else
                else
                {
                    if ($is_editable)
                    {
                        echo $field['type'];
                    }
                    else
                    {
                        echo $field['value'];
                    }
                }

                // Close edit form
                if ($is_editable) {
                    echo ' <input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
                    echo '</form>';
                }

                echo '</td></tr>';
            }
        }

        global $db, $conf, $hookmanager, $user;

        // Fetch optionals attributes and labels
        $extrafields = new ExtraFields($db);
        $extralabels = $extrafields->fetch_name_optionals_label($object->table_element, true);
        $object->fetch_optionals($object->id, $extralabels);
        if (empty($user->rights->{$object->element}->create)) {
            @$user->rights->{$object->element}->create = $allow_edit; // hack to allow editing extrafields
        }

        include_once DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

        echo '</table>';
    }
}
