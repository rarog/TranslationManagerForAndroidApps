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
                'fontAwesome' => 'plus-circle fa-fw',
            ],
            'type'       => 'submit',
        ]);
    }
}
