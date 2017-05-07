<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Form;

use Zend\Form\Form;

class TeamForm extends Form
{
    public function __construct($name = null)
    {
        // Ignore the name provided to the constructor.
        parent::__construct('app');

        $this->setAttribute('method', 'post');

        // Creating the form elements.
        $this->add([
            'name' => 'id',
            'type' => 'hidden',
        ]);
        $this->add([
            'attributes' => [
                'id' => 'name',
                'maxlength' => 255,
            ],
            'name'       => 'name',
            'options'    => [
                'column-size'      => 'sm-9',
                'label'            => _('Name'),
                'label_attributes' => [
                    'class' => 'col-sm-3',
                ],
            ],
            'type'       => 'text',
        ]);
        $this->add(array(
            'type' => 'Csrf',
            'name' => 'csrf_team',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => null,
                )
            )
        ));
        $this->add([
            'attributes' => [
                'value' => _('Save'),
                'id'    => 'submit',
            ],
            'name'       => 'submit',
            'options'    => [
                'column-size' => 'sm-9 col-sm-offset-3',
            ],
            'type'       => 'submit',
        ]);
    }
}
