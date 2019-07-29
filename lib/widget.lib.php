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

if (! function_exists('set_title'))
{
    /**
     * Set widget title
     *
     * @param   ModeleBoxes   $widget   widget object instance
     * @param   string        $title    widget title
     * @param   int           $max      maximum number of rows to show (will be added to translated title if so)
     */
    function set_title(&$widget, $title, $max = 5)
    {
        global $langs;

        // Use configuration value for max lines count
        $widget->max = $max;

        // Set widget title
        $widget->info_box_head = array(
            // Title text
            'text' => $langs->trans($title, $max),
            // Add a link
            'sublink' => '',
            // Sublink icon placed after the text
            'subpicto' => '',
            // Sublink icon HTML alt text
            'subtext' => '',
            // Sublink HTML target
            'target' => '',
            // HTML class attached to the picto and link
            'subclass' => 'center',
            // Limit and truncate with "…" the displayed text lenght, 0 = disabled
            'limit' => 0,
            // Adds translated " (Graph)" to a hidden form value's input (?)
            'graph' => false
        );
    }
}

// --------------------------------------------------------------------

if (! function_exists('set_link'))
{
    /**
     * Set widget link
     *
     * @param   ModeleBoxes   $widget    widget object instance
     * @param   string        $link      widget link
     * @param   string        $picture   link picture
     * @param   string        $tooltip   tooltip text
     * @param   string        $target    link target, use '' or '_blank' to open in a new window / tab
     * @param   string        $class     link css class
     */
    function set_link(&$widget, $link, $picture, $tooltip = '', $target = '_self', $class = 'boxhandle')
    {
        global $langs;

        if (function_exists('version_compare') && version_compare(DOL_VERSION, '9.0.0') >= 0) {
            $class .= ' valignmiddle';
        }

        $widget->info_box_head['sublink']  = $link;
        $widget->info_box_head['subpicto'] = $picture;
        $widget->info_box_head['subtext']  = $langs->trans($tooltip);
        $widget->info_box_head['target']   = $target;
        $widget->info_box_head['subclass'] = $class;
    }
}

// --------------------------------------------------------------------

if (! function_exists('add_content'))
{
    /**
     * Add content to widget
     *
     * @param   ModeleBoxes   $widget           widget object instance
     * @param   string        $text             text to show
     * @param   string        $attr             element attributes (align, colspan, ...)
     * @param   boolean       $clean_text       allow HTML cleaning & truncation
     * @param   int           $max_length       maximum text length (0 = disabled)
     * @param   string        $first_col_attr   first column attributes
     */
    function add_content(&$widget, $text, $attr = '', $clean_text = false, $max_length = 0, $first_col_attr = '')
    {
        $lines_count = count($widget->info_box_contents);

        $current_line = $lines_count > 0 ? $lines_count - 1 : 0;

        $widget->info_box_contents[$current_line][] = array(
            // HTML properties of the TD element
            'td'           => $attr,
            // Fist line logo
            //'logo'         => 'mypicture@mymodule',
            // Main text
            'text'         => $text,
            // Secondary text
            //'text2'        => '<p><strong>Another text</strong></p>',
            // Unformatted text, usefull to load javascript elements
            //'textnoformat' => '',
            // Link on 'text' and 'logo' elements
            //'url'          => 'http://example.com',
            // Link's target HTML property
            //'target'       => '_blank',
            // Truncates 'text' element to the specified character length, 0 = disabled
            'maxlength'    => $max_length,
            // Prevents HTML cleaning (and truncation)
            'asis'         => ! $clean_text, // abbr.: asis = as it is
            // Same for 'text2'
            //'asis2'        => true
        );

        $cols_count = count($widget->info_box_contents[$current_line]);

        if ($cols_count == 1 && ! empty($first_col_attr)) {
            //  HTML properties of the TR element. Only available on the first column.
            $widget->info_box_contents[$current_line][0]['tr'] = $first_col_attr;
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('new_line'))
{
    /**
     * Add a new line to widget content
     *
     * @param   ModeleBoxes   $widget   widget object instance
     */
    function new_line(&$widget)
    {
        $new_line = count($widget->info_box_contents);

        $widget->info_box_contents[$new_line] = array();
    }
}
