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

class DeleteHelperForm extends Form
{
    public function __construct($name = null)
    {
        // Ignore the name provided to the constructor.
        parent::__construct('delete_helper');

        $this->setAttribute('method', 'post');

        // Creating the form elements.
        $this->add([
            'name'    => 'csrf_delete_helper',
            'options' => [
                'csrf_options' => [
                    'timeout' => null,
                ],
            ],
            'type'    => 'csrf',
        ]);
        $this->add([
            'attributes' => [
                'class' => 'btn-danger',
                'id'    => 'yes',
                'type'  => 'submit',
                'value' => 'true',
            ],
            'name'       => 'del',
            'options'    => [
                'column-size'  => 'sm-12',
                'button-group' => 'group-1',
                'glyphicon'    => 'ok',
                'label'        => _('Yes'),
            ],
            'type'       => 'button',
        ]);
        $this->add([
            'attributes' => [
                'class' => 'btn-success',
                'id'    => 'no',
                'type'  => 'submit',
                'value' => 'false',
            ],
            'name'       => 'delno',
            'options'    => [
                'button-group' => 'group-1',
                'glyphicon'    => 'remove',
                'label'        => _('No'),
            ],
            'type'       => 'button',
        ]);
    }
}
