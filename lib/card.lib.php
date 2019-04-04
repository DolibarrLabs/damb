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
     * @param  object $form    Form object
     * @param  array  $fields  Array of fields as ...
     * @param  string $summary Form table summary text
     */
    function print_create_form($form, $fields, $summary = '')
    {
        global $langs;

        echo '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
        echo '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
        echo '<input type="hidden" name="mainmenu" value="' . $_SESSION ['mainmenu'] . '">';
        echo '<input type="hidden" name="action" value="add">';

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
                // Print field
                echo '<tr>';
                echo '<td>'.$langs->trans($field['label']).'</td>';
                echo '<td>';

                // Ref
                if ($field['type'] == 'ref')
                {
                    echo $langs->trans('Draft');
                }
                // Text, number, range
                else if (in_array($field['type'], array('text', 'number', 'range')))
                {
                    $class = 'flat';
                    if ($field['type'] == 'range') {
                        $class .= ' valignmiddle';
                    }
                    echo '<input type="'.$field['type'].'"'.(isset($field['min']) ? ' min="'.$field['min'].'"' : '').(isset($field['max']) ? ' max="'.$field['max'].'"' : '').(isset($field['size']) ? ' size="'.$field['size'].'"' : '').' class="'.$class.'" name="'.$field['name'].'" value="'.$field['value'].'">'."\n";
                }
                // Date
                else if ($field['type'] == 'date')
                {
                    echo $form->select_date($field['value'], $field['name'], 0, 0, 1, '', 1, 1, 1);
                }
                // Select
                else if ($field['type'] == 'select')
                {
                    echo $form->selectarray($field['name'], $field['values'], $field['value'], 0, 0, 0, '', 1);
                }
                // Multi select
                else if ($field['type'] == 'multiselect')
                {
                    $value = is_array($field['value']) ? $field['value'] : explode(',', $field['value']);
                    echo '<input type="hidden" name="field_type" value="multiselect" />'."\n";
                    echo $form->multiselectarray($field['name'], $field['values'], $value, 0, 0, '', 1, '60%');
                }

                // Print field summary
                if (isset($field['summary']) && ! empty($field['summary'])) {
                    $field_summary = $langs->trans($field['summary']);
                    echo $form->textwithpicto(' ', $field_summary);
                }

                echo '</td></tr>';
            }
        }

        echo '</table>';

        echo '<div class="center">';
        echo '<input type="submit" class="button" value="'.$langs->trans('Create').'">';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '<input type="button" class="button" value="'.$langs->trans('Cancel').'" onClick="javascript:history.go(-1)">';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '<input type="reset" class="button" value="'.$langs->trans('Reset').'">';
        echo '</div>';

        echo '</form>';
    }
}
