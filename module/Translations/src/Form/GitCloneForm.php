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

class GitCloneForm extends Form
{
    public function __construct($name = null)
    {
        // Ignore the name provided to the constructor.
        parent::__construct('git_clone');

        $this->setAttribute('method', 'post');

        // Creating the form elements.
        $this->add([
            'name'       => 'confirm_deletion',
            'options'    => [
                'checked_value'      => 1,
                'unchecked_value'    => '',
                'column-size'  => 'sm-12',
                'label'        => _('Confirm deletion of all content inside the folder before cloning'),
            ],
            'type'       => 'checkbox',
        ]);
        $this->add([
            'name'    => 'csrf_git_clone',
            'options' => [
                'csrf_options' => [
                    'timeout' => null,
                ],
            ],
            'type'    => 'csrf',
        ]);
        $this->add([
            'attributes' => [
                'class' => 'btn-default',
                'id'    => 'back',
                'type'  => 'submit',
                'value' => 'back',
            ],
            'name'       => 'back',
            'options'    => [
                'button-group' => 'group-1',
                'glyphicon'    => 'remove',
                'label'        => _('Back'),
            ],
            'type'       => 'button',
        ]);
        $this->add([
            'attributes' => [
                'class' => 'btn-warning',
                'id'    => 'clone',
                'type'  => 'submit',
                'value' => 'clone',
            ],
            'name'       => 'clone',
            'options'    => [
                'column-size'  => 'sm-12',
                'button-group' => 'group-1',
                'glyphicon'    => 'ok',
                'label'        => _('Clone'),
            ],
            'type'       => 'button',
        ]);
    }
}
