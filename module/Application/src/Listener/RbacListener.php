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

namespace Application\Listener;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use ZfcRbac\Service\AuthorizationServiceInterface;

class RbacListener implements ListenerAggregateInterface
{
    /**
     * @var AuthorizationServiceInterface
     */
    private $authorizationService;

    /**
     * @var callable
     */
    private $event;

    /**
     * Constructor
     *
     * @param AuthorizationServiceInterface $authorizationService
     */
    public function __construct(AuthorizationServiceInterface $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->event = function ($e) {
            return $this->onIsAllowed($e);
        };
        $events->getSharedManager()
            ->attach('Zend\View\Helper\Navigation\AbstractHelper', 'isAllowed', $this->event, $priority);
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
     * Handler for isAllowed event
     *
     * @param  EventInterface $event
     * @return bool|void
     */
    private function onIsAllowed(EventInterface $event)
    {
        $page = $event->getParam('page');

        if (!$page instanceof \Zend\Navigation\Page\AbstractPage) {
            return;
        }

        $permission = $page->getPermission();
        $event->stopPropagation();

        if (is_null($permission)) {
            return false;
        }

        return $this->authorizationService->isGranted($permission);
    }
}