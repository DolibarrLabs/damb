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
     *     'hidden'               => true, // or false
     *     'enabled'              => '$conf->module->enabled' // condition to enable field
     * )]
     * array keys with (*) are required
     * @param  object $object Card object instance
     * @param  string $action Action, ex: GETPOST('action')
     * @param  string $summary Form table summary text
     * @param  string $action_name Form action name
     */
    function print_create_form($form, $fields, $object, $action, $summary = '', $action_name = 'add')
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
        echo '<table class="border allwidth">';

        foreach ($fields as $name => $field)
        {
            if ((! isset($field['enabled']) || empty($field['enabled']) || verifCond($field['enabled'])) && ( ! isset($field['hidden']) || ! $field['hidden']))
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

        global $db, $hookmanager;

        // fetch optionals attributes and labels
        if (isset($object->extrafields)) {
            $extrafields = $object->extrafields;
        }
        else {
            $extrafields = new ExtraFields($db);
        }
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
     * @param     $morehtmlref    more html under the reference
     */
    function print_banner($object, $list_link = '', $morehtmlleft = '', $morehtmlref = '')
    {
        global $langs;

        $morehtml = (empty($list_link) ? '' : '<a href="'.dol_buildpath($list_link, 1).'">'.$langs->trans('BackToList').'</a>');

        dol_banner_tab($object, 'ref', $morehtml, 1, 'ref', 'ref', $morehtmlref, '', 0, $morehtmlleft);

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
     *     'hidden'               => true, // or false
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
        echo '<table class="border allwidth">';

        foreach ($fields as $name => $field)
        {
            if ((! isset($field['enabled']) || empty($field['enabled']) || verifCond($field['enabled'])) && ( ! isset($field['hidden']) || ! $field['hidden']))
            {
                $value = $object->$name;
                $is_editable = $allow_edit && $action == 'edit_'.$name && (! isset($field['editable']) || $field['editable']);

                // Print field
                echo '<tr>';
                echo '<td width="25%"><table class="nobordernopadding allwidth"><tr>';
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
                    if (! is_array($value)) {
                        $value = explode(',', $value);
                    }

                    if ($is_editable)
                    {
                        echo $form->multiselectarray($name, $field['values'], $value, 0, 0, '', 1, '60%');
                    }
                    else
                    {
                        foreach ($value as $val) {
                            echo $field['values'][$val].'<br>';
                        }
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
        if (isset($object->extrafields)) {
            $extrafields = $object->extrafields;
        }
        else {
            $extrafields = new ExtraFields($db);
        }
        $extralabels = $extrafields->fetch_name_optionals_label($object->table_element, true);
        $object->fetch_optionals($object->id, $extralabels);
        if (empty($user->rights->{$object->element}->create)) {
            @$user->rights->{$object->element}->create = $allow_edit; // hack to allow editing extrafields
        }

        include_once DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

        echo '</table>';
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_card_buttons'))
{
    /**
     * Print card buttons
     * 
     * @param  array $buttons array of buttons to show on card as [
     * array(
     *     'label'            (*) => 'MyButton',
     *     'title'                => 'MyButtonTitle',
     *     'href'                 => 'card.php?id=...',
     *     'class'                => 'butAction', // or 'butActionDelete'
     *     'id'                   => 'action-...', // used to display confirmation messages without reloading the page
     *     'target'               => '_self', // or '_blank' to open in a new window
     *     'enabled'              => true // true or false
     * )]
     * array keys with (*) are required
     */
    function print_card_buttons($buttons)
    {
        global $langs;

        dol_fiche_end();

        echo '<div class="tabsAction">';

        foreach ($buttons as $button)
        {
            if (! isset($button['enabled']) || $button['enabled'])
            {
                $title = isset($button['title']) && ! empty($button['title']) ? $langs->trans($button['title']) : '';
                $href = isset($button['href']) && ! empty($button['href']) ? $button['href'] : '#';
                $class = isset($button['class']) && ! empty($button['class']) ? $button['class'] : 'butAction';
                $target = isset($button['target']) && ! empty($button['target']) ? $button['target'] : '_self';

                if (js_enabled() && isset($button['id']) && ! empty($button['id'])) {
                    echo '<span class="'.$class.'" id="'.$button['id'].'" title="'.$title.'">'.$langs->trans($button['label']).'</span>';
                }
                else {
                    echo '<a class="'.$class.'" href="'.$href.'" target="'.$target.'" title="'.$title.'">'.$langs->trans($button['label']).'</a>';
                }
            }
        }

        echo '</div>';
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_mail_form'))
{
    /**
     * Print mail form
     * 
     * @param  object  $object                       Card object
     * @param  string  $mail_subject                 Mail subject
     * @param  string  $mail_template                Mail template
     * @param  array   $mail_substitutions           Mail substitutions array, ex: array('__REF__' => $object->ref)
     * @param  boolean $enable_mail_delivery_receipt Enable mail delivery receipt by default
     */
    function print_mail_form($object, $mail_subject = 'MailSubject', $mail_template = 'MailTemplate', $mail_substitutions = array(), $enable_mail_delivery_receipt = false)
    {
        if (is_object($object) && isset($object->id))
        {
            global $langs, $user, $conf;

            dol_fiche_end();

            echo '<div class="clearboth"></div><br>';
            echo load_fiche_titre($langs->trans('SendByMail'));
            dol_fiche_head();

            // Get receivers
            $receivers = array();
            if ($object->fetch_thirdparty() > 0)
            {
                foreach ($object->thirdparty->thirdparty_and_contact_email_array(1) as $key => $value) {
                    $receivers[$key] = $value;
                }
            }

            // Set substitutions
            $substitutions = array(
                '__REF__'       => $object->ref,
                '__SIGNATURE__' => $user->signature
            );

            // Add custom substitutions
            foreach ($mail_substitutions as $key => $value) {
                $substitutions[$key] = $value;
            }

            // Set additional parameters
            $params = array(
                'id'        => $object->id,
                'returnurl' => $_SERVER["PHP_SELF"] . '?id=' . $object->id
            );

            // Get file/attachment
            $ref = dol_sanitizeFileName($object->ref);
            require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
            $fileparams = dol_most_recent_file($conf->{$object->modulepart}->dir_output . '/' . $ref, preg_quote($ref, '/').'[^\-]+');
            $file = $fileparams['fullname'];

            // Show form
            $trackid  = $object->element.$object->id;
            $subject  = $langs->trans($mail_subject, '__REF__');
            $template = $langs->trans($mail_template);
            echo get_mail_form($trackid, $subject, $template, $substitutions, array($file), $enable_mail_delivery_receipt, $receivers, $params);

            dol_fiche_end();
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_documents'))
{
    /**
     * Print documents block
     * 
     * @param  object  $object     Card object
     * @param  boolean $genallowed Allow generation
     * @param  boolean $delallowed Allow deletion
     * @param  boolean $close_div  Close documents block HTML div
     */
    function print_documents($object, $genallowed = true, $delallowed = false, $close_div = true)
    {
        if (is_object($object) && isset($object->id))
        {
            global $db, $conf;
            
            require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
            
            $formfile = new FormFile($db);

            echo '<div class="fichecenter"><div class="fichehalfleft">';

            // Documents
            $ref = dol_sanitizeFileName($object->ref);
            $file = $conf->{$object->modulepart}->dir_output . '/' . $ref . '/' . $ref . '.pdf';
            $relativepath = $ref . '/' . $ref . '.pdf';
            $filedir = $conf->{$object->modulepart}->dir_output . '/' . $ref;
            $urlsource = $_SERVER['PHP_SELF'] . '?id=' . $object->id;
            if (empty($object->model_pdf)) {
                $modelselected = $conf->global->{$object->doc_model_const_name};
            }
            else {
                $modelselected = $object->model_pdf;
            }
            echo $formfile->showdocuments($object->modulepart, $ref, $filedir, $urlsource, $genallowed, $delallowed, $modelselected);

            echo '</div>';

            if ($close_div) {
                echo '</div>';
            }
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_events'))
{
    /**
     * Print events block
     * 
     * @param  object  $object      Card object
     * @param  string  $typeelement 'invoice','propal','order','invoice_supplier','order_supplier','fichinter'
     * @param  boolean $close_div   Close documents block HTML div
     */
    function print_events($object, $typeelement, $close_div = false)
    {
        if (is_object($object) && isset($object->id))
        {
            global $db, $user;

            echo '<div class="fichehalfright"><div class="ficheaddleft">';

            // List of actions on element
            include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
            $formactions = new FormActions($db);
            $socid = isset($user->societe_id) ? $user->societe_id : 0;
            $somethingshown = $formactions->showactions($object, $typeelement, $socid, 1);

            echo '</div></div>';

            if ($close_div) {
                echo '</div>';
            }
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_linked_objects'))
{
    function print_linked_objects($object, $form, $action, $allow_delete = false)
    {
        if (is_object($object) && isset($object->id) && (isset($object->socid) || isset($object->fk_soc)))
        {
            echo '<div class="fichecenter"><div class="fichehalfleft">';

            $permissiondellink = $allow_delete; // Used by the include of actions_dellink.inc.php
            $id = $object->id;

            include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php'; // Must be include, not include_once

            // Show links to link elements
            $linktoelem = $form->showLinkToObjectBlock($object);
            $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);

            echo '</div></div>';
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_card_lines'))
{
    /**
     * Print card lines
     * 
     * @param  object  $form               Form object instance
     * @param  array   $line_fields        Array of line fields as [
     * 'my_field' => array(
     *     'label'            (*) => 'MyField',
     *     'type'             (*) => 'text', // possible values: 'text', 'number', 'range', 'price', 'percentage', 'date', 'select', 'multiselect', 'textarea', 'texteditor', 'file' or plain html
     *     'value'            (*) => array(1 => $line->getNomUrl(1)), // used only when type is plain html or select
     *     'values'           (*) => array(0 => 'value 1', 1 => 'value 2'), // for type select & multiselect only
     *     'size'                 => 8, // useful for text inputs
     *     'min'                  => 0, // work with number & range inputs
     *     'max'                  => 100, // for number & range inputs too
     *     'editable'             => true, // field is editable or not
     *     'enabled'              => '$conf->module->enabled' // condition to enable field
     * )]
     * array keys with (*) are required
     * @param  object  $object             Card object instance
     * @param  string  $action             Action, ex: GETPOST('action')
     * @param  boolean $allow_edit         Allow lines edition
     * @param  boolean $allow_delete       Allow lines deletion
     * @param  string  $update_action_name Update action name
     * @param  string  $delete_action_name Delete action name
     * @param  string  $edit_action_name   Edit action name
     */
    function print_card_lines($form, $line_fields, $object, $action, $allow_edit = true, $allow_delete = false, $update_action_name = 'updateline', $delete_action_name = 'deleteline', $edit_action_name = 'editline')
    {
        global $langs;

        if (is_array($object->lines) && ! empty($object->lines))
        {
            echo '<br>';
            echo '<table class="noborder noshadow allwidth">';

            // Print table header
            echo '<tr class="liste_titre">';

            foreach ($line_fields as $name => $field) {
                echo '<td>'.$langs->trans($field['label']).'</td>';
            }

            echo '<td></td>';
            echo '</tr>';

            // Print lines
            foreach ($object->lines as $line)
            {
                $line_id = GETPOST('lineid', 'int');
                $is_editable = $allow_edit && $action == $edit_action_name && $line->id == $line_id;

                echo '<tr class="oddeven">';

                // Open edit form
                if ($is_editable) {
                    echo '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
                    echo '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
                    echo '<input type="hidden" name="mainmenu" value="' . $_SESSION ['mainmenu'] . '">';
                    echo '<input type="hidden" name="action" value="' . $update_action_name . '">';
                    echo '<input type="hidden" name="id" value="' . $object->id . '">';
                    echo '<input type="hidden" name="lineid" value="' . $line_id . '">';
                }

                foreach ($line_fields as $name => $field)
                {
                    echo '<td>';

                    $value = $line->$name;

                    // Text, number, range, price, percentage
                    if (in_array($field['type'], array('text', 'number', 'range', 'price', 'percentage')))
                    {
                        if ($is_editable)
                        {
                            $class = 'flat';
                            if ($field['type'] == 'range') {
                                $class .= ' valignmiddle';
                            }
                            $type = in_array($field['type'], array('price', 'percentage')) ? 'text' : $field['type'];
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

                        if ($field['type'] == 'percentage') {
                            echo '%';
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
                            $key = isset($field['value']) && ! empty($field['value']) ? 'value' : 'values';
                            echo $field[$key][$value];
                        }
                    }

                    // Multi select
                    else if ($field['type'] == 'multiselect')
                    {
                        if (! is_array($value)) {
                            $value = explode(',', $value);
                        }

                        if ($is_editable)
                        {
                            echo $form->multiselectarray($name, $field['values'], $value, 0, 0, '', 1, '60%');
                        }
                        else
                        {
                            foreach ($value as $val) {
                                echo $field['values'][$val].'<br>';
                            }
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
                            echo $field['value'][$value];
                        }
                    }

                    echo '</td>';
                }

                // Close edit form
                if ($is_editable)
                {
                    echo '<td align="right">';
                    echo '<input type="submit" class="button" value="'.$langs->trans('Modify').'">';
                    echo '<a class="button" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">'.$langs->trans('Cancel').'</a>';
                    echo '</td>';

                    echo '</form>';
                }
                else
                {
                    // Print edit & delete links
                    echo '<td align="right">';

                    if ($allow_edit) {
                        echo '<a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action='.$edit_action_name.'&lineid='.$line->id.'">'.img_edit().'</a>';
                    }

                    if ($allow_delete) {
                        echo '<a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action='.$delete_action_name.'&lineid='.$line->id.'">'.img_delete().'</a>';
                    }

                    echo '</td>';
                }

                echo '</tr>';
            }

            echo '</table>';
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_add_line_form'))
{
    /**
     * Print add line form
     * 
     * @param  object $form        Form object instance
     * @param  array  $line_fields Array of line fields as [
     * 'my_field' => array(
     *     'label'            (*) => 'MyField',
     *     'type'             (*) => 'text', // possible values: 'text', 'number', 'range', 'price', 'percentage', 'date', 'select', 'multiselect', 'textarea', 'texteditor', 'file' or plain html
     *     'values'           (*) => array(0 => 'value 1', 1 => 'value 2'), // for type select & multiselect only
     *     'size'                 => 8, // useful for text inputs
     *     'min'                  => 0, // work with number & range inputs
     *     'max'                  => 100, // for number & range inputs too
     *     'editable'             => true, // field is editable or not
     *     'enabled'              => '$conf->module->enabled' // condition to enable field
     * )]
     * array keys with (*) are required
     * @param  object $object      Card object instance
     * @param  string $title       Form table title
     * @param  string $action_name Form action name
     */
    function print_add_line_form($form, $line_fields, $object, $title, $action_name = 'addline')
    {
        global $langs;

        echo '<br>';

        echo '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
        echo '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
        echo '<input type="hidden" name="mainmenu" value="' . $_SESSION ['mainmenu'] . '">';
        echo '<input type="hidden" name="action" value="' . $action_name . '">';
        echo '<input type="hidden" name="id" value="' . $object->id . '">';

        echo '<table id="tablelines" class="noborder noshadow allwidth">';

        // Print table header
        echo '<tr class="liste_titre">';
        echo '<td>'.$langs->trans($title).'</td>';

        $line_fields_shifted = $line_fields;
        array_shift($line_fields_shifted); // remove the first element of the array (replaced by $title)
        foreach ($line_fields_shifted as $name => $field) {
            echo '<td>'.$langs->trans($field['label']).'</td>';
        }

        echo '<td></td>';
        echo '</tr>';

        // Print line fields
        echo '<tr>';

        foreach ($line_fields as $name => $field)
        {
            if (! isset($field['enabled']) || empty($field['enabled']) || verifCond($field['enabled']))
            {
                $value = GETPOST($name);

                echo '<td>';

                // Text, number, range, price, percentage
                if (in_array($field['type'], array('text', 'number', 'range', 'price', 'percentage')))
                {
                    $class = 'flat';
                    if ($field['type'] == 'range') {
                        $class .= ' valignmiddle';
                    }
                    $type = in_array($field['type'], array('price', 'percentage')) ? 'text' : $field['type'];
                    echo '<input type="'.$type.'"'.(isset($field['min']) ? ' min="'.$field['min'].'"' : '').(isset($field['max']) ? ' max="'.$field['max'].'"' : '').(isset($field['size']) ? ' size="'.$field['size'].'"' : '').' class="'.$class.'" name="'.$name.'" value="'.$value.'">'."\n";
                    if ($field['type'] == 'percentage') {
                        echo '%';
                    }
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

                echo '</td>';
            }
        }

        echo '<td align="center">';
        echo '<input type="submit" class="button" value="'.$langs->trans('Add').'">';
        echo '</td>';

        echo '</tr>';
        echo '</table>';
        echo '</form>';
    }
}
