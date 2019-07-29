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

if (! function_exists('array_match'))
{
    /**
     * Loops on an array values & check if a value matches the pattern using preg_match
     *
     * @param   string   $pattern   Regular expression pattern
     * @param   array    $array     Array to check
     * @param   array    $matches   Pattern matches
     * @return  int|bool            1 if the pattern matches given array, 0 if it does not, or FALSE if an error occurred.
     */
    function array_match($pattern, $array, &$matches)
    {
        foreach ($array as $value)
        {
            $result = preg_match($pattern, $value, $matches);
            if ($result) {
                return $result;
            }
        }

        return 0;
    }
}

// --------------------------------------------------------------------

if (! function_exists('validate_field'))
{
    /**
     * Check/validate specified field
     *
     * @param   string    $field_name               field name
     * @param   string    $field_label              field label
     * @param   string    $field_validation_rules   field validatin rules
     * @param   boolean   $return_error_number      return errors number or boolean value
     * @return  boolean|int                         true/false | errors number
     */
    function validate_field($field_name, $field_label, $field_validation_rules, $return_error_number = false)
    {
        global $langs;

        $langs->load('errors');

        $error = 0;
        $field_value = GETPOST($field_name);
        $validation_rules = explode('|', $field_validation_rules);

        // required
        $is_required = in_array('required', $validation_rules);
        if ($is_required && $field_value == '') {
            setEventMessage($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities($field_label)), 'errors');
            $error++;
        }

        // numeric (escape if empty)
        else if (in_array('numeric', $validation_rules) && $field_value != '' && ! is_numeric($field_value)) {
            setEventMessage($langs->transnoentities('ErrorFieldMustBeANumeric', $langs->transnoentities($field_label)), 'errors');
            $error++;
        }

        // string (escape if empty)
        else if (in_array('string', $validation_rules) && $field_value != '' && ! is_string($field_value)) {
            setEventMessage($langs->transnoentities('ErrorFieldFormat', $langs->transnoentities($field_label)), 'errors');
            $error++;
        }

        // validEmail (escape if empty)
        else if (in_array('validEmail', $validation_rules) && $field_value != '' && ! filter_var($field_value, FILTER_VALIDATE_EMAIL)) {
            setEventMessage($langs->transnoentities('ErrorFieldFormat', $langs->transnoentities($field_label)), 'errors');
            $error++;
        }

        // validTel (escape if empty)
        else if (in_array('validTel', $validation_rules) && $field_value != '' && ! preg_match('/^[0-9\-\(\)\/\+\s]*$/', $field_value)) {
            setEventMessage($langs->transnoentities('ErrorFieldFormat', $langs->transnoentities($field_label)), 'errors');
            $error++;
        }

        // validUrl (escape if empty)
        else if (in_array('validUrl', $validation_rules) && $field_value != '' && ! filter_var($field_value, FILTER_VALIDATE_URL)) {
            setEventMessage($langs->transnoentities('ErrorFieldFormat', $langs->transnoentities($field_label)), 'errors');
            $error++;
        }

        // validID (escape if empty)
        else if (in_array('validID', $validation_rules) && $field_value != '' && is_numeric($field_value) && $field_value <= 0) {
            $error_msg = ($is_required ? 'ErrorFieldRequired' : 'ErrorFieldFormat');
            setEventMessage($langs->transnoentities($error_msg, $langs->transnoentities($field_label)), 'errors');
            $error++;
        }

        // greaterThan (escape if empty)
        else if (array_match('/^greaterThan\(([0-9]+)\)$/i', $validation_rules, $matches) && $field_value != '' && is_numeric($field_value) && $field_value <= $matches[1]) {
            setEventMessage($langs->transnoentities('ErrorFieldMustBeGreaterThan', $langs->transnoentities($field_label), $matches[1]), 'errors');
            $error++;
        }

        // lessThan (escape if empty)
        else if (array_match('/^lessThan\(([0-9]+)\)$/i', $validation_rules, $matches) && $field_value != '' && is_numeric($field_value) && $field_value >= $matches[1]) {
            setEventMessage($langs->transnoentities('ErrorFieldMustBeLessThan', $langs->transnoentities($field_label), $matches[1]), 'errors');
            $error++;
        }

        // minLength (escape if empty)
        else if (array_match('/^minLength\(([0-9]+)\)$/i', $validation_rules, $matches) && $field_value != '' && strlen($field_value) < $matches[1]) {
            setEventMessage($langs->transnoentities('ErrorFieldMustHaveMinLength', $langs->transnoentities($field_label), $matches[1]), 'errors');
            $error++;
        }

        // maxLength (escape if empty)
        else if (array_match('/^maxLength\(([0-9]+)\)$/i', $validation_rules, $matches) && $field_value != '' && strlen($field_value) > $matches[1]) {
            setEventMessage($langs->transnoentities('ErrorFieldMustHaveMaxLength', $langs->transnoentities($field_label), $matches[1]), 'errors');
            $error++;
        }

        if ($return_error_number) {
            return $error;
        }
        else {
            return $error > 0 ? false : true;
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('validate_fields'))
{
    /**
     * Check/validate fields
     *
     * @param   array   $fields   array of fields as [
     * 'my_field' => array(
     *     'label'            (*) => 'MyField', // will be displayed on validation error message
     *     'validation_rules' (*) => 'required', // possible values: 'required|numeric|string|validEmail|validTel|validUrl|validID|greaterThan()|lessThan()|minLength()|maxLength()'
     *     'enabled'              => '$conf->module->enabled' // condition to enable field validation
     * )]
     * array keys with (*) are required
     * @return  boolean   true or false
     */
    function validate_fields($fields)
    {
        $error = 0;

        foreach($fields as $name => $field)
        {
            if (isset($field['validation_rules']) && ! empty($field['validation_rules']) && (! isset($field['enabled']) || empty($field['enabled']) || verifCond($field['enabled']))) {
                $error += validate_field($name, $field['label'], $field['validation_rules'], true);
            }
        }

        return $error > 0 ? false : true;
    }
}

// --------------------------------------------------------------------

if (! function_exists('validate_extra_fields'))
{
    /**
     * Check/validate extrafields
     *
     * @param   object    $object   object instance
     * @return  boolean             true or false
     */
    function validate_extra_fields($object)
    {
        global $db;

        // fetch optionals attributes and labels
        if (isset($object->extrafields)) {
            $extrafields = $object->extrafields;
        }
        else {
            $extrafields = new ExtraFields($db);
        }
        $extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

        // Fill array 'array_options' with data from add form
        $result = $extrafields->setOptionalsFromPost($extralabels, $object);

        return $result >= 0 ? true : false;
    }
}

// --------------------------------------------------------------------

if (! function_exists('validate_field_by_name'))
{
    /**
     * Check/validate a field by its name
     *
     * @param   string   $field_name   field name
     * @param   array    $fields       array of fields where $field_name should exist
     * @return  boolean                true or false
     */
    function validate_field_by_name($field_name, $fields)
    {
        foreach($fields as $name => $field)
        {
            if ($name == $field_name) {
                return validate_field($name, $field['label'], $field['validation_rules']);
            }
        }

        return true; // return true instead of false to continue execution
    }
}
