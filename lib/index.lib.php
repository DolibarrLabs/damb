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

// --------------------------------------------------------------------

if (! function_exists('print_search_form'))
{
    /**
     * Print a search form
     *
     * @param     $fields     an array of form fields, e.: array('Field 1' => 'field_name')
     * @param     $url        form url
     * @param     $title      form title
     * @param     $summary    form summary
     */
    function print_search_form($fields, $url, $title = 'Search', $summary = '')
    {
        global $db, $langs;

        require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

        $form = new Form($db);

        echo '<form method="post" action="'.dol_buildpath($url, 1).'">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        echo '<table class="noborder nohover" width="100%">';
        echo '<tr class="liste_titre"><td colspan="3">';

        $title = $langs->trans($title);
        if (empty($summary)) {
            echo $title;
        }
        else {
            $form->textwithpicto($title, $langs->trans($summary));
        }

        echo '</td></tr>';

        $count = 0;
        foreach ($fields as $key => $value)
        {
            $autofocus = ($count == 0 ? ' autofocus' : '');
            echo '<tr><td>'.$langs->trans($key);
            echo '</td><td><input type="text" class="flat inputsearch" name="'.$value.'" size="18"'.$autofocus.'></td>';
            if ($count == 0) {
                echo '<td class="noborderbottom" rowspan="'.count($fields).'"><input type="submit" value="'.$langs->trans('Search').'" class="button"></td>';
            }
            echo '</tr>';
            $count++;
        }

        echo "</table></form><br>\n";
    }
}
