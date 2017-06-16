<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Form;

use Zend\Form\Form;

class SyncExportForm extends Form
{
    public function __construct($name = null)
    {
        // Ignore the name provided to the constructor.
        parent::__construct('sync_export');

        $this->setAttribute('method', 'post');

        // Creating the form elements.
        $this->add([
            'name'       => 'confirm_deletion',
            'options'    => [
                'checked_value'      => 1,
                'unchecked_value'    => '',
                'column-size'  => 'sm-12',
                'label'        => _('Delete resources not present in set'),
            ],
            'type'       => 'checkbox',
        ]);
        $this->add([
            'name'    => 'csrf_sync_export',
            'options' => [
                'csrf_options' => [
                    'timeout' => null,
                ],
            ],
            'type'    => 'csrf',
        ]);
        $this->add([
            'attributes' => [
                'value' => _('Export'),
                'id'    => 'submit',
            ],
            'name'       => 'submit',
            'options'    => [
                'column-size' => 'sm-12',
                'glyphicon'   => 'export',
            ],
            'type'       => 'submit',
        ]);
    }
}
