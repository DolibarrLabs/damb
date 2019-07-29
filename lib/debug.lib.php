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

if (! function_exists('debug'))
{
    /**
     * Add a message to Debug bar
     *
     * @param   string   $message   Message
     * @param   string   $label     Label, possible values: 'info', 'error', 'warning', ...
     */
    function debug($message, $label = 'info')
    {
        global $debugbar;

        if (is_object($debugbar)) {
            $debugbar['messages']->addMessage($message, $label);
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('exception'))
{
    /**
     * Add an exception to Debug bar
     *
     * @param   Exception   $exception   Exception object
     */
    function exception($exception)
    {
        global $debugbar;

        if (is_object($debugbar)) {
            $debugbar['exceptions']->addException($exception);
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('start_time_measure'))
{
    /**
     * Start time measure, that will appear later on Debug bar Timeline
     *
     * @param   string   $name        measure name, used to stop measure later
     * @param   string   $label       measure label, will appear on Timeline
     * @param   string   $stop_name   name of measure to stop before starting the new one, leave empty if not
     */
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

// --------------------------------------------------------------------

if (! function_exists('stop_time_measure'))
{
    /**
     * Stop time measure
     *
     * @param   string   $name   measure name
     */
    function stop_time_measure($name)
    {
        global $debugbar;

        if (is_object($debugbar) && $debugbar['time']->hasStartedMeasure($name)) {
            $debugbar['time']->stopMeasure($name);
        }
    }
}
