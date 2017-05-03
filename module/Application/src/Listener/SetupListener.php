<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application\Listener;

use UserRbac\Mapper\UserRoleLinkerMapper;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class SetupListener implements ListenerAggregateInterface
{
    protected $userRoleLinkerMapper;

    /**
     * Constructor
     *
     * @param UserRoleLinkerMapper $userRoleLinkerMapper
     */
    public function __construct(UserRoleLinkerMapper $userRoleLinkerMapper)
    {
        $this->userRoleLinkerMapper = $userRoleLinkerMapper;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->getSharedManager()->attach('Setup\Controller\SetupController', 'userCreated', function ($e) {
            $this->onUserCreated($e);
        }, $priority);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::detach()
     */
    public function detach(EventManagerInterface $events)
    {
        // Not sure, if anything needs to be done here.
    }

    /**
     * Handler for userCreated event
     *
     * @param EventInterface $event
     */
    protected function onUserCreated(EventInterface $event)
    {
        $user = $event->getParam('user', null);
        if ($user instanceof \ZfcUser\Entity\UserInterface) {
            $userLinker = new \UserRbac\Entity\UserRoleLinker($user, 'admin');
            $this->userRoleLinkerMapper->insert($userLinker);
        }
    }
}
