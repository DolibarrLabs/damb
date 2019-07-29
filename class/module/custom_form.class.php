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

require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';

/**
 * CustomForm class
 */

class CustomForm extends Form
{
    /**
     * @var object used to call Dolibarr more form functions like: color picker
     */
    public $other;


    /**
     * Constructor
     *
     */
    public function __construct()
    {
        global $db;

        $this->db = $db;
        $this->other = new FormOther($db);
    }

    /**
     * Return a checkbox
     *
     * @param   $name     checkbox name
     * @param   $value    checkbox value
     * @param   $id       checkbox id
     * @param   $class    checkbox class
     * @param   $checked  checkbox is checked or not
     * @param   $disabled checkbox is disabled or not
     * @return  string    checkbox HTML
     */
    public function checkBox($name, $value = '', $id = '', $class = '', $checked = false, $disabled = false)
    {
        return '<input type="checkbox" class="flat'.(! empty($class) ? ' '.$class : '').'" name="'.$name.'" id="'.$id.'" value="'.$value.'"'.($checked ? ' checked' : '').($disabled ? ' disabled' : '').'>';
    }

    /**
     * Return a text input
     *
     * @param   $name    input name
     * @param   $value   input value
     * @param   $size    input size
     * @return  string   input HTML
     */
    public function textInput($name, $value, $size = 8)
    {
        return '<input type="text" class="flat" name="'.$name.'" value="'.$value.'" size="'.$size.'">';
    }

    /**
     * Return a text area
     *
     * @param   $name           text area name
     * @param   $value          text area value
     * @param   $rows           text area rows
     * @return  string          text area HTML
     */
    public function textArea($name, $value, $rows = '3')
    {
        return '<textarea name="'.$name.'" class="flat centpercent" rows="'.$rows.'">'.$value.'</textarea>';
    }

    /**
     * Return a text area with editor (if WYSIWYG editor module is activated)
     *
     * @param   $name           text area name
     * @param   $value          text area value
     * @param   $toolbarname    toolbar name, possible values: 'dolibarr_details', 'dolibarr_readonly', 'dolibarr_notes', 'dolibarr_mailings'
     * @param   $height         text area height
     * @return  string          text area HTML
     */
    public function textEditor($name, $value, $toolbarname = 'dolibarr_details', $height = 100)
    {
        global $conf;

        if (! empty($conf->global->FCKEDITOR_ENABLE_DETAILS_FULL)) $toolbarname = 'Full';
        else if (empty($toolbarname)) $toolbarname = 'dolibarr_details';
        $doleditor = new DolEditor($name, $value, '', $height, $toolbarname, 'In', false, false, true, ROWS_3, '90%');

        return $doleditor->Create(1);
    }

    /**
     * Return a file input
     *
     * @since   2.9.5
     * @param   $name    input name
     * @param   $accept  input accept attribute
     * @return  string   input HTML
     */
    public function fileInput($name, $accept = '')
    {
        return '<input type="file" class="flat" name="'.$name.'"'.(! empty($accept) ? ' accept="'.$accept.'"' : '').'>';
    }

    /**
     * Return a number input
     *
     * @param   $name    input name
     * @param   $value   input value
     * @param   $min     input minimum number
     * @param   $max     input maximum number
     * @return  string   input HTML
     */
    public function numberInput($name, $value, $min = 0, $max = 100)
    {
        return '<input type="number" min="'.$min.'" max="'.$max.'" class="flat" name="'.$name.'" value="'.$value.'">';
    }

    /**
     * Return a range input
     *
     * @param   $name    input name
     * @param   $value   input value
     * @param   $min     input minimum value
     * @param   $max     input maximum value
     * @return  string   input HTML
     */
    public function rangeInput($name, $value, $min = 0, $max = 100)
    {
        return '<input type="range" min="'.$min.'" max="'.$max.'" class="flat valignmiddle" name="'.$name.'" value="'.$value.'">';
    }

    /**
     * Return a date input
     *
     * @param   $name         input name
     * @param   $value        input value
     * @param   $addnowlink   add now link
     * @return  string        input HTML
     */
    public function dateInput($name, $value, $addnowlink = true)
    {
        return $this->select_date($value, $name, 0, 0, 1, '', 1, $addnowlink, 1);
    }

    /**
     * Return a datetime input
     *
     * @since   2.9.4
     * @param   $name         input name
     * @param   $value        input value
     * @param   $addnowlink   add now link
     * @return  string        input HTML
     */
    public function datetimeInput($name, $value, $addnowlink = true)
    {
        return $this->select_date($value, $name, 1, 1, 1, '', 1, $addnowlink, 1);
    }

    /**
     * Return a list
     *
     * @param   $name       list name
     * @param   $values     list values
     * @param   $selected   list selected value
     * @param   $show_empty show empty value, 0 no empty value allowed, 1 or string to add an empty value into list (key is -1 and value is '' or '&nbsp;' if 1, key is -1 and value is text if string), <0 to add an empty value with key that is this value.
     * @param   $translate  translate values
     * @return  string      list HTML
     */
    public function listInput($name, $values, $selected, $show_empty = 0, $translate = true)
    {
        return $this->selectarray($name, $values, $selected, $show_empty, 0, 0, '', $translate);
    }

    /**
     * Return a multi select list
     *
     * @since   2.9.5
     * @param   $name       list name
     * @param   $values     list values
     * @param   $selected   list selected value
     * @param   $translate  translate values
     * @param   $width      list width
     * @return  string      list HTML
     */
    public function multiSelectListInput($name, $values, $selected, $translate = true, $width = '100%')
    {
        return $this->multiselectarray($name, $values, $selected, 0, 0, '', $translate, $width);
    }

    /**
     * Return a radio list
     *
     * @param   $name      list name
     * @param   $values    list values
     * @param   $selected  list selected value
     * @return  string     list HTML
     */
    public function radioList($name, $values, $selected)
    {
        global $langs;

        $count = 0;
        $out = '';
        foreach ($values as $val => $label) {
            $content = '';
            if (is_array($label)) {
                $val = $label['value'];
                $content = $label['content'];
                $label = $label['label'];
            }
            $out.= '<div class="minheight20" style="padding-bottom: 5px;">';
            $out.= '<span>';
            $out.= '<input type="radio" class="valignmiddle" name="'.$name.'" id="'.$name.'-'.$val.'" value="'.$val.'"'.($selected == $val || ($count == 0 && empty($selected)) ? ' checked' : '').'>';
            $out.= ' <label class="valignmiddle" for="'.$name.'-'.$val.'">' . $langs->trans($label) . '</label>';
            $out.= '</span>';
            $out.= $content;
            $out.= '</div>';
            $count++;
        }

        return $out;
    }

    /**
     * Return a check list
     *
     * @param   $name      list name
     * @param   $values    list values
     * @param   $selected  list selected value(s)
     * @return  string     list HTML
     */
    public function checkList($name, $values, $selected)
    {
        global $langs;

        $out = '';
        foreach ($values as $val => $label) {
            $content = '';
            if (is_array($label)) {
                $val = $label['value'];
                $content = $label['content'];
                $label = $label['label'];
            }
            $out.= '<div class="minheight20" style="padding-bottom: 5px;">';
            $out.= '<span>';
            $out.= '<input type="checkbox" class="valignmiddle" name="'.$name.'[]" id="'.$name.'-'.$val.'" value="'.$val.'"'.(! empty($selected) && in_array($val, $selected) ? ' checked' : '').'>';
            $out.= ' <label class="valignmiddle" for="'.$name.'-'.$val.'">' . $langs->trans($label) . '</label>';
            $out.= '</span>';
            $out.= $content;
            $out.= '</div>';
        }

        return $out;
    }

    /**
     * Return a color input
     *
     * @param   $name    input name
     * @param   $value   input value
     * @return  string   input HTML
     */
    public function colorInput($name, $value)
    {
        return $this->other->selectColor(colorArrayToHex(colorStringToArray($value, array()), ''), $name, 'formcolor', 1);
    }

    /**
     * Return products list
     *
     * @param   $name         list name
     * @param   $value        list value
     * @param   $show_empty   show empty line or not, '1' if yes '' if no, 'Your text' if you wanna show some text
     * @param   $filter       filter on product type (''=nofilter, 0=product, 1=service)
     * @param   $morecss      add more css on select
     * @return  string        list HTML
     */
    public function productList($name, $value, $show_empty = '1', $filter = '', $morecss = '')
    {
        global $conf;

        // To know: select_produits() have no option to return the output instead of print it, so the only way is this
        ob_start();
        $this->select_produits($value, $name, $filter, $conf->product->limit_size, 0, 1, 2, '', 1, array(), 0, $show_empty, 0, $morecss, 1);
        $out = ob_get_contents();
        ob_end_clean();

        return $out;
    }

    /**
     * Print a confirmation message
     *
     * @param     $url                Page url
     * @param     $title              Message title
     * @param     $question           Message question / content
     * @param     $action             Action to do after confirmation
     * @param     $question_param     Question parameter
     * @param     $dialog_id_suffix   Dialog id suffix (used to show the dialog without reloading the page)
     */
    public function printConfirm($url, $title, $question, $action, $question_param = '', $dialog_id_suffix = '')
    {
        global $langs;

        $use_ajax = (empty($dialog_id_suffix) ? 0 : $dialog_id_suffix);

        echo $this->formconfirm($url, $langs->trans($title), $langs->trans($question, $question_param), $action, '', '', $use_ajax);
    }
}
