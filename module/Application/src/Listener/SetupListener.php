<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application\Listener;

use Translations\Model\TeamTable;
use UserRbac\Mapper\UserRoleLinkerMapper;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class SetupListener implements ListenerAggregateInterface
{
    /**
     * @var UserRoleLinkerMapper
     */
    private $userRoleLinkerMapper;

    /**
     * @var TeamTable
     */
    private $teamTable;

    /**
     * @var callable
     */
    private $event;

    /**
     * Constructor
     *
     * @param UserRoleLinkerMapper $userRoleLinkerMapper
     * @param TeamTable $teamTable
     */
    public function __construct(UserRoleLinkerMapper $userRoleLinkerMapper, TeamTable $teamTable)
    {
        $this->userRoleLinkerMapper = $userRoleLinkerMapper;
        $this->teamTable = $teamTable;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->event = function ($e) {
            return $this->onUserCreated($e);
        };
        $events->getSharedManager()
            ->attach('Setup\Controller\SetupController', 'userCreated', $this->event, $priority);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::detach()
     */
    public function detach(EventManagerInterface $events)
    {
        $events->getSharedManager()->detach($this->event);
        unset($this->event);
    }

    /**
     * Handler for userCreated event
     *
     * @param EventInterface $event
     */
    private function onUserCreated(EventInterface $event)
    {
        $user = $event->getParam('user', null);
        if ($user instanceof \ZfcUser\Entity\UserInterface) {
            // Giving the new user the admin role.
            $userLinker = new \UserRbac\Entity\UserRoleLinker($user, 'admin');
            $this->userRoleLinkerMapper->insert($userLinker);

            // Creating the first team.
            $team = new \Translations\Model\Team([
                'team' => 'Default team', // Don't translate here, just create English name.
            ]);
            $team = $this->teamTable->saveTeam($team);
        }
    }
}
