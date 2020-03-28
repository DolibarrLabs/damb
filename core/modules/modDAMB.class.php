<?php

// Load DolibarrModules class
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

// Load module lib
dol_include_once('damb/lib/module.lib.php');

/**
 * Class to describe and enable module
 */
class modDAMB extends DolibarrModules
{
    /**
     * Constructor. Define names, constants, directories, boxes, permissions
     *
     * @param      DoliDB      $db      Database handler
     */
    public function __construct($db)
    {
        // Module configuration
        $this->db              = $db;
        $this->editor_name     = 'AXeL';
        $this->editor_url      = 'https://github.com/AXeL-dev';
        $this->numero          = 686577660;
        $this->rights_class    = 'damb';
        $this->family          = 'base';
        $this->module_position = 500;
        $this->name            = 'AdvancedModuleBuilder';
        $this->description     = 'AdvancedModuleBuilderDesc';
        $this->picto           = 'module.png@damb';
        $this->version         = '1.0.2';
        $this->const_name      = get_constant_name($this);
        $this->special         = 0;

        // Module parts (css, js, ...)
        $this->module_parts    = array(
            'css'   => array(),
            'js'    => array(),
            'hooks' => array('toprightmenu'),
            //'triggers' => 1,
        );

        // Config page
        $this->config_page_url = array('setup.php@damb');

        // Dependencies
        $this->need_dolibarr_version = array(3, 8);
        $this->phpmin                = array(4, 0);
        $this->depends               = array();
        $this->requiredby            = array();
        $this->conflictwith          = array();
        $this->langfiles             = array('damb@damb');

        // Constants
        global $user;
        add_constant($this, 'DAMB_AUTHOR_NAME', $user->lastname);
        add_constant($this, 'DAMB_AUTHOR_URL', '#');
        add_constant($this, 'DAMB_AUTHOR_EMAIL', $user->email);
        add_constant($this, 'DAMB_AUTHOR_DOLISTORE_URL', '#');
    }

    /**
     * Function called when module is enabled.
     * The init function add constants, boxes, permissions and menus
     * (defined in constructor) into Dolibarr database.
     * It also creates data directories
     *
     * @param string $options Options when enabling module ('', 'noboxes')
     * @return int 1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        // Load module tables
        //$result = $this->_load_tables('/damb/sql/');

        return $this->_init(array(), $options);
    }

    /**
     * Function called when module is disabled.
     * Remove from database constants, boxes and permissions from Dolibarr database.
     * Data directories are not deleted
     *
     * @param string $options Options when enabling module ('', 'noboxes')
     * @return int 1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        return $this->_remove(array(), $options);
    }
}
