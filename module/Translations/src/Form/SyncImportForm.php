<?php
/**
 * Translation Manager for Android Apps
 *
 * PHP version 7
 *
 * @category  PHP
 * @package   TranslationManagerForAndroidApps
 * @author    Andrej Sinicyn <rarogit@gmail.com>
 * @copyright 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps
 */

namespace Translations\Form;

use Zend\Form\Form;

class SyncImportForm extends Form
{
    public function __construct($name = null)
    {
        // Ignore the name provided to the constructor.
        parent::__construct('sync_import');

        $this->setAttribute('method', 'post');

        // Creating the form elements.
        $this->add([
            'name'       => 'confirm_deletion',
            'options'    => [
                'checked_value'      => 1,
                'column-size'        => 'sm-12',
                'label'              => _('Delete resources not present in set'),
                'unchecked_value'    => 0,
                'use_hidden_element' => true,
            ],
            'type'       => 'checkbox',
        ]);
        $this->add([
            'name'    => 'csrf_sync_import_export',
            'options' => [
                'csrf_options' => [
                    'timeout' => null,
                ],
            ],
            'type'    => 'csrf',
        ]);
        $this->add([
            'attributes' => [
                'value' => _('Import'),
                'id'    => 'import_submit',
            ],
            'name'       => 'submit',
            'options'    => [
                'column-size' => 'sm-12',
                'glyphicon'   => 'import',
            ],
            'type'       => 'submit',
        ]);
    }
}
