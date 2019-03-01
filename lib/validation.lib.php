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

/**
 * Loops on an array values & check if a value matches the pattern using preg_match
 *
 * @param     $pattern   Regular expression pattern
 * @param     $array     Array to check
 * @param     $matches   Pattern matches
 * @return    int|bool   1 if the pattern matches given array, 0 if it does not, or FALSE if an error occurred.
 */
if (! function_exists('array_match'))
{
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

/**
 * Check/validate specified field
 *
 * @param      $field_name                 field name
 * @param      $field_trans                field translation
 * @param      $field_validation_rules     field validatin rules
 * @param      $return_err_number          return errors number or boolean value
 * @return     boolean|int                 true/false | errors number
 */
if (! function_exists('validate_field'))
{
    function validate_field($field_name, $field_trans, $field_validation_rules, $return_err_number = false)
    {
        global $langs;

        $langs->load('errors');

        $error = 0;
        $field_value = GETPOST($field_name);
        $validation_rules = explode('|', $field_validation_rules);

        // required
        $is_required = in_array('required', $validation_rules);
        if ($is_required && $field_value == '') {
            setEventMessage($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities($field_trans)), 'errors');
            $error++;
        }

        // numeric (escape if empty)
        else if (in_array('numeric', $validation_rules) && $field_value != '' && ! is_numeric($field_value)) {
            setEventMessage($langs->transnoentities('ErrorFieldMustBeANumeric', $langs->transnoentities($field_trans)), 'errors');
            $error++;
        }

        // string (escape if empty)
        else if (in_array('string', $validation_rules) && $field_value != '' && ! is_string($field_value)) {
            setEventMessage($langs->transnoentities('ErrorFieldFormat', $langs->transnoentities($field_trans)), 'errors');
            $error++;
        }

        // validEmail (escape if empty)
        else if (in_array('validEmail', $validation_rules) && $field_value != '' && ! filter_var($field_value, FILTER_VALIDATE_EMAIL)) {
            setEventMessage($langs->transnoentities('ErrorFieldFormat', $langs->transnoentities($field_trans)), 'errors');
            $error++;
        }

        // validTel (escape if empty)
        else if (in_array('validTel', $validation_rules) && $field_value != '' && ! preg_match('/^[0-9\-\(\)\/\+\s]*$/', $field_value)) {
            setEventMessage($langs->transnoentities('ErrorFieldFormat', $langs->transnoentities($field_trans)), 'errors');
            $error++;
        }

        // validUrl (escape if empty)
        else if (in_array('validUrl', $validation_rules) && $field_value != '' && ! filter_var($field_value, FILTER_VALIDATE_URL)) {
            setEventMessage($langs->transnoentities('ErrorFieldFormat', $langs->transnoentities($field_trans)), 'errors');
            $error++;
        }

        // validID (escape if empty)
        else if (in_array('validID', $validation_rules) && $field_value != '' && is_numeric($field_value) && $field_value <= 0) {
            $error_msg = ($is_required ? 'ErrorFieldRequired' : 'ErrorFieldFormat');
            setEventMessage($langs->transnoentities($error_msg, $langs->transnoentities($field_trans)), 'errors');
            $error++;
        }

        // greaterThan (escape if empty)
        else if (array_match('/^greaterThan\(([0-9]+)\)$/i', $validation_rules, $matches) && $field_value != '' && is_numeric($field_value) && $field_value <= $matches[1]) {
            setEventMessage($langs->transnoentities('ErrorFieldMustBeGreaterThan', $langs->transnoentities($field_trans), $matches[1]), 'errors');
            $error++;
        }

        // lessThan (escape if empty)
        else if (array_match('/^lessThan\(([0-9]+)\)$/i', $validation_rules, $matches) && $field_value != '' && is_numeric($field_value) && $field_value >= $matches[1]) {
            setEventMessage($langs->transnoentities('ErrorFieldMustBeLessThan', $langs->transnoentities($field_trans), $matches[1]), 'errors');
            $error++;
        }

        // minLength (escape if empty)
        else if (array_match('/^minLength\(([0-9]+)\)$/i', $validation_rules, $matches) && $field_value != '' && strlen($field_value) < $matches[1]) {
            setEventMessage($langs->transnoentities('ErrorFieldMustHaveMinLength', $langs->transnoentities($field_trans), $matches[1]), 'errors');
            $error++;
        }

        // maxLength (escape if empty)
        else if (array_match('/^maxLength\(([0-9]+)\)$/i', $validation_rules, $matches) && $field_value != '' && strlen($field_value) > $matches[1]) {
            setEventMessage($langs->transnoentities('ErrorFieldMustHaveMaxLength', $langs->transnoentities($field_trans), $matches[1]), 'errors');
            $error++;
        }

        if ($return_err_number) {
            return $error;
        }
        else {
            return $error > 0 ? false : true;
        }
    }
}

/**
 * Check/validate fields
 *
 * @param      $fields     array of fields as [
   array(
       'name'             => 'my_field', // used to get field value
       'translation'      => 'MyField', // will be displayed on validation error message
       'validation_rules' => 'required' // possible values: 'required|numeric|string|validEmail|validTel|validUrl|validID|greaterThan()|lessThan()|minLength()|maxLength()'
   )
 ]
 * @return     boolean     true or false
 */
if (! function_exists('validate_fields'))
{
    function validate_fields($fields)
    {
        $error = 0;

        foreach($fields as $field) {
            $error += validate_field($field['name'], $field['translation'], $field['validation_rules'], true);
        }

        return $error > 0 ? false : true;
    }
}

/**
 * Check/validate a field by its name
 *
 * @param      $field_name     field name
 * @param      $fields         array of fields where $field_name should exist
 * @return     boolean         true or false
 */
if (! function_exists('validate_field_by_name'))
{
    function validate_field_by_name($field_name, $fields)
    {
        foreach($fields as $field) {
            if ($field['name'] == $field_name) {
                return validate_field($field['name'], $field['translation'], $field['validation_rules']);
            }
        }

        return true; // return true instead of false to avoid blocking actions
    }
}
