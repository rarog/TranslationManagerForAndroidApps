<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application;

use Zend\Mvc\MvcEvent;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Session\Validator;

class Module
{
    const VERSION = '0.2-dev';

    /**
     * @var Container
     */
    private $userSettings;

    /**
     * Sets up listeners, that shouldn't be initialised via config.
     *
     * @param MvcEvent $e
     */
    private function bootstrapLateListeners(MvcEvent $e)
    {
        $application  = $e->getApplication();
        $eventManager = $application->getEventManager();
        $serviceManager = $application->getServiceManager();

        // Sets up the redirection strategy
        $setupAwareRedirectStrategy = $serviceManager->get('SetupAwareRedirectStrategy');
        $setupAwareRedirectStrategy->attach($eventManager);

        // Sets up the RBAC listener
        $rbacListener = $serviceManager->get('RbacListener');
        $rbacListener->attach($eventManager);
    }

    /**
     * Sets up the session
     *
     * @param MvcEvent $e
     */
    private function bootstrapSession(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $sharedManager = $e->getApplication()->getEventManager()->getSharedManager();
        $session = $serviceManager->get(SessionManager::class);

        try {
            $session->start();
        } catch (\Exception $e) {
            session_unset();
            $session->start();
        }

        $container = new Container('initialized');
        if (!isset($container->init)) {
            $request = $serviceManager->get('Request');

            $session->regenerateId(true);
            $container->init          = 1;
            $container->remoteAddr    = $request->getServer()->get('REMOTE_ADDR');
            $container->httpUserAgent = $request->getServer()->get('HTTP_USER_AGENT');

            $config = $serviceManager->get('Config');
            if (!isset($config['session'])) {
                return;
            }

            $sessionConfig = $config['session'];
            if (isset($sessionConfig['validators'])) {
                $chain = $session->getValidatorChain();

                foreach ($sessionConfig['validators'] as $validator) {
                    switch ($validator) {
                        case Validator\HttpUserAgent::class:
                            $validator = new $validator($container->httpUserAgent);
                            break;
                        case Validator\RemoteAddr::class:
                            $validator  = new $validator($container->remoteAddr);
                            break;
                        default:
                            $validator = new $validator();
                    }

                    $chain->attach('session.validate', array($validator, 'isValid'));
                }
            }
        }
    }

    /**
     * Sets up the translator
     *
     * @param MvcEvent $e
     */
    private function bootstrapTranslator(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();

        $translatorCache = null;
        $config = $serviceManager->get('Config');
        if (isset($config) &&
            isset($config['settings']) &&
            isset($config['settings']['translator_cache']) &&
            !empty($cacheName = trim($config['settings']['translator_cache'])) &&
            $serviceManager->has($cacheName)) {
            try {
                $translatorCache = $serviceManager->get($cacheName);
            } catch (\Exception $e) {
                $translatorCache = null;
            }
        }

        $translator = $serviceManager->get('MvcTranslator');

        if ($this->userSettings) {
            $translator->setLocale($this->userSettings->locale);
        }

        $translator->setFallbackLocale(\Locale::getPrimaryLanguage($translator->getLocale()));
        if (!is_null($translatorCache)) {
            $translator->setCache($translatorCache);
        }
        \Zend\Validator\AbstractValidator::setDefaultTranslator($translator);
    }

    /**
     * Sets up cached user settings
     *
     * @param MvcEvent $e
     */
    private function bootstrapUserSettings(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $auth = $serviceManager->get('zfcuser_auth_service');

        if ($auth->hasIdentity()) {
            $this->userSettings = new Container('userSettings');

            if (isset($this->userSettings->init)) {
                return;
            }

            $userSettingsTable = $serviceManager->get(\Translations\Model\UserSettingsTable::class);
            try {
                $userSettings = $userSettingsTable->getUserSettings($auth->getIdentity()->getId());
            } catch (\RuntimeException $e) {
                $this->userSettings = null;
                return;
            }

            $this->userSettings->exchangeArray($userSettings->getArrayCopy());
            $this->userSettings->init = 1;
        }
    }

    /**
     * Returns the module config
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Returns the service config
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                SessionManager::class => function ($container) {
                    $config = $container->get('config');
                    if (!isset($config['session'])) {
                        $sessionManager = new SessionManager();
                        Container::setDefaultManager($sessionManager);
                        return $sessionManager;
                    }

                    $session = $config['session'];

                    $sessionConfig = null;
                    if (isset($session['config'])) {
                        $class = isset($session['config']['class'])
                        ?  $session['config']['class']
                        : SessionConfig::class;

                        $options = isset($session['config']['options'])
                        ?  $session['config']['options']
                        : [];

                        $sessionConfig = new $class();
                        $sessionConfig->setOptions($options);
                    }

                    $sessionStorage = null;
                    if (isset($session['storage'])) {
                        $class = $session['storage'];
                        $sessionStorage = new $class();
                    }

                    $sessionSaveHandler = null;
                    if (isset($session['save_handler'])) {
                        // class should be fetched from service manager
                        // since it will require constructor arguments
                        $sessionSaveHandler = $container->get($session['save_handler']);
                    }

                    $sessionManager = new SessionManager(
                        $sessionConfig,
                        $sessionStorage,
                        $sessionSaveHandler
                    );

                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                },
            ],
        ];
    }

    /**
     * Bootstrap event
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $this->bootstrapSession($e);
        $this->bootstrapUserSettings($e);
        $this->bootstrapTranslator($e);
        $this->bootstrapLateListeners($e);
    }
}
