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

class AppForm extends Form
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
                'id' => 'team_id',
            ],
            'name'       => 'team_id',
            'options'    => [
                'column-size'      => 'sm-9',
                'help-block'       => _('It\'s strongly advised not to change this value in existing apps.'),
                'label'            => _('Team'),
                'label_attributes' => [
                    'class' => 'col-sm-3',
                ],
            ],
            'type'       => 'select',
        ]);
        $this->add([
            'attributes' => [
                'id'        => 'name',
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
                'id'        => 'path_to_res_folder',
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
        $this->add([
            'attributes' => [
                'id'        => 'git_repository',
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
                'id'        => 'git_username',
                'maxlength' => 255,
            ],
            'name'       => 'git_username',
            'options'    => [
                'column-size'      => 'sm-9',
                'help-block'       => _('Username for git repository access'),
                'label'            => _('Git username'),
                'label_attributes' => [
                    'class' => 'col-sm-3',
                ],
            ],
            'type'       => 'text',
        ]);
        $this->add([
            'attributes' => [
                'id'        => 'git_password',
                'maxlength' => 255,
                'placeholder' => '●●●●●',
            ],
            'name'       => 'git_password',
            'options'    => [
                'column-size'      => 'sm-9',
                'help-block'       => _('Password for git repository access. Leave empty to keep old value.'),
                'label'            => _('Git password'),
                'label_attributes' => [
                    'class' => 'col-sm-3',
                ],
            ],
            'type'       => 'password',
        ]);
        $this->add([
            'attributes' => [
                'id'        => 'git_password_delete',
            ],
            'name'       => 'git_password_delete',
            'options'    => [
                'unchecked_value'    => 1,
                'column-size'        => 'sm-9 col-sm-offset-3',
                'label'              => _('Delete git password'),
                'unchecked_value'    => 0,
                'use_hidden_element' => true,
            ],
            'type'       => 'checkbox',
        ]);
        $this->add([
            'attributes' => [
                'id'        => 'git_user',
                'maxlength' => 255,
            ],
            'name'       => 'git_user',
            'options'    => [
                'column-size'      => 'sm-9',
                'help-block'       => _('User for git commits'),
                'label'            => _('Git user'),
                'label_attributes' => [
                    'class' => 'col-sm-3',
                ],
            ],
            'type'       => 'text',
        ]);
        $this->add([
            'attributes' => [
                'id'        => 'git_email',
                'maxlength' => 255,
            ],
            'name'       => 'git_email',
            'options'    => [
                'column-size'      => 'sm-9',
                'help-block'       => _('Email for git commits'),
                'label'            => _('Git email'),
                'label_attributes' => [
                    'class' => 'col-sm-3',
                ],
            ],
            'type'       => 'text',
        ]);
        $this->add([
            'name'    => 'csrf_app',
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
                'fontAwesome' => 'floppy-o fa-fw',
            ],
            'type'       => 'submit',
        ]);
    }
}
