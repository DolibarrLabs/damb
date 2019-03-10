<?php

/**
 * ActionsDAMB class (hooks manager)
 */

class ActionsDAMB
{
    /**
     * Overloading the printTopRightMenu function
     *
     * @param   array()         $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          &$action        Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
    public function printTopRightMenu($parameters, &$object, &$action, $hookmanager)
    {
        global $langs, $user;

        $error = 0; // Error counter

        // Do something only for the context 'toprightmenu'
        if (in_array('toprightmenu', explode(':', $parameters['context'])))
        {
            if ($user->admin)
            {
                // Builder shortcut
                $langs->load('damb@damb');

                $title = $langs->trans('AdvancedModuleBuilder');
                $text = '<a href="'.dol_buildpath('damb/builder/new.php', 1).'">';
                $dol_version = explode('.', DOL_VERSION);

                if ((int)$dol_version[0] >= 6) {
                    $text.= '<span class="fa fa-rocket atoplogin"></span>';
                }
                else {
                    $text.= img_picto($title, 'object_module.png@damb');
                }

                $text.= '</a>';
                $this->resprints = @Form::textwithtooltip('', $title, 2, 1, $text, 'login_block_elem', 2);
            }
        }

        if (! $error)
        {
            return 0; // or return 1 to replace standard code
        }
        else
        {
            $this->errors[] = 'Could not add damb shortcut to the top right menu';
            return -1;
        }
    }
}
