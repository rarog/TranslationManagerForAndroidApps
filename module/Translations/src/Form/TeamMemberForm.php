<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Form;

use Zend\Form\Form;

class TeamMemberForm extends Form
{
    public function __construct($name = null)
    {
        // Ignore the name provided to the constructor.
        parent::__construct('teamMember');

        $this->setAttribute('method', 'post');

        // Creating the form elements.
        $this->add([
            'name' => 'user_id',
            'type' => 'hidden',
        ]);
        $this->add([
            'name' => 'team_id',
            'type' => 'hidden',
        ]);
        $this->add([
            'name'    => 'csrf_team_member',
            'options' => [
                'csrf_options' => [
                    'timeout' => null,
                ],
            ],
            'type'    => 'csrf',
        ]);
        $this->add([
            'attributes' => [
                'value' => _('Add'),
                'id'    => 'submit',
            ],
            'name'       => 'submit',
            'options' => [
                'glyphicon' => 'plus-sign',
            ],
            'type'       => 'submit',
        ]);
    }
}
