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
            'name'    => 'name',
            'type'    => 'text',
            'options' => [
                'label' => _('Name'),
            ],
        ]);
        $this->add([
            'name'    => 'git_repository',
            'type'    => 'text',
            'options' => [
                'label' => _('Git repository'),
            ],
        ]);
        $this->add([
            'name'    => 'path_to_res_folder',
            'type'    => 'text',
            'options' => [
                'label' => _('Path to "res" folder'),
            ],
        ]);
        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => _('Save'),
                'id'    => 'submit',
            ],
        ]);
    }
}
