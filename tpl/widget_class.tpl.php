<?php

// Load ModeleBoxes class
require_once DOL_DOCUMENT_ROOT.'/core/boxes/modules_boxes.php';

// Load widget lib
dol_include_once('${module_folder}/lib/widget.lib.php');

/**
 * Class to manage the widget box
 *
 * Warning: for the box to be detected correctly by dolibarr,
 * the filename should be the lowercase classname
 */
class ${widget_class_name} extends ModeleBoxes
{
    /**
     * Constructor
     * 
     * @param     $db         Database handler
     * @param     $param      More widget options
     */
    public function __construct($db, $param = '')
    {
        global $langs;

        // Load language files
        $langs->load('${widget_lang_file}');

        // Widget configuration
        $this->db                = $db;
        $this->boxcode           = '';
        $this->boxlabel          = $langs->trans('${widget_label}');
        $this->boximg            = '${widget_image}';
        $this->position          = ${widget_position};
        $this->enabled           = ${enable_widget};
        $this->depends           = array();
        $this->info_box_head     = array();
        $this->info_box_contents = array();
    }

    /**
     * Load data into info_box_contents array to show array later. Called by Dolibarr before displaying the box.
     *
     * @param int $max Maximum number of records to load
     * @return void
     */
    public function loadBox($max = 5)
    {
        set_title($this, '${widget_title}');

        add_content($this, 'Add some content here..');
    }
}
