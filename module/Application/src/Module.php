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
    const VERSION = '0.1-dev';

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
        $rbacListener = $serviceManager->get('RbacListener');
        $rbacListener->attach($eventManager);
    }

    /**
     * Sets up the redirection strategy
     *
     * @param MvcEvent $e
     */
    private function bootstrapRedirectionStrategy(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $listener = $serviceManager->get(\ZfcRbac\View\Strategy\RedirectStrategy::class);
        $listener->attach($e->getApplication()->getEventManager());
    }

    /**
     * Sets up the session
     *
     * @param MvcEvent $e
     */
    private function bootstrapSession(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
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
        if (!is_null($translatorCache)) {
            $translator->setCache($translatorCache);
        }
        \Zend\Validator\AbstractValidator::setDefaultTranslator($translator);
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
        $this->bootstrapTranslator($e);
        $this->bootstrapRedirectionStrategy($e);
        $this->bootstrapLateListeners($e);
    }
}
