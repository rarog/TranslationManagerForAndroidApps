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

use Application\Model\UserSettingsTable;
use Translations\Model\TeamTable;
use UserRbac\Mapper\UserRoleLinkerMapper;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use ZfcUser\Mapper\User as UserMapper;

class SetupListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * @var UserMapper
     */
    private $userMapper;

    /**
     * @var UserRoleLinkerMapper
     */
    private $userRoleLinkerMapper;

    /**
     * @var TeamTable
     */
    private $teamTable;

    /**
     * @var UserSettingsTable
     */
    private $userSettingsTable;

    /**
     * Constructor
     *
     * @param UserMapper $userMapper
     * @param UserRoleLinkerMapper $userRoleLinkerMapper
     * @param TeamTable $teamTable
     * @param UserSettingsTable $userSettingsTable
     */
    public function __construct(
        UserMapper $userMapper,
        UserRoleLinkerMapper $userRoleLinkerMapper,
        TeamTable $teamTable,
        UserSettingsTable $userSettingsTable
    ) {
        $this->userMapper = $userMapper;
        $this->userRoleLinkerMapper = $userRoleLinkerMapper;
        $this->teamTable = $teamTable;
        $this->userSettingsTable = $userSettingsTable;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->getSharedManager()->attach(
            'Setup\Controller\SetupController',
            'userCreated',
            [$this, 'onUserCreated'],
            $priority
        );
    }

    /**
     * Handler for userCreated event
     *
     * @param EventInterface $event
     */
    public function onUserCreated(EventInterface $event)
    {
        $user = $event->getParam('user', null);
        if ($user instanceof \ZfcUser\Entity\UserInterface) {
            // Enable the user for login.
            $user->setState(1);
            $this->userMapper->update($user);

            // Giving the new user the admin role.
            $userLinker = new \UserRbac\Entity\UserRoleLinker($user, 'admin');
            $this->userRoleLinkerMapper->insert($userLinker);

            // Creating the first team.
            $team = new \Translations\Model\Team([
                'name' => 'Default team', // Don't translate here, just create English name.
            ]);
            $team = $this->teamTable->saveTeam($team);

            // Give the new user the current setup locale and newly created team.
            $setupContainer = new \Zend\Session\Container('setup');
            $userSettings = new \Application\Model\UserSettings([
                'user_id' => $user->getId(),
                'locale'  => $setupContainer->currentLanguage,
                'team_id' => $team->id,
            ]);
            $this->userSettingsTable->saveUserSettings($userSettings);
        }
    }
}
