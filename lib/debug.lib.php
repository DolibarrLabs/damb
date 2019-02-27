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

/**
 * Add a message to Debug bar
 *
 * @param     $message     Message
 * @param     $label       Label, possible values: 'info', 'error', 'warning', ...
 */
if (! function_exists('debug'))
{
    function debug($message, $label = 'info')
    {
        global $debugbar;

        if (is_object($debugbar)) {
            $debugbar['messages']->addMessage($message, $label);
        }
    }
}

/**
 * Add an exception to Debug bar
 *
 * @param     $exception     Exception object
 */
if (! function_exists('exception'))
{
    function exception($exception)
    {
        global $debugbar;

        if (is_object($debugbar)) {
            $debugbar['exceptions']->addException($exception);
        }
    }
}

/**
 * Start time measure, that will appear later on Debug bar Timeline
 *
 * @param     $name        measure name, used to stop measure later
 * @param     $label       measure label, will appear on Timeline
 * @param     $stop_name   name of measure to stop before starting the new one, leave empty if not
 */
if (! function_exists('start_time_measure'))
{
    function start_time_measure($name, $label = '', $stop_name = '')
    {
        global $debugbar;

        if (is_object($debugbar))
        {
            if (! empty($stop_name)) {
                stop_time_measure($stop_name);
            }
            $debugbar['time']->startMeasure($name, $label);
        }
    }
}

/**
 * Stop time measure
 *
 * @param     $name        measure name
 */
if (! function_exists('stop_time_measure'))
{
    function stop_time_measure($name)
    {
        global $debugbar;

        if (is_object($debugbar) && $debugbar['time']->hasStartedMeasure($name)) {
            $debugbar['time']->stopMeasure($name);
        }
    }
}
