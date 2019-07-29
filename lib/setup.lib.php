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

if (! function_exists('load_default_actions'))
{
    /**
     * Load default actions
     *
     * @param   string   $action                    action parameter, e.: GETPOST('action', 'alpha')
     * @param   string   $num_model_const_name      constant name for numbering model, e.: 'MYMODULE_ADDON'
     * @param   string   $doc_model_type            document model type, e.: 'mytype'
     * @param   string   $doc_model_const_name      document model constant name, e.: 'MYMODULE_ADDON_PDF'
     * @param   string   $doc_model_template_path   document templates path, e.: 'mymodule/core/doc_models'
     * @param   string   $modulepart                name of folder used to store documents in dolibarr/documents, e.: 'mymodule'
     * @param   string   $object_class_name         object class name, e.: 'MyObject'
     * @param   string   $object_class_file         object class file, e.: 'mymodule/class/myobject.class.php'
     */
    function load_default_actions($action, $num_model_const_name = '', $doc_model_type = '', $doc_model_const_name = '', $doc_model_template_path = '', $modulepart = '', $object_class_name = '', $object_class_file = '')
    {
        global $conf, $db, $langs;

        // Load admin & functions lib
        require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
        require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

        // Set a constant
        if (preg_match('/^set_(.*)/', $action, $reg))
        {
            $code = $reg[1];
            $type = GETPOST('option_type', 'alpha');
            $value = (isset($_GET[$code]) || isset($_POST[$code]) ? ($type == 'date' ? dol_mktime(0, 0, 0, GETPOST($code.'month'), GETPOST($code.'day'), GETPOST($code.'year')) : GETPOST($code)) : (in_array($type, array('text', 'multiselect')) ? '' : 1));

            if (is_array($value)) {
                $value = (! empty($value) ? join(',', $value) : '');
            }

            if (dolibarr_set_const($db, $code, $value, 'chaine', 0, '', $conf->entity) > 0)
            {
                redirect($_SERVER["PHP_SELF"].'?mainmenu=home');
            }
            else
            {
                dol_print_error($db);
            }
        }

        // Delete a constant
        else if (preg_match('/^del_(.*)/', $action, $reg))
        {
            $code = $reg[1];

            if (dolibarr_del_const($db, $code, $conf->entity) > 0)
            {
                redirect($_SERVER["PHP_SELF"].'?mainmenu=home');
            }
            else
            {
                dol_print_error($db);
            }
        }

        // Update numbering model mask
        else if ($action == 'updateMask')
        {
            $maskconst = GETPOST('maskconst', 'alpha');
            $mask = GETPOST('mask', 'alpha');
            $error = 0;

            if ($maskconst) $res = dolibarr_set_const($db, $maskconst, $mask, 'chaine', 0, '', $conf->entity);

            if (! $res > 0) $error++;

            if (! $error)
            {
                setEventMessages($langs->trans('SetupSaved'), null, 'mesgs');
            }
            else
            {
                setEventMessages($langs->trans('Error'), null, 'errors');
            }
        }

        // Set/Activate a numbering model
        else if ($action == 'setmod')
        {
            $value = GETPOST('value', 'alpha');

            dolibarr_set_const($db, $num_model_const_name, $value, 'chaine', 0, '', $conf->entity);
        }

        // Activate a document model
        else if ($action == 'setdoc')
        {
            $value = GETPOST('value', 'alpha');

            $ret = addDocumentModel($value, $doc_model_type);
        }

        // Disable a document model
        else if ($action == 'deldoc')
        {
            $value = GETPOST('value', 'alpha');
            $ret = delDocumentModel($value, $doc_model_type);

            if ($ret > 0 && $conf->global->$doc_model_const_name == $value)
            {
                dolibarr_del_const($db, $doc_model_const_name, $conf->entity);
            }
        }

        // Set default document model
        else if ($action == 'setdefaultdoc')
        {
            $value = GETPOST('value', 'alpha');

            if (dolibarr_set_const($db, $doc_model_const_name, $value, 'chaine', 0, '', $conf->entity))
            {
                // The constant that was read before the new set
                // We therefore requires a variable to have a coherent view
                $conf->global->$doc_model_const_name = $value;
            }

            // activate model
            $ret = delDocumentModel($value, $doc_model_type);
            if ($ret > 0)
            {
                $ret = addDocumentModel($value, $doc_model_type);
            }
        }

        // Generate specimen document
        else if ($action == 'specimen')
        {
            $model = GETPOST('model', 'alpha');

            if (! empty($object_class_file) && ! empty($object_class_name)) {
                dol_include_once($object_class_file);
                $classname = $object_class_name;
            }
            else {
                $classname = 'stdClass';
            }

            $object = new $classname();

            if (method_exists($object, 'initAsSpecimen'))
            {
                $object->initAsSpecimen();
            }
            else
            {
                $object->doc_title     = 'SPECIMEN';
                $object->ref           = 'SPECIMEN';
                $object->specimen      = 1;
                $object->creation_date = time();
                $object->doc_lines     = array(
                    'Lorem ipsum' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit.',
                    'Aliquam tincidunt' => 'Aliquam tincidunt mauris eu risus.',
                    'Donec odio' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec odio. Quisque volutpat mattis eros. Nullam malesuada erat ut turpis. Suspendisse urna nibh, viverra non, semper suscipit, posuere a, pede.'
                );
            }

            // Search template files
            $error = 0;
            $dirmodels = array(
                dol_buildpath($doc_model_template_path)
            );

            foreach ($dirmodels as $dir)
            {
                $file = rtrim($dir, '/').'/pdf_'.$model.'.modules.php';
                if (file_exists($file))
                {
                    $error = 0;
                    require_once $file;

                    $classname = 'pdf_'.$model;
                    $module = new $classname($db);

                    // Generate document
                    if ($module->write_file($object, $langs) > 0)
                    {
                        redirect(DOL_URL_ROOT.'/document.php?modulepart='.$modulepart.'&file=SPECIMEN.pdf');
                    }
                    else
                    {
                        setEventMessages($module->error, null, 'errors');
                        dol_syslog($module->error, LOG_ERR);
                    }

                    break;
                }
                else
                {
                    $error++;
                }
            }

            if ($error)
            {
                setEventMessages($langs->trans('ErrorModuleNotFound'), null, 'errors');
                dol_syslog($langs->trans('ErrorModuleNotFound'), LOG_ERR);
            }
        }
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_num_models'))
{
    /**
     * Print numbering models
     *
     * @param   string   $template_path   numbering models template(s) path, e.: 'mymodule/core/num_models'
     * @param   string   $const_name      numbering models constant name, e.: 'MYMODULE_ADDON'
     */
    function print_num_models($template_path, $const_name)
    {
        global $db, $conf, $langs;

        echo '<table class="noborder allwidth">';
        echo '<tr class="liste_titre">';
        echo '<td>'.$langs->trans('Name').'</td>';
        echo '<td>'.$langs->trans('Description').'</td>';
        echo '<td class="nowrap">'.$langs->trans('Example').'</td>';
        echo '<td align="center" width="60">'.$langs->trans('Status').'</td>';
        echo '<td align="center" width="16">'.$langs->trans('ShortInfo').'</td>';
        echo '</tr>'."\n";

        clearstatcache();

        $dirmodels = array(
            dol_buildpath($template_path)
        );

        foreach ($dirmodels as $dir)
        {
            if (is_dir($dir))
            {
                $handle = opendir($dir);
                if (is_resource($handle))
                {
                    $var = true;

                    while (($file = readdir($handle)) !== false)
                    {
                        if (substr($file, dol_strlen($file)-3, 3) == 'php')
                        {
                            $file = substr($file, 0, dol_strlen($file)-4);

                            require_once rtrim($dir, '/').'/'.$file.'.php';

                            $classname = 'NumModel'.ucfirst($file);

                            $model = new $classname();

                            // Show models according to features level
                            if ($model->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) continue;
                            if ($model->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) continue;

                            if ($model->isEnabled())
                            {
                                $var = !$var;
                                echo '<tr '.$bc[$var].'><td>'.$model->nom."</td><td>\n";
                                echo $model->info();
                                echo '</td>';

                                // Show example of numbering model
                                echo '<td class="nowrap">';
                                $tmp = $model->getExample();
                                if (preg_match('/^Error/',$tmp)) echo '<div class="error">'.$langs->trans($tmp).'</div>';
                                elseif ($tmp == 'NotConfigured') echo $langs->trans($tmp);
                                else echo $tmp;
                                echo '</td>'."\n";

                                echo '<td align="center">';
                                if ($conf->global->$const_name == $file)
                                {
                                    echo img_picto($langs->trans('Activated'), 'switch_on');
                                }
                                else
                                {
                                    echo '<a href="'.$_SERVER["PHP_SELF"].'?action=setmod&amp;value='.$file.'">';
                                    echo img_picto($langs->trans('Disabled'), 'switch_off');
                                    echo '</a>';
                                }
                                echo '</td>';

                                // Info
                                $htmltooltip = $langs->trans('Version').': <b>'.$model->getVersion().'</b><br>';
                                $nextval = $model->getNextValue();
                                if ("$nextval" != $langs->trans('NotAvailable')) { // Keep " on nextval
                                    $htmltooltip.= $langs->trans('NextValue').': ';
                                    if ($nextval) {
                                        if (preg_match('/^Error/',$nextval) || $nextval == 'NotConfigured') {
                                            $nextval = $langs->trans($nextval);
                                        }
                                        $htmltooltip.= $nextval.'<br>';
                                    } else {
                                        $htmltooltip.= $langs->trans($model->error).'<br>';
                                    }
                                }

                                require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

                                $form = new Form($db);

                                echo '<td align="center">';
                                echo $form->textwithpicto('', $htmltooltip, 1, 0);
                                echo '</td>';

                                echo "</tr>\n";
                            }
                        }
                    }
                    closedir($handle);
                }
            }
        }

        echo "</table>\n<br>\n";
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_doc_models'))
{
    /**
     * Print document models
     *
     * @param   string   $template_path   document models template(s) path, e.: 'mymodule/core/doc_models'
     * @param   string   $type            document models type, e.: 'mytype'
     * @param   string   $const_name      document models constant name, e.: 'MYMODULE_ADDON'
     * @param   string   $picture         document models preview link picture, e.: 'picture.png@mymodule'
     */
    function print_doc_models($template_path, $type, $const_name, $picture)
    {
        global $db, $conf, $langs;

        // Load array def with activated templates
        $def = array();
        $sql = "SELECT nom";
        $sql.= " FROM ".MAIN_DB_PREFIX."document_model";
        $sql.= " WHERE type = '".$type."'";
        $sql.= " AND entity = ".$conf->entity;
        $resql = $db->query($sql);
        if ($resql)
        {
            $i = 0;
            $num_rows = $db->num_rows($resql);
            while ($i < $num_rows)
            {
                $array = $db->fetch_array($resql);
                array_push($def, $array[0]);
                $i++;
            }
        }
        else
        {
            dol_print_error($db);
        }

        echo '<table class="noborder allwidth">';
        echo '<tr class="liste_titre">';
        echo '<td>'.$langs->trans('Name').'</td>';
        echo '<td>'.$langs->trans('Description').'</td>';
        echo '<td align="center" width="60">'.$langs->trans('Status').'</td>';
        echo '<td align="center" width="60">'.$langs->trans('Default').'</td>';
        echo '<td align="center" width="38">'.$langs->trans('ShortInfo').'</td>';
        echo '<td align="center" width="38">'.$langs->trans('Preview').'</td>';
        echo '</tr>'."\n";

        clearstatcache();

        $dirmodels = array(
            dol_buildpath($template_path)
        );

        foreach ($dirmodels as $dir)
        {
            if (is_dir($dir))
            {
                $handle = opendir($dir);
                if (is_resource($handle))
                {
                    $var = true;

                    while (($file = readdir($handle)) !== false)
                    {
                        if (preg_match('/\.modules\.php$/i',$file) && preg_match('/^(pdf_|doc_)/',$file))
                        {
                            require_once rtrim($dir, '/').'/'.$file;

                            $classname = substr($file, 0, dol_strlen($file) - 12);

                            $model = new $classname($db);

                            // Show models according to features level
                            $modelqualified = 1;
                            if ($model->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) $modelqualified = 0;
                            if ($model->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) $modelqualified = 0;

                            if ($modelqualified)
                            {
                                $var = !$var;
                                echo '<tr '.$bc[$var].'><td width="100">'.$model->name."</td><td>\n";
                                if (method_exists($model, 'info')) echo $model->info($langs);
                                else echo $model->description;
                                echo '</td>';

                                // Active
                                if (in_array($model->name, $def))
                                {
                                    echo '<td align="center">'."\n";
                                    echo '<a href="'.$_SERVER["PHP_SELF"].'?action=deldoc&value='.$model->name.'">';
                                    echo img_picto($langs->trans('Enabled'), 'switch_on');
                                    echo '</a>';
                                    echo '</td>';
                                }
                                else
                                {
                                    echo '<td align="center">'."\n";
                                    echo '<a href="'.$_SERVER["PHP_SELF"].'?action=setdoc&value='.$model->name.'">'.img_picto($langs->trans('Disabled'), 'switch_off').'</a>';
                                    echo "</td>";
                                }

                                // Default
                                echo '<td align="center">';
                                if ($conf->global->$const_name == $model->name)
                                {
                                    echo img_picto($langs->trans('Default'), 'on');
                                }
                                else
                                {
                                    echo '<a href="'.$_SERVER["PHP_SELF"].'?action=setdefaultdoc&value='.$model->name.'" alt="'.$langs->trans('Default').'">'.img_picto($langs->trans('Disabled'), 'off').'</a>';
                                }
                                echo '</td>';

                                // Info
                                $htmltooltip = $langs->trans('Name').': '.$model->name;
                                $htmltooltip.= '<br>'.$langs->trans('Type').': '.($model->type?$model->type:$langs->trans('Unknown'));
                                if ($model->type == 'pdf')
                                {
                                    $htmltooltip.= '<br>'.$langs->trans('Width').'/'.$langs->trans('Height').': '.$model->page_largeur.'/'.$model->page_hauteur;
                                }
                                $htmltooltip.= '<br><br><u>'.$langs->trans('FeaturesSupported').':</u>';
                                $htmltooltip.= '<br>'.$langs->trans('Logo').': '.yn($model->option_logo, 1, 1);
                                $htmltooltip.= '<br>'.$langs->trans('MultiLanguage').': '.yn($model->option_multilang, 1, 1);
                                $htmltooltip.= '<br>'.$langs->trans('WatermarkOnDraft').': '.yn($model->option_draft_watermark, 1, 1);

                                require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

                                $form = new Form($db);

                                echo '<td align="center">';
                                echo $form->textwithpicto('', $htmltooltip, 1, 0);
                                echo '</td>';

                                // Preview
                                echo '<td align="center">';
                                if ($model->type == 'pdf')
                                {
                                    echo '<a href="'.$_SERVER["PHP_SELF"].'?action=specimen&model='.$model->name.'">'.img_object($langs->trans('Preview'), $picture).'</a>';
                                }
                                else
                                {
                                    echo img_object($langs->trans('PreviewNotAvailable'), 'generic');
                                }
                                echo '</td>';

                                echo "</tr>\n";
                            }
                        }
                    }
                    closedir($handle);
                }
            }
        }

        echo "</table>\n<br>\n";
    }
}

// --------------------------------------------------------------------

if (! function_exists('print_options'))
{
    /**
     * Print a table with options
     *
     * @param   array   $options   options array as [
     * array(
     *     'name'   (*) => 'MYMODULE_OPTION',
     *     'type'   (*) => 'switch', // or 'text', 'number', 'range', 'date', 'select', 'multiselect', 'color'
     *     'desc'   (*) => 'My option description', // description will be displayed on the left of the row
     *     'value'      => $conf->global->MYMODULE_OPTION, // or any specific value
     *     'values' (*) => array(0 => 'value 1', 1 => 'value 2'), // for type select & multiselect only
     *     'enabled'    => '$conf->module->enabled', // condition to enable option
     *     'use_ajax'   => true, // for type switch only
     *     'desc_right' => '', // used to display something else on the right of the description
     *     'width'      => '50%', // to make an input more large for example
     *     'size'       => 8, // useful for text inputs
     *     'min'        => 0, // work with number & range inputs
     *     'max'        => 100 // for number & range inputs too
     * )]
     * array keys with (*) are required
     */
    function print_options($options)
    {
        global $db, $conf, $langs, $bc;

        require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
        require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
        require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
        require_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';

        $form = new Form($db);
        $formother = new FormOther($db);

        echo '<table class="noborder allwidth">'."\n";

        // Table header
        echo '<tr class="liste_titre">'."\n";
        echo '<td>'.$langs->trans('Option').'</td>'."\n";
        echo '<td align="center" width="100">'.$langs->trans('Value').'</td>'."\n";
        echo '</tr>'."\n";

        // Tables rows
        $odd = true;
        foreach ($options as $option)
        {
            if (is_array($option))
            {
                if (! isset($option['enabled']) || empty($option['enabled']) || verifCond($option['enabled']))
                {
                    $odd = !$odd;
                    $desc_right = isset($option['desc_right']) ? $option['desc_right'] : '';
                    $width = isset($option['width']) ? $option['width'] : '350';
                    $value = isset($option['value']) ? $option['value'] : $conf->global->{$option['name']};

                    echo '<tr '.$bc[$odd].'><td>'.$langs->trans($option['desc']).$desc_right.'</td>'."\n";
                    echo '<td align="right" width="'.$width.'">'."\n";

                    // Switch
                    if ($option['type'] == 'switch')
                    {
                        if (isset($option['use_ajax']) && $option['use_ajax'] && ! empty($conf->use_javascript_ajax) && function_exists('ajax_constantonoff'))
                        {
                            echo ajax_constantonoff($option['name']);
                        }
                        else
                        {
                            if (empty($value))
                            {
                                echo '<a href="'.$_SERVER['PHP_SELF'].'?action=set_'.$option['name'].'">'.img_picto($langs->trans('Disabled'), 'switch_off').'</a>'."\n";
                            }
                            else
                            {
                                echo '<a href="'.$_SERVER['PHP_SELF'].'?action=del_'.$option['name'].'">'.img_picto($langs->trans('Enabled'), 'switch_on').'</a>'."\n";
                            }
                        }
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    else
                    {
                        echo '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">'."\n";
                        echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'" />'."\n";
                        echo '<input type="hidden" name="action" value="set_'.$option['name'].'" />'."\n";
                        echo '<input type="hidden" name="mainmenu" value="home" />'."\n";

                        // Text, number, range
                        if (in_array($option['type'], array('text', 'number', 'range')))
                        {
                            $class = 'flat';
                            if ($option['type'] == 'range') {
                                $class .= ' valignmiddle';
                            }
                            echo '<input type="'.$option['type'].'"'.(isset($option['min']) ? ' min="'.$option['min'].'"' : '').(isset($option['max']) ? ' max="'.$option['max'].'"' : '').(isset($option['size']) ? ' size="'.$option['size'].'"' : '').' class="'.$class.'" name="'.$option['name'].'" value="'.$value.'">'."\n";
                        }
                        // Date
                        else if ($option['type'] == 'date')
                        {
                            echo '<input type="hidden" name="option_type" value="date" />'."\n";
                            echo $form->select_date($value, $option['name'], 0, 0, 1, '', 1, 1, 1);
                        }
                        // Select
                        else if ($option['type'] == 'select')
                        {
                            echo $form->selectarray($option['name'], $option['values'], $value, 0, 0, 0, '', 1);
                        }
                        // Multi select
                        else if ($option['type'] == 'multiselect')
                        {
                            if (! is_array($value)) {
                                $value = explode(',', $value);
                            }
                            echo '<input type="hidden" name="option_type" value="multiselect" />'."\n";
                            echo $form->multiselectarray($option['name'], $option['values'], $value, 0, 0, '', 1, '60%');
                        }
                        // Color
                        else if ($option['type'] == 'color')
                        {
                            echo $formother->selectColor(colorArrayToHex(colorStringToArray($value, array()), ''), $option['name']);
                        }

                        echo '&nbsp;&nbsp;<input type="submit" class="button" value="'.$langs->trans('Modify').'">&nbsp;&nbsp;'."\n";
                        echo "</form>\n";
                    }

                    echo "</td>\n</tr>\n";
                }
            }
        }

        echo "</table>\n<br>\n";
    }
}
