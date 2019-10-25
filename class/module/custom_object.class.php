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

require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';

/**
 * CustomObject class
 */

class CustomObject extends CommonObject
{
    /**
     * @var string Id to identify managed object
     */
    public $element = ''; // e.: 'myobject'
    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = ''; // e.: 'mytable'
    /**
     * @var array Fetch fields
     */
    public $fetch_fields = array(); // e.: array('field_1', 'field_2', 'field_3')
    /**
     * @var array Date fields
     */
    public $date_fields = array(); // e.: array('creation_date')
    /**
     * @var string Primary key name (id field)
     */
    public $pk_name = 'rowid';
    /**
     * @var string Ref. field name
     */
    public $ref_field_name = 'ref';
    /**
     * @var array Object rows (used in fetchAll function)
     */
    public $rows = array();
    /**
     * @var string Triggers prefix
     */
    public $triggers_prefix;
    /**
     * @var string Banner picture
     */
    public $picto = ''; // e.: 'mypicture@mymodule'
    /**
     * @var array Tooltip details
     */
    public $tooltip_details = array(); // e.: array('detail_1' => 'value_1', 'detail_2' => 'value_2')
    /**
     * @var string Document title
     */
    public $doc_title = '';
    /**
     * @var string Document date
     */
    public $doc_date = '';
    /**
     * @var array Document lines/rows
     */
    public $doc_lines = array();
    /**
     * @var string Card url
     */
    public $card_url = '#';
    /**
     * @var string Module part
     */
    public $modulepart; // e.: 'mymodule'
    /**
     * @var string Numbering model constant name
     */
    public $num_model_constant_name; // e.: 'MYMODULE_ADDON'
    /**
     * @var string Numbering model template path
     */
    public $num_model_template_path; // e.: 'mymodule/core/num_models'
    /**
     * @var string Document model constant name
     */
    public $doc_model_constant_name; // e.: 'MYMODULE_ADDON_PDF'
    /**
     * @var string Document model template path
     */
    public $doc_model_template_path; // e.: 'mymodule/core/doc_models'
    /**
     * @var object Extrafields
     */
    public $extrafields;


    /**
     * Constructor
     * 
     */
    public function __construct()
    {
        global $db;

        $this->db = $db;
        $this->triggers_prefix = strtoupper($this->element);
        $this->extrafields = new ExtraFields($this->db);
    }

    /**
     * Clone an object
     *
     * @param  $obj  object to clone from
     * @return $this
     */
    public function _clone($obj)
    {
        foreach (get_object_vars($obj) as $key => $value)
        {
            if (in_array($key, $this->date_fields)) {
                $this->$key = $this->db->jdate($value); // Fix error: dol_print_date function call with deprecated value of time
            }
            else {
                $this->$key = $value;
            }
        }

        // ensure that $this->id is filled because we use it in update & delete functions
        if (! in_array('id', $this->fetch_fields)) {
            $this->id = $obj->{$this->pk_name};
        }

        return $this;
    }

    /**
     * Create object into database
     *
     * @param  array  $data array, e.: array('my_field_name' => 'my_field_value', 'second_field_name' => 'second_field_value')
     * @param  int    $notrigger 0=launch triggers after, 1=disable triggers
     * @return int    <0 if KO, Id of created object if OK
     */
    public function create($data, $notrigger = 1)
    {
        $error = 0;

        // INSERT request
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . $this->table_element . "(";
        foreach ($data as $key => $value) {
            $sql.= "`" . $key . "`,";
        }
        $sql = substr($sql, 0, -1); // Remove the last ','

        $sql.= ") VALUES (";
        foreach ($data as $key => $value) {
            $sql.= $this->escape($value) . ",";
        }
        $sql = substr($sql, 0, -1); // Remove the last ','

        $sql.= ")";

        $this->db->begin();

        dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (! $resql) {
            $error ++;
            $this->errors[] = "Error " . $this->db->lasterror();
        }

        if (! $error) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element, $this->pk_name);

            if (! $notrigger) {
                $error = $this->run_triggers('_CREATE');
            }
        }

        // Commit or rollback
        if ($error) {
            foreach ($this->errors as $errmsg) {
                dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();
            setEventMessage($this->error, 'errors');

            return -1 * $error;
        } else {
            $this->db->commit();

            return $this->id;
        }
    }

    /**
     * Load object in memory from database
     *
     * @param  int     $id object Id
     * @param  string  $ref object ref
     * @return int     <0 if KO, >0 if OK
     */
    public function fetch($id, $ref = '')
    {
        // SELECT request
        $sql = "SELECT ";
        foreach ($this->fetch_fields as $field) {
            $sql.= "`" . $field . "`,";
        }
        $sql = substr($sql, 0, -1); // Remove the last ','
        $sql.= " FROM " . MAIN_DB_PREFIX . $this->table_element;
        $sql.= " WHERE ";
        if (! empty($ref)) {
            $sql.= $this->ref_field_name . " = '" . $ref . "'";
        }
        else {
            $sql.= $this->pk_name . " = " . $id;
        }

        dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);

                foreach ($this->fetch_fields as $field)
                {
                    if (in_array($field, $this->date_fields)) {
                        $this->$field = $this->db->jdate($obj->$field); // Fix error: dol_print_date function call with deprecated value of time
                    }
                    else {
                        $this->$field = $obj->$field;
                    }
                }

                // ensure that $this->id is filled because we use it in update & delete functions
                if (! in_array('id', $this->fetch_fields)) {
                    $this->id = $obj->{$this->pk_name};
                }

                $this->db->free($resql);

                return 1;
            }
            $this->db->free($resql);

            return 0;
        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);
            setEventMessage($this->error, 'errors');

            return -1;
        }
    }

    /**
     * Load object in memory from database (wrapper for fetchAll function)
     *
     * @param  string  $where        where clause (without 'WHERE')
     * @return int                   <0 if KO, >0 if OK
     */
    public function fetchWhere($where)
    {
        return $this->fetchAll($where);
    }

    /**
     * Load all object entries in memory from database
     *
     * @param  string  $where        where clause (without 'WHERE')
     * @param  string  $moresql      more sql to add after where clause
     * @return int                   <0 if KO, >0 if OK
     */
    public function fetchAll($where = '', $moresql = '')
    {
        // Init rows
        $this->rows = array();

        if (empty($this->fetch_fields)) {
            return 0;
        }

        // SELECT request
        $sql = "SELECT DISTINCT ";
        foreach ($this->fetch_fields as $field) {
            $sql.= "`" . $field . "`,";
        }
        $sql = substr($sql, 0, -1); // Remove the last ','
        $sql.= " FROM " . MAIN_DB_PREFIX . $this->table_element;
        if (! empty($where)) $sql.= " WHERE " . $where;
        if (! empty($moresql)) $sql.= " " . $moresql;

        dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            if ($num) {
                $i = 0;
                $set_id = (! in_array('id', $this->fetch_fields) ? true : false);

                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);

                    $classname = get_class($this);

                    $this->rows[$i] = new $classname();

                    foreach ($this->fetch_fields as $field)
                    {
                        if (in_array($field, $this->date_fields)) {
                            $this->rows[$i]->$field = $this->db->jdate($obj->$field); // Fix error: dol_print_date function call with deprecated value of time
                        }
                        else {
                            $this->rows[$i]->$field = $obj->$field;
                        }
                    }

                    // ensure that $this->id is filled because we use it in update/delete/getNomUrl functions
                    if ($set_id) {
                        $this->rows[$i]->id = $obj->{$this->pk_name};
                    }

                    $i++;
                }

                $this->db->free($resql);

                return 1;
            }
            $this->db->free($resql);

            return 0;
        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);
            setEventMessage($this->error, 'errors');

            return -1;
        }
    }

    /**
     * Return object entries count
     *
     * @param  string  $where        where clause (without 'WHERE')
     * @param  string  $moresql      more sql to add after where clause
     * @return int                   entries count
     */
    public function getCount($where = '', $moresql = '')
    {
        if (empty($this->fetch_fields)) {
            return 0;
        }

        // SELECT request
        $sql = "SELECT DISTINCT ";
        foreach ($this->fetch_fields as $field) {
            $sql.= "t.`" . $field . "`,";
        }
        $sql = substr($sql, 0, -1); // Remove the last ','
        $sql.= " FROM " . MAIN_DB_PREFIX . $this->table_element . ' AS t';
        if (! empty($where)) $sql.= " WHERE " . $where;
        if (! empty($moresql)) $sql.= " " . $moresql;

        dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $this->db->free($resql);

            return $num;
        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);
            setEventMessage($this->error, 'errors');

            return 0;
        }
    }

    /**
     * Load all object entries in memory from database
     *
     * @param  string  $where        where clause (without 'WHERE')
     * @param  string  $moresql      more sql to add after where clause
     * @return int                   <0 if KO, >0 if OK
     */
    public function fetchAllWithExtraFields($where = '', $moresql = '')
    {
        // Init rows
        $this->rows = array();

        if (empty($this->fetch_fields)) {
            return 0;
        }

        // Fetch optionals attributes and labels
        $extralabels = $this->extrafields->fetch_name_optionals_label($this->table_element);
        $search_array_options = $this->extrafields->getOptionalsFromPost($extralabels, '', 'search_');

        // SELECT request
        $sql = "SELECT DISTINCT ";
        foreach ($this->fetch_fields as $field) {
            $sql.= "t.`" . $field . "`,";
        }
        $sql = substr($sql, 0, -1); // Remove the last ','
        // Add fields from extrafields
        foreach ($this->extrafields->attribute_label as $key => $val) {
            $sql.= ($this->extrafields->attribute_type[$key] != 'separate' ? ', ef.'.$key.' as options_'.$key : '');
        }
        $sql.= " FROM " . MAIN_DB_PREFIX . $this->table_element . ' AS t';
        // Join extrafields table
        if (is_array($this->extrafields->attribute_label) && count($this->extrafields->attribute_label)) {
            $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.$this->table_element.'_extrafields as ef ON t.rowid = ef.fk_object';
        }
        // Loop to complete the sql search criterias from extrafields
        if (empty($where)) $where.= '1=1';
        foreach ($search_array_options as $key => $val)
        {
            $tmpkey = preg_replace('/search_options_/', '', $key);
            $type = $this->extrafields->attribute_type[$tmpkey];

            if (in_array($type, array('date', 'datetime')) && ! empty($val))
            {
                $where.= " AND date(ef.".$tmpkey.") = date('".$db->idate($val)."')";
            }
            else
            {
                $crit = $val;
                $mode_search = 0;

                if (in_array($type, array('int', 'double', 'real'))) {
                    $mode_search = 1; // Search on a numeric
                }
                else if (in_array($type, array('sellist', 'link', 'chkbxlst', 'checkbox')) && $crit != '0' && $crit != '-1') {
                    $mode_search = 2; // Search on a foreign key int
                }

                if ($crit != '' && (! in_array($type, array('select', 'sellist')) || ($crit != '0' && $crit != '-1')) && (! in_array($type, array('link')) || $crit != '-1'))
                {
                    $where.= natural_search('ef.'.$tmpkey, $crit, $mode_search);
                }
            }
        }
        if (! empty($where)) $sql.= " WHERE " . $where;
        if (! empty($moresql)) $sql.= " " . $moresql;

        dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            if ($num) {
                $i = 0;
                $set_id = (! in_array('id', $this->fetch_fields) ? true : false);

                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);

                    $classname = get_class($this);

                    $this->rows[$i] = new $classname();

                    foreach (get_object_vars($obj) as $field => $value)
                    {
                        if (in_array($field, $this->date_fields)) {
                            $this->rows[$i]->$field = $this->db->jdate($value); // Fix error: dol_print_date function call with deprecated value of time
                        }
                        else {
                            $this->rows[$i]->$field = $value;
                        }
                    }

                    // ensure that $this->id is filled because we use it in update/delete/getNomUrl functions
                    if ($set_id) {
                        $this->rows[$i]->id = $obj->{$this->pk_name};
                    }

                    $i++;
                }

                $this->db->free($resql);

                return 1;
            }
            $this->db->free($resql);

            return 0;
        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);
            setEventMessage($this->error, 'errors');

            return -1;
        }
    }

    /**
     * Update object into database
     *
     * @param  array   $data array, e.: array('my_field_name' => 'my_field_value', 'second_field_name' => 'second_field_value')
     * @param  int     $notrigger 0=launch triggers after, 1=disable triggers
     * @param  string  $trigger_suffix trigger suffix
     * @return int     <0 if KO, >0 if OK
     */
    public function update($data, $notrigger = 1, $trigger_suffix = '_MODIFY')
    {
        $error = 0;

        // UPDATE request
        $sql = "UPDATE " . MAIN_DB_PREFIX . $this->table_element . " SET ";
        foreach ($data as $key => $value) {
            $sql.= "`" . $key . "` = " . $this->escape($value) . ",";
        }
        $sql = substr($sql, 0, -1); // Remove the last ','
        $sql.= " WHERE ".$this->pk_name."=" . $this->id;

        $this->db->begin();

        dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (! $resql) {
            $error ++;
            $this->errors[] = "Error " . $this->db->lasterror();
        }

        if (! $error) {
            if (! $notrigger) {
                $error = $this->run_triggers($trigger_suffix);
            }
        }

        // Commit or rollback
        if ($error) {
            foreach ($this->errors as $errmsg) {
                dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();
            setEventMessage($this->error, 'errors');

            return -1 * $error;
        } else {
            $this->db->commit();

            // apply changes to object
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }

            return 1;
        }
    }

    /**
     * Update row(s) into database (wrapper for updateAll function)
     *
     * @param  array   $data      array, e.: array('my_field_name' => 'my_field_value', 'second_field_name' => 'second_field_value')
     * @param  string  $where     where clause (without 'WHERE')
     * @param  int     $notrigger 0=launch triggers after, 1=disable triggers
     * @return int                <0 if KO, >0 if OK
     */
    public function updateWhere($data, $where, $notrigger = 1)
    {
        return $this->updateAll($data, $where, $notrigger);
    }

    /**
     * Update all object rows into database
     *
     * @param  array   $data      array, e.: array('my_field_name' => 'my_field_value', 'second_field_name' => 'second_field_value')
     * @param  string  $where     where clause (without 'WHERE')
     * @param  int     $notrigger 0=launch triggers after, 1=disable triggers
     * @return int                <0 if KO, >0 if OK
     */
    public function updateAll($data, $where = '', $notrigger = 1)
    {
        $error = 0;

        // UPDATE request
        $sql = "UPDATE " . MAIN_DB_PREFIX . $this->table_element . " SET ";
        foreach ($data as $key => $value) {
            $sql.= "`" . $key . "` = " . $this->escape($value) . ",";
        }
        $sql = substr($sql, 0, -1); // Remove the last ','
        if (! empty($where)) $sql.= " WHERE ".$where;

        $this->db->begin();

        dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (! $resql) {
            $error ++;
            $this->errors[] = "Error " . $this->db->lasterror();
        }

        if (! $error) {
            if (! $notrigger) {
                $error = $this->run_triggers('_MODIFY_ALL');
            }
        }

        // Commit or rollback
        if ($error) {
            foreach ($this->errors as $errmsg) {
                dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();
            setEventMessage($this->error, 'errors');

            return -1 * $error;
        } else {
            $this->db->commit();

            return 1;
        }
    }

    /**
     * Delete object in database
     *
     * @param  int  $notrigger 0=launch triggers after, 1=disable triggers
     * @return int  <0 if KO, >0 if OK
     */
    public function delete($notrigger = 1)
    {
        $error = 0;

        $this->db->begin();

        if (! $error) {
            if (! $notrigger) {
                $error = $this->run_triggers('_DELETE');
            }
        }

        if (! $error) {
            $sql = "DELETE FROM " . MAIN_DB_PREFIX . $this->table_element;
            $sql.= " WHERE ".$this->pk_name."=" . $this->id;

            dol_syslog(__METHOD__ . " sql=" . $sql);
            $resql = $this->db->query($sql);
            if (! $resql) {
                $error ++;
                $this->errors[] = "Error " . $this->db->lasterror();
            }
            else {
                $this->deleteObjectLinked(); // delete linked objects also
            }
        }

        // Commit or rollback
        if ($error) {
            foreach ($this->errors as $errmsg) {
                dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();
            setEventMessage($this->error, 'errors');

            return -1 * $error;
        } else {
            $this->db->commit();

            return 1;
        }
    }

    /**
     * Delete row(s) in database (wrapper for deleteAll function)
     *
     * @param  string  $where     where clause (without 'WHERE')
     * @param  int     $notrigger 0=launch triggers after, 1=disable triggers
     * @return int                <0 if KO, >0 if OK
     */
    public function deleteWhere($where, $notrigger = 1)
    {
        return $this->deleteAll($where, $notrigger);
    }

    /**
     * Delete all object rows in database
     *
     * @param  string  $where     where clause (without 'WHERE')
     * @param  int     $notrigger 0=launch triggers after, 1=disable triggers
     * @return int                <0 if KO, >0 if OK
     */
    public function deleteAll($where = '', $notrigger = 1)
    {
        $error = 0;

        $this->db->begin();

        if (! $error) {
            if (! $notrigger) {
                $error = $this->run_triggers('_DELETE_ALL');
            }
        }

        if (! $error) {
            if (empty($where)) {
                // TRUNCATE request
                $sql = "TRUNCATE " . MAIN_DB_PREFIX . $this->table_element;
            }
            else {
                // DELETE request
                $sql = "DELETE FROM " . MAIN_DB_PREFIX . $this->table_element;
                $sql.= " WHERE ".$where;
                // Fetch rows ids before deleting
                $this->fetchWhere($where);
            }

            dol_syslog(__METHOD__ . " sql=" . $sql);
            $resql = $this->db->query($sql);
            if (! $resql) {
                $error ++;
                $this->errors[] = "Error " . $this->db->lasterror();
            }
            else {
                // delete linked objects also
                if (empty($where)) {
                    $this->deleteAllObjectLinked();
                }
                else {
                    foreach ($this->rows as $obj) {
                        $obj->deleteObjectLinked();
                    }
                }
            }
        }

        // Commit or rollback
        if ($error) {
            foreach ($this->errors as $errmsg) {
                dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();
            setEventMessage($this->error, 'errors');

            return -1 * $error;
        } else {
            $this->db->commit();

            return 1;
        }
    }

    /**
     * Delete all links between an object $this
     *
     * @return     int     <0 if KO, >0 if OK
     */
    protected function deleteAllObjectLinked()
    {
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."element_element";
        $sql.= " WHERE sourcetype = '".$this->db->escape($this->element)."' OR targettype = '".$this->db->escape($this->element)."'";

        dol_syslog(get_class($this)."::deleteAllObjectLinked", LOG_DEBUG);
        if ($this->db->query($sql))
        {
            return 1;
        }
        else
        {
            $this->error = $this->db->lasterror();
            $this->errors[] = $this->error;
            return -1;
        }
    }

    /**
     * Escape field value
     *
     * @param     $value     field value
     * @return    string     escaped value
     */
    protected function escape($value)
    {
        return is_null($value) ? 'null' : "'".$value."'";
    }

    /**
     * Run Dolibarr triggers (from other modules)
     *
     * @param      $action_suffix      action suffix
     * @return     int                 errors number
     */
    protected function run_triggers($action_suffix)
    {
        global $user, $langs, $conf;

        $error = 0;

        // Call triggers
        require_once DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php";
        $interface = new Interfaces($this->db);
        $action = $this->triggers_prefix.$action_suffix;
        $result = $interface->run_triggers($action, $this, $user, $langs, $conf);
        if ($result < 0) { $error++; $this->errors = $interface->errors; }
        // End call triggers

        return $error;
    }

    /**
     * Returns object extrafields as a ['label' => 'value'] array
     *
     * @return array  Extrafields array
     */
    public function getExtraFields()
    {
        $result = array();

        // Get extrafields
        $extralabels = $this->extrafields->fetch_name_optionals_label($this->table_element, true);
        $this->fetch_optionals($this->id, $extralabels);

        // Fill ['label' => 'value'] array
        if (! empty($this->extrafields->attributes[$this->table_element]['label']))
        {
            foreach ($this->extrafields->attributes[$this->table_element]['label'] as $key => $label) {
                $result[$label] = $this->extrafields->showOutputField($key, $this->array_options['options_' . $key]);
            }
        }

        return $result;
    }

    /**
     * Update extra fields
     *
     * @return     int         >0 if KO, 0 if OK
     */
    public function updateExtraFields()
    {
        $error = 0;

        // Fill array 'array_options' with data from update form
        $extralabels = $this->extrafields->fetch_name_optionals_label($this->table_element);
        $this->fetch_optionals($this->id, $extralabels);
        $ret = $this->extrafields->setOptionalsFromPost($extralabels, $this, GETPOST('attribute'));
        if ($ret < 0) $error++;
        if (! $error)
        {
            $result = $this->insertExtraFields();
            if ($result < 0)
            {
                setEventMessages($this->error, $this->errors, 'errors');
                $error++;
            }
        }

        return $error;
    }

    /**
     * Returns the reference to the following non used object depending on the active numbering model
     * defined into MODULE_RIGHTS_CLASS_ADDON
     *
     * @param  Societe     $soc                   Object thirdparty
     * @return string      Reference
     */
    public function getNextNumRef($soc = '')
    {
        global $conf, $langs;

        if (! empty($conf->global->{$this->num_model_constant_name}))
        {
            $file = $conf->global->{$this->num_model_constant_name};
            $classname = 'NumModel'.ucfirst($file);
            $exists = false;

            // Include file with class
            $dirmodels = array(
                dol_buildpath($this->num_model_template_path)
            );

            foreach ($dirmodels as $dir)
            {
                if (is_dir($dir))
                {
                    // Load file with numbering class (if found)
                    $exists|=@include_once rtrim($dir, '/').'/'.$file.'.php';
                }
            }

            if (! $exists)
            {
                dol_print_error('', 'Failed to include file '.$file);
                return '';
            }

            $obj = new $classname();
            $numref = '';
            $numref = $obj->getNextValue($soc);

            if ($numref != '')
            {
                return $numref;
            }
            else
            {
                $this->error = $obj->error;
                setEventMessage($this->error, 'errors');
                return '';
            }
        }
        else
        {
            $langs->load('errors');
            $this->error = $langs->trans('ErrorModuleSetupNotComplete');
            setEventMessage($this->error, 'errors');
            return '';
        }
    }

    /**
     * Return clicable name (with picto eventually)
     *
     * @param      int        $withpicto     0=No picto, 1=Include picto into link, 2=Only picto
     * @param      string     $title         Tooltip title
     * @return     string                    Chain with URL
     */
    public function getNomUrl($withpicto = 0, $title = '')
    {
        global $langs;

        $ref_field = $this->ref_field_name;

        $result = '';
        $label  = (! empty($title) ? '<u>' . $langs->trans($title) . '</u><br>' : '');
        if (! empty($this->$ref_field)) {
            $label .= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->$ref_field;
        }
        // Add tooltip details
        foreach ($this->tooltip_details as $key => $value) {
            $label .= '<br><b>' . $langs->trans($key) . ':</b> ' . $value;
        }

        $url = dol_buildpath($this->card_url.'?id='.$this->id, 1);
        $link = '<a href="'.$url.'" title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip">';
        $linkend = '</a>';

        if ($withpicto) $result.= ($link.img_object($label, $this->picto, 'class="classfortooltip"').$linkend);
        if ($withpicto && $withpicto != 2) $result.= ' ';
        $result.= $link.$this->$ref_field.$linkend;

        return $result;
    }

    /**
     * Return label of status of object (draft, validated, ...)
     *
     * @param      int        $mode     0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto
     * @return     string     Label
     */
    public function getLibStatut($mode = 0)
    {
        return ''; // temporary fix to allow display banner without errors
    }

    /**
     * Get object image(s)
     *
     * @param     $default_image     Default image to show if no image is available
     * @return    string             Object image(s) HTML output
     */
    public function getImage($default_image)
    {
        global $conf;

        $out = '';
        $image_available = false;
        $dir = $conf->{$this->modulepart}->dir_output;

        $out.= '<div class="floatleft inline-block valignmiddle divphotoref">';

        if (method_exists($this, 'show_photos'))
        {
            $max = 5;
            $width = 80;
            $photos = $this->show_photos($this->modulepart, $dir ,'small', $max, 0, 0, 0, $width);

            if ($this->nbphoto > 0) {
                $out.= $photos;
                $image_available = true;
            }
        }

        if (! $image_available)
        {
            $out.= '<div class="photoref">'.img_picto('', $default_image).'</div>';
        }

        $out.= '</div>';

        return $out;
    }

    /**
     * Create a document onto disk according to template module.
     *
     * @param      string     $model     Force template to use ('' to not force)
     * @return     int                   0 if KO, 1 if OK
     */
    public function generateDocument($model)
    {
        global $conf, $user, $langs;

        // Get parameters
        $hidedetails = (GETPOST('hidedetails', 'int') ? GETPOST('hidedetails', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0));
        $hidedesc = (GETPOST('hidedesc', 'int') ? GETPOST('hidedesc', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0));
        $hideref = (GETPOST('hideref', 'int') ? GETPOST('hideref', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0));

        // Save last template used to generate document
        if ($model) {
            $this->setDocModel($user, $model);
            $this->model_pdf = $model;
        }

        // Define output language
        $outputlangs = $langs;
        $newlang = '';
        if ($conf->global->MAIN_MULTILANGS && empty($newlang) && ! empty($_REQUEST['lang_id']))
            $newlang = $_REQUEST['lang_id'];
        if ($conf->global->MAIN_MULTILANGS && empty($newlang))
            $newlang = $this->thirdparty->default_lang;
        if (! empty($newlang)) {
            $outputlangs = new Translate('', $conf);
            $outputlangs->setDefaultLang($newlang);
        }

        // Model to use
        if (! dol_strlen($model))
        {
            if (! empty($conf->global->{$this->doc_model_constant_name}))
            {
                $model = $conf->global->{$this->doc_model_constant_name};
            }
            else
            {
                $model = 'azur';
            }
        }

        // Get model path
        $modelpath = rtrim($this->doc_model_template_path, '/').'/';
        $dirmodels = array(
            $modelpath => dol_buildpath($modelpath)
        );

        foreach ($dirmodels as $path => $dir)
        {
            foreach(array('doc', 'pdf') as $prefix)
            {
                if (file_exists($dir.$prefix.'_'.$model.'.modules.php')) {
                    $modelpath = $path;
                    break 2;
                }
            }
        }

        // Generate document
        $result = @$this->commonGenerateDocument($modelpath, $model, $outputlangs, $hidedetails, $hidedesc, $hideref);
        if ($result <= 0) {
            setEventMessages($this->error, $this->errors, 'errors');
        }

        return $result;
    }

    /**
     * Delete document from disk.
     *
     * @return     int     0 if KO, 1 if OK
     */
    public function deleteDocument()
    {
        global $conf, $langs;

        if ($this->id > 0)
        {
            require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

            $langs->load('other');
            $upload_dir = $conf->{$this->modulepart}->dir_output;
            $file = $upload_dir . '/' . GETPOST('file');
            $result = dol_delete_file($file, 0, 0, 0, $this);
            if ($result) {
                setEventMessages($langs->trans('FileWasRemoved', GETPOST('file')), null, 'mesgs');
            }
            else {
                setEventMessages($langs->trans('ErrorFailToDeleteFile', GETPOST('file')), null, 'errors');
            }

            return $result;
        }

        return 0;
    }
}
