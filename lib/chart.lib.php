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

require_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';

// --------------------------------------------------------------------

if (! class_exists('Chart'))
{
    /**
     * Chart class (placing this class in a separate file has no benefits, since its only a wrapper for DolGraph class)
     */
    class Chart extends DolGraph
    {
        /**
         * Generate chart
         * 
         * @param   string   $type     chart type: 'pie', 'bars' or 'lines'
         * @param   array    $data     chart data (array)
         * @param   array    $legend   chart legend (array)
         * @param   string   $title    chart title
         * @param   string   $width    chart width
         * @param   string   $height   chart height
         */
        public function generate($type, $data, $legend = array(), $title = '', $width = '', $height = '')
        {
            if (! $this->isGraphKo())
            {
                global $langs;

                // Fill default parameters
                if (empty($width)) {
                    $width = self::getDefaultGraphSizeForStats('width');
                }
                if (empty($height)) {
                    $height = self::getDefaultGraphSizeForStats('height');
                }
                $show_legend = empty($legend) ? 0 : 1;

                // Set chart settings
                $this->SetData($data);
                $this->SetLegend($legend);
                $this->setShowLegend($show_legend);
                $this->setWidth($width);
                $this->setHeight($height);
                $this->SetType(array($type));
                if (in_array($type, array('bars', 'lines'))) {
                    $this->SetMaxValue($this->GetCeilMaxValue());
                    $this->SetMinValue(min(0, $this->GetFloorMinValue()));
                }
                if (! empty($title)) {
                    $this->SetTitle($langs->trans($title));
                }
            }
        }

        /**
         * Display chart (shortcut for draw & show)
         * 
         * @param   string   $filename     chart file name
         * @param   string   $fileurl      chart file url
         */
        public function display($filename, $fileurl = '')
        {
            if (! $this->isGraphKo())
            {
                $this->draw($filename, $fileurl);
                echo $this->show();
            }
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_stats_graph'))
{
    /**
     * Print a statistics graph
     *
     * @param   string   $table_name       Table name (without prefix)
     * @param   string   $field_name       Table field name
     * @param   array    $field_values     Table field values, e.: array(0 => 'Status 1', 1 => 'Status 2')
     * @param   string   $graph_type       Type of graph ('pie', 'bars', 'lines')
     * @param   string   $graph_title      Graph title
     * @param   string   $where            Sql request where clause
     * @param   string   $pk_field_name    Table primary key name
     */
    function print_stats_graph($table_name, $field_name, $field_values = array(), $graph_type = 'pie', $graph_title = 'Statistics', $where = '', $pk_field_name = 'rowid')
    {
        global $db, $langs, $conf, $bc, $stats_id;

        if (empty($stats_id)) {
            $stats_id = 1;
        }

        $sql = 'SELECT count(t.'.$pk_field_name.'), t.'.$field_name;
        $sql.= ' FROM '.MAIN_DB_PREFIX.$table_name.' as t';
        if (! empty($where)) $sql.= ' WHERE '.$where;
        $sql.= ' GROUP BY t.'.$field_name;

        $resql = $db->query($sql);

        if ($resql)
        {
            $num = $db->num_rows($resql);

            $i = 0;
            $total = 0;
            $totalinprocess = 0;
            $dataseries = array();
            $vals = array();

            while ($i < $num)
            {
                $row = $db->fetch_row($resql);
                if ($row)
                {
                    $vals[$row[1]]   = $row[0];
                    $totalinprocess += $row[0];
                    $total          += $row[0];
                }
                $i++;
            }
            $db->free($resql);

            echo '<table class="noborder nohover" width="100%">';
            echo '<tr class="liste_titre"><td colspan="2">'.$langs->trans($graph_title).'</td></tr>'."\n";
            $var = true;

            foreach ($field_values as $key => $value)
            {
                $count = (isset($vals[$key]) ? (int) $vals[$key] : 0);

                if ($count > 0)
                {
                    $label = $langs->trans($value);

                    $dataseries[] = array($label, $count);

                    if (! $conf->use_javascript_ajax)
                    {
                        $var = ! $var;
                        echo "<tr ".$bc[$var].">";
                        echo '<td>'.$label.'</td>';
                        echo '<td align="right"><a href="list.php?'.$field_name.'='.$key.'">'.$count.'</a></td>';
                        echo "</tr>\n";
                    }
                }
            }

            if ($conf->use_javascript_ajax)
            {
                echo '<tr class="impair"><td align="center" colspan="2">';

                // Generate graph
                $graph = new Chart();
                $graph->generate($graph_type, $dataseries);
                $graph->setShowLegend(1); // force show legend
                $graph->setShowPercent(1);
                $graph->display('stats_'.($stats_id++));

                echo '</td></tr>';
            }

            echo '<tr class="liste_total"><td>'.$langs->trans('Total').'</td><td align="right">'.$total.'</td></tr>';
            echo '</table><br>';
        }
        else
        {
            dol_print_error($db);
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_stats_graph_from_data'))
{
    /**
     * Print a statistics graph from predefined data
     *
     * @param   array    $data             Data to show
     * @param   array    $legend           Legend array
     * @param   string   $graph_type       Type of graph ('pie', 'bars', 'lines')
     * @param   string   $graph_title      Graph title
     */
    function print_stats_graph_from_data($data, $legend = array(), $graph_type = 'pie', $graph_title = 'Statistics')
    {
        global $langs, $stats_id;

        if (empty($stats_id)) {
            $stats_id = 1;
        }

        echo '<table class="noborder nohover" width="100%">';
        echo '<tr class="liste_titre"><td colspan="2">'.$langs->trans($graph_title).'</td></tr>'."\n";
        echo '<tr class="impair"><td align="center" colspan="2">';

        // Generate graph
        $graph = new Chart();
        $graph->generate($graph_type, $data, $legend);
        $graph->setShowPercent(1);
        $graph->display('stats_'.($stats_id++));

        echo '</td></tr>';
        echo '</table><br>';
    }
}
