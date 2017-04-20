<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application\Listener;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class SetupListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->getSharedManager()->attach('Setup\Controller\SetupController', 'userCreated', function ($e) {
            $this->onUserCreated($e);
        }, $priority);
    }

    public function detach(EventManagerInterface $events)
    {
        // Not sure, if anything needs to be done here.
    }

    protected function onUserCreated(EventInterface $event)
    {
        $user = $event->getParam('user', null);
        if ($user instanceof \ZfcUser\Entity\UserInterface) {
            // TODO: React to user creation during setup and do something with it.
        }
    }
}