<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Setup;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManagerInterface;

class Module implements ConfigProviderInterface, InitProviderInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ModuleManager\Feature\ConfigProviderInterface::getConfig()
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * {@inheritDoc}
     * @see \Zend\ModuleManager\Feature\InitProviderInterface::init()
     */
    public function init(ModuleManagerInterface $manager)
    {
        $events = $manager->getEventManager();

        $events->attach(ModuleEvent::EVENT_MERGE_CONFIG, [$this, 'onMergeConfig']);
    }

    /**
     * Event to modify merged configuration with user-set values
     *
     * @param ModuleEvent $e
     */
    public function onMergeConfig(ModuleEvent $e)
    {
        $configListener = $e->getConfigListener();
        $config = $configListener->getMergedConfig(false);

        // Setting ttl of the setup cache, which is used for internal session storage, if value is provided.
        // Minimum value = 60 seconds
        if (array_key_exists('setup', $config) && array_key_exists('setup_session_timeout', $config['setup']) && is_int($config['setup']['setup_session_timeout']) && array_key_exists('caches', $config) && array_key_exists('SetupCache', $config['caches']) && array_key_exists('options', $config['caches']['SetupCache'])) {
            $config['caches']['SetupCache']['options']['ttl'] = max(60, $config['setup']['setup_session_timeout']);
        }

        $configListener->setMergedConfig($config);
    }
}
