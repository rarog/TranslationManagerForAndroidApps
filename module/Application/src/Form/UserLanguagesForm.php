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

namespace Application\Form;

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
            'attributes' => [
                'class'            => 'selectpicker',
                'data-live-search' => 'true',
                'id'               => 'languages',
                'multiple'         => 'multiple',
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
                'fontAwesome' => 'floppy-o fa-fw',
            ],
            'type'       => 'submit',
        ]);
    }
}
