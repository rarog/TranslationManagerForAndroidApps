<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use RuntimeException;
use Translations\Form\DeleteHelperForm;
use Translations\Form\TeamForm;
use Translations\Model\Team;
use Translations\Model\TeamTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TeamMemberController extends AbstractActionController
{
    /**
     * @var TeamTable
     */
    private $table;

    /**
     * Constructor
     *
     * @param TeamTable $table
     */
    public function __construct(TeamTable $table)
    {
        $this->table = $table;
    }

    /**
     * Team member add action
     *
     * @throws RuntimeException
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
    }

    /**
     * Team member delete action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
    }

    /**
     * Team member overview action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return [
            'teamMembers' => $this->table->fetchAll(),
        ];
    }
}
