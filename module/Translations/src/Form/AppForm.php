<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Form;

use Zend\Form\Form;

class AppForm extends Form
{
    public function __construct($name = null)
    {
        // We will ignore the name provided to the constructor
        parent::__construct('app');

        $this->setAttribute('method', 'post');

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

        $this->add([
            'attributes' => [
                'id' => 'git_repository',
                'maxlength' => 4096,
            ],
            'name'       => 'git_repository',
            'options'    => [
                'column-size'      => 'sm-9',
                'label'            => _('Git repository'),
                'label_attributes' => [
                    'class' => 'col-sm-3',
                ],
            ],
            'type'       => 'text',
        ]);

        $this->add([
            'attributes' => [
                'id' => 'path_to_res_folder',
                'maxlength' => 4096,
            ],
            'name'       => 'path_to_res_folder',
            'options'    => [
                'column-size'      => 'sm-9',
                'label'            => _('Path to "res" folder'),
                'label_attributes' => [
                    'class' => 'col-sm-3',
                ],
            ],
            'type'       => 'text',
        ]);

        $this->add(array(
            'type' => 'Csrf',
            'name' => 'csrf_app',
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
