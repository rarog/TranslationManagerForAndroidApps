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

class AppResourceForm extends Form
{
    public function __construct($name = null)
    {
        // Ignore the name provided to the constructor.
        parent::__construct('appResource');

        $this->setAttribute('method', 'post');

        // Creating the form elements.
        $this->add([
            'name' => 'id',
            'type' => 'hidden',
        ]);
        $this->add([
            'name' => 'app_id',
            'type' => 'hidden',
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
                'class'            => 'selectpicker',
                'data-live-search' => 'true',
                'id'               => 'locale',
            ],
            'name'       => 'locale',
            'options'    => [
                'column-size'      => 'sm-9',
                'empty_option'     => _('Please choose a locale'),
                'help-block'       => _('Default values should be universal like "en" or "en_US"'),
                'label'            => _('Locale'),
                'label_attributes' => [
                    'class' => 'col-sm-3',
                ],
            ],
            'type'       => 'select',
        ]);
        $this->add([
            'attributes' => [
                'id'        => 'description',
                'maxlength' => 255,
            ],
            'name'       => 'description',
            'options'    => [
                'column-size'      => 'sm-9',
                'label'            => _('Description'),
                'label_attributes' => [
                    'class' => 'col-sm-3',
                ],
            ],
            'type'       => 'text',
        ]);
        $this->add([
            'name'    => 'csrf_app_resource',
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
