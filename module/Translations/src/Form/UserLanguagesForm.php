<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Form;

use Zend\Form\Form;

class UserLanguagesForm extends Form
{
    public function __construct($name = null)
    {
        // Ignore the name provided to the constructor.
        parent::__construct('userLanguages');

        $this->setAttribute('method', 'post');

        // Creating the form elements.
        $this->add([
            'name' => 'user_id',
            'type' => 'hidden',
        ]);
        $this->add([
            'attributes' => [
                'id'       => 'languages',
                'multiple' => 'multiple',
            ],
            'name'       => 'languages',
            'options'    => [
                'column-size'      => 'sm-9',
                'label'            => _('Languages'),
                'label_attributes' => [
                    'class' => 'col-sm-3',
                ],
                'value' => null,
            ],
            'type'       => 'select',
        ]);
        $this->add([
            'name'    => 'csrf_user_languages',
            'options' => [
                'csrf_options' => [
                    'timeout' => null,
                ],
            ],
            'type'    => 'csrf',
        ]);
        $this->add([
            'attributes' => [
                'value' => _('Save'),
                'id'    => 'submit',
            ],
            'name'       => 'submit',
            'options'    => [
                'column-size' => 'sm-9 col-sm-offset-3',
                'glyphicon'   => 'floppy-save',
            ],
            'type'       => 'submit',
        ]);
    }
}
