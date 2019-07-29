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

if (! function_exists('get_list_limit'))
{
    /**
     * Return list limit
     *
     * @return int list limit
     */
    function get_list_limit()
    {
        global $conf;

        return $conf->liste_limit;
    }
}

// --------------------------------------------------------------------

if (! function_exists('init_list_hooks'))
{
    /**
     * Initialize list hooks
     * 
     * @param  string $contextpage page context
     */
    function init_list_hooks($contextpage)
    {
        global $hookmanager;

        // Initialize technical object to manage hooks of thirdparties.
        $hookmanager->initHooks(array($contextpage));
    }
}

// --------------------------------------------------------------------

if (! function_exists('load_default_actions'))
{
    /**
     * Load default list actions
     * 
     * @param  string $contextpage page context
     */
    function load_default_actions($contextpage)
    {
        // Selection of new fields
        global $db, $conf, $user;
        include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

        // Purge search criteria
        if (GETPOST('button_removefilter_x') || GETPOST('button_removefilter')) // Both test are required to be compatible with all browsers
        {
            $_POST = array();
            $_GET  = array();
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_list_head'))
{
    /**
     * Open list / print list head
     *
     * @param   $title               List title
     * @param   $picture             List picture
     * @param   $object              List object instance
     * @param   $form                Form object instance
     * @param   $contextpage         Page context
     * @param   $list_fields         List fields
     * @param   $search_fields       List search fields
     * @param   $nbofshownrecords    Number of shown records
     * @param   $nbtotalofrecords    Total number of records
     * @param   $fieldstosearchall   Fields to search all
     * @param   $sortfield           Sort field
     * @param   $sortorder           Sort order
     * @param   $morehtmlright       More HTML to show on the right of the list title
     * @return  array                array fields
     */
    function print_list_head($title, $picture = 'title_generic.png', $object, $form, $contextpage, $list_fields, $search_fields, $nbofshownrecords, $nbtotalofrecords, $fieldstosearchall = array(), $sortfield = '', $sortorder = '', $morehtmlright = '')
    {
        global $db, $langs, $conf;

        // Get parameters
        $sall = isset($search_fields['all']) ? $search_fields['all'] : GETPOST('sall', 'alphanohtml');
        $page = GETPOST('page', 'int') ? GETPOST('page', 'int') : 0;
        if (empty($sortorder)) $sortorder = GETPOST('sortorder', 'alpha');
        if (empty($sortfield)) $sortfield = GETPOST('sortfield', 'alpha');
        $limit = GETPOST('limit') ? GETPOST('limit', 'int') : $conf->liste_limit;
        $optioncss = GETPOST('optioncss', 'alpha');

        // List form
        echo '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        echo '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
        echo '<input type="hidden" name="action" value="list">';
        echo '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
        echo '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

        // Add list parameters
        $param = '';
        foreach ($search_fields as $key => $value) {
            if ($value != '') $param.= '&'.$key.'='.urlencode($value);
        }
        if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param.= '&contextpage='.urlencode($contextpage);
        if ($limit > 0 && $limit != $conf->liste_limit) $param.= '&limit='.urlencode($limit);
        if ($optioncss != '') $param.= '&optioncss='.urlencode($optioncss);
        // Loop to complete $param for extrafields
        if (isset($object->extrafields)) {
            $extrafields = $object->extrafields;
        }
        else {
            $extrafields = new ExtraFields($db);
        }
        $extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
        $search_array_options = $extrafields->getOptionalsFromPost($extralabels, '', 'search_');
        foreach ($search_array_options as $key => $val)
        {
            $tmpkey = preg_replace('/search_options_/', '', $key);
            if ($val != '') $param.= '&search_options_'.$tmpkey.'='.urlencode($val);
        }

        // List title
        $title = $langs->trans($title);
        print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $nbofshownrecords, $nbtotalofrecords, $picture, 0, $morehtmlright, '', $limit);

        if ($sall)
        {
            foreach($fieldstosearchall as $key => $val) {
                $fieldstosearchall[$key] = $langs->trans($val);
            }
            echo $langs->trans('FilterOnInto', $sall) . join(', ',$fieldstosearchall);
        }

        echo '<div class="div-table-responsive">';
        echo '<table class="tagtable liste">'."\n";

        // Generate $arrayfields
        $arrayfields = array();
        foreach ($list_fields as $field) {
            $checked = (isset($field['checked']) ? $field['checked'] : 1);
            $enabled = (isset($field['enabled']) ? verifCond($field['enabled']) : 1);
            $arrayfields[$field['name']] = array('label' => $field['label'], 'checked' => $checked, 'enabled' => $enabled);
        }
        // Extra fields
        if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
        {
            foreach($extrafields->attribute_label as $key => $val)
            {
                $arrayfields['ef.'.$key] = array('label' => $extrafields->attribute_label[$key], 'checked' => (($extrafields->attribute_list[$key]<0)?0:1), 'position' => $extrafields->attribute_pos[$key], 'enabled' => $extrafields->attribute_perms[$key]);
            }
        }
        // This change content of $arrayfields
        $varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
        $selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);
        $old_dolibarr = function_exists('version_compare') && version_compare(DOL_VERSION, '6.0.0') < 0;

        // List fields
        echo '<tr class="liste_titre">';
        foreach ($list_fields as $field) {
            if (! empty($arrayfields[$field['name']]['checked'])) {
                $field_align = (isset($field['align']) ? 'align="'.$field['align'].'"' : '');
                $field_class = (isset($field['class']) ? $field['class'].' ' : '');
                $label = $old_dolibarr ? $langs->trans($field['label']) : $field['label'];
                print_liste_field_titre($label, $_SERVER["PHP_SELF"], $field['name'], '', $param, $field_align, $sortfield, $sortorder, $field_class);
            }
        }
        // Loop to show all columns of extrafields for the title line
        if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
        {
            foreach($extrafields->attribute_label as $key => $val)
            {
                if (! empty($arrayfields["ef.".$key]['checked']))
                {
                    $align = $extrafields->getAlignFlag($key);
                    $sortonfield = "ef.".$key;
                    if (! empty($extrafields->attribute_computed[$key])) $sortonfield = '';
                    $label = $old_dolibarr ? $langs->trans($extralabels[$key]) : $extralabels[$key];
                    echo getTitleFieldOfList($label, 0, $_SERVER["PHP_SELF"], $sortonfield, "", $param, ($align?'align="'.$align.'"':''), $sortfield, $sortorder)."\n";
                }
            }
        }
        print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], '', '', '', 'align="right"', $sortfield, $sortorder, 'maxwidthsearch ');
        echo "</tr>\n";

        // List search fields
        if ($optioncss != 'print')
        {
            echo '<tr class="liste_titre liste_titre_filter">';
            foreach ($list_fields as $field) {
                if (! empty($arrayfields[$field['name']]['checked'])) {
                    $field_align = (isset($field['align']) ? ' align="'.$field['align'].'"' : '');
                    $field_class = (isset($field['class']) ? ' '.$field['class'] : '');
                    $search_input = (isset($field['search_input']) ? $field['search_input'] : '');
                    echo '<td class="liste_titre'.$field_class.'"'.$field_align.'>'.$search_input.'</td>';
                }
            }
            // Loop to show all columns of extrafields for the search title line
            if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
            {
                foreach($extrafields->attribute_label as $key => $val)
                {
                    if (! empty($arrayfields['ef.'.$key]['checked']))
                    {
                        $align = $extrafields->getAlignFlag($key);
                        $typeofextrafield = $extrafields->attribute_type[$key];
                        echo '<td class="liste_titre'.($align?' '.$align:'').'">';
                        if (in_array($typeofextrafield, array('varchar', 'int', 'double', 'select')) && empty($extrafields->attribute_computed[$key]))
                        {
                            $tmpkey = preg_replace('/search_options_/', '', $key);
                            $searchclass = '';
                            if (in_array($typeofextrafield, array('varchar', 'select'))) {
                                $searchclass = 'searchstring';
                            }
                            else if (in_array($typeofextrafield, array('int', 'double'))) {
                                $searchclass = 'searchnum';
                            }

                            if ($typeofextrafield == 'select') {
                                echo $form->listInput('search_options_'.$tmpkey, $extrafields->attribute_param[$tmpkey]['options'], dol_escape_htmltag($search_array_options['search_options_'.$tmpkey]), 1);
                            }
                            else {
                                echo '<input class="flat'.($searchclass?' '.$searchclass:'').'" size="4" type="text" name="search_options_'.$tmpkey.'" value="'.dol_escape_htmltag($search_array_options['search_options_'.$tmpkey]).'">';
                            }
                        }
                        /*else if (in_array($typeofextrafield, array('datetime', 'timestamp')))
                        {
                            // TODO
                            // Use showInputField in a particular manner to have input with a comparison operator, not input for a specific value date-hour-minutes
                        }*/
                        else
                        {
                            // for the type as 'checkbox', 'chkbxlst', 'sellist' we should use code instead of id (example: I declare a 'chkbxlst' to have a link with dictionnairy, I have to extend it with the 'code' instead of 'rowid')
                            $morecss = '';
                            if ($typeofextrafield == 'sellist') {
                                $morecss = 'maxwidth200';
                            }
                            echo $extrafields->showInputField($key, $search_array_options['search_options_'.$key], '', '', 'search_', $morecss);
                        }
                        echo '</td>';
                    }
                }
            }
            // search buttons
            echo '<td class="liste_titre" align="right"><input type="image" class="liste_titre" name="button_search" src="'.img_picto($langs->trans('Search'),'search.png','','',1).'" value="'.dol_escape_htmltag($langs->trans('Search')).'" title="'.dol_escape_htmltag($langs->trans('Search')).'">';
            echo '<input type="image" class="liste_titre" name="button_removefilter" src="'.img_picto($langs->trans('Search'),'searchclear.png','','',1).'" value="'.dol_escape_htmltag($langs->trans('RemoveFilter')).'" title="'.dol_escape_htmltag($langs->trans('RemoveFilter')).'">';
            echo "</td></tr>\n";
        }

        return $arrayfields;
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_list_extrafields'))
{
    /**
     * Print extrafields columns
     *
     * @param   $obj           object
     * @param   $arrayfields   array of fields as [
     * 'ef.field' => array(
     *     'checked' => true
     * )]
     */
    function print_list_extrafields($obj, $arrayfields)
    {
        global $db;

        if (isset($obj->extrafields)) {
            $extrafields = $obj->extrafields;
        }
        else {
            $extrafields = new ExtraFields($db);
        }
        $extralabels = $extrafields->fetch_name_optionals_label($obj->table_element);

        if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
        {
            foreach($extrafields->attribute_label as $key => $val)
            {
                if (! empty($arrayfields['ef.'.$key]['checked']))
                {
                    $align = $extrafields->getAlignFlag($key);
                    echo '<td';
                    if ($align) echo ' align="'.$align.'"';
                    echo '>';
                    $tmpkey = 'options_'.$key;
                    if (in_array($extrafields->attribute_type[$key], array('date', 'datetime', 'timestamp')))
                    {
                        $value = $db->jdate($obj->$tmpkey);
                    }
                    else
                    {
                        $value = $obj->$tmpkey;
                    }
                    echo $extrafields->showOutputField($key, $value, '');
                    echo '</td>';
                }
            }
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_list_end'))
{
    /**
     * Close list / print list end
     *
     * @param   $buttons                   buttons array to add before close list as [
     * array(
     *     'label'            (*) => 'MyButton',
     *     'name'             (*) => 'my_action',
     *     'enabled'              => true // true or false
     * )]
     * array keys with (*) are required
     * @param   $hide_buttons_by_default   hide buttons by default
     */
    function print_list_end($buttons = array(), $hide_buttons_by_default = false)
    {
        echo "</table>\n";

        $optioncss = GETPOST('optioncss', 'alpha');

        if (! empty($buttons) && $optioncss != 'print')
        {
            global $langs;

            echo '<div class="tabsAction'.($hide_buttons_by_default ? ' hidden' : '').'">';

            foreach ($buttons as $button)
            {
                if (! isset($button['enabled']) || verifCond($button['enabled'])) {
                    echo '<input type="submit" class="butAction" name="'.$button['name'].'" value="'.$langs->trans($button['label']).'">';
                }
            }

            echo '</div>';
        }

        echo "</div></form>\n";
    }
}
