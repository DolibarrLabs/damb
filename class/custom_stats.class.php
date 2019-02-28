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

require_once DOL_DOCUMENT_ROOT . '/core/class/stats.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';

/**
 * CustomStats class
 */

class CustomStats extends Stats
{
    /**
     * @var string From clause
     */
    protected $from = '';
    /**
     * @var string Join clause
     */
    protected $join = '';
    /**
     * @var string Where clause
     */
    protected $where = '';
    /**
     * @var string Date field
     */
    protected $date_field = '';
    /**
     * @var string Amount field
     */
    protected $amount_field = '';


    /**
     * Constructor
     *
     * @param     $table_name     Table name
     * @param     $join           Join clause (without ', ')
     * @param     $where          Where clause (without 'WHERE ' or 'AND ')
     * @param     $date_field     Date field name
     * @param     $amount_field   Amount field name
     */
    public function __construct($table_name, $join = '', $where = '', $date_field = 'creation_date', $amount_field = '')
    {
        global $db;

        $this->db = $db;

        $this->from = MAIN_DB_PREFIX . $table_name;

        $this->join = $join;

        $this->where = $where;

        $this->date_field = $date_field;

        $this->amount_field = $amount_field;
    }

    /**
     * Return object number by month for a year
     *
     * @param   int     $year       Year to scan
     * @param   int     $format     0=Label of absiss is a translated text, 1=Label of absiss is month number, 2=Label of absiss is first letter of month
     * @return  array               Array with number by month
     */
    public function getNbByMonth($year, $format = 0)
    {
        $sql = "SELECT date_format(".$this->date_field.",'%m') as dc, COUNT(*) as nb";
        $sql.= " FROM ".$this->from;
        if (! empty($this->join)) $sql.= ", ".$this->join;
        $sql.= " WHERE ".$this->date_field." BETWEEN '".$this->db->idate(dol_get_first_day($year))."' AND '".$this->db->idate(dol_get_last_day($year))."'";
        if (! empty($this->where)) $sql.= " AND ".$this->where;
        $sql.= " GROUP BY dc";
        $sql.= $this->db->order('dc','DESC');

        return $this->_getNbByMonth($year, $sql, $format);
    }

    /**
     * Return object number per year
     *
     * @return  array   Array with number by year
     *
     */
    public function getNbByYear()
    {
        $sql = "SELECT date_format(".$this->date_field.",'%Y') as dc, COUNT(*) as nb";
        if (! empty($this->amount_field)) $sql.= ", SUM(".$this->amount_field.")";
        $sql.= " FROM ".$this->from;
        if (! empty($this->join)) $sql.= ", ".$this->join;
        if (! empty($this->where)) $sql.= " WHERE ".$this->where;
        $sql.= " GROUP BY dc";
        $sql.= $this->db->order('dc', 'DESC');

        return $this->_getNbByYear($sql);
    }

    /**
     *  Return nb, total and average
     *
     *  @return array   Array of values
     */
    public function getAllByYear()
    {
        $sql = "SELECT date_format(".$this->date_field.",'%Y') as year, COUNT(*) as nb";
        if (! empty($this->amount_field)) $sql.= ", SUM(".$this->amount_field.") as total, AVG(".$this->amount_field.") as avg";
        $sql.= " FROM ".$this->from;
        if (! empty($this->join)) $sql.= ", ".$this->join;
        if (! empty($this->where)) $sql.= " WHERE ".$this->where;
        $sql.= " GROUP BY year";
        $sql.= $this->db->order('year', 'DESC');

        return $this->_getAllByYear($sql);
    }

    /**
     * Return object amount by month for a year
     *
     * @param   int     $year       Year to scan
     * @param   int     $format     0=Label of absiss is a translated text, 1=Label of absiss is month number, 2=Label of absiss is first letter of month
     * @return  array               Array with amount by month
     */
    public function getAmountByMonth($year, $format = 0)
    {
        $sql = "SELECT date_format(".$this->date_field.",'%m') as dc, SUM(".$this->amount_field.")";
        $sql.= " FROM ".$this->from;
        if (! empty($this->join)) $sql.= ", ".$this->join;
        $sql.= " WHERE ".$this->date_field." BETWEEN '".$this->db->idate(dol_get_first_day($year))."' AND '".$this->db->idate(dol_get_last_day($year))."'";
        if (! empty($this->where)) $sql.= " AND ".$this->where;
        $sql.= " GROUP BY dc";
        $sql.= $this->db->order('dc','DESC');

        return $this->_getAmountByMonth($year, $sql, $format);
    }

    /**
     * Return object amount average by month for a year
     *
     * @param   int     $year       year for stats
     * @param   int     $format     0=Label of absiss is a translated text, 1=Label of absiss is month number, 2=Label of absiss is first letter of month
     * @return  array               array with number by month
     */
    public function getAverageByMonth($year, $format = 0)
    {
        $sql = "SELECT date_format(".$this->date_field.",'%m') as dc, AVG(".$this->amount_field.")";
        $sql.= " FROM ".$this->from;
        if (! empty($this->join)) $sql.= ", ".$this->join;
        $sql.= " WHERE ".$this->date_field." BETWEEN '".$this->db->idate(dol_get_first_day($year))."' AND '".$this->db->idate(dol_get_last_day($year))."'";
        $sql.= " AND ".$this->where;
        $sql.= " GROUP BY dc";
        $sql.= $this->db->order('dc','DESC');

        return $this->_getAverageByMonth($year, $sql, $format);
    }
}
