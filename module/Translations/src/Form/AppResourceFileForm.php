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

class AppResourceFileForm extends Form
{
    public function __construct($name = null)
    {
        // Ignore the name provided to the constructor.
        parent::__construct('appResourceFile');

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
                'help-block'       => _('It\'s strongly advised not to change this value in existing app resources.'),
                'label'            => _('Name'),
                'label_attributes' => [
                    'class' => 'col-sm-3',
                ],
            ],
            'type'       => 'text',
        ]);
        $this->add([
            'name'    => 'csrf_app_resource_file',
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
