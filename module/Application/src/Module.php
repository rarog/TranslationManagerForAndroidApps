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

namespace Application;

use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Zend\Cache\Storage\Adapter\AbstractAdapter;
use Zend\Console\Console;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature\SequenceFeature;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Session\Validator;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Exception\RuntimeException as SessionRuntimeException;
use Zend\Session\Validator\ValidatorInterface;
use Zend\Validator\AbstractValidator;
use Locale;
use RuntimeException;

class Module implements BootstrapListenerInterface, ConfigProviderInterface, ServiceProviderInterface
{
    const VERSION = '0.5-dev';

    /**
     * @var Container
     */
    protected $userSettings;

    /**
     * Sets up listeners, that shouldn't be initialised via config.
     *
     * @param EventInterface $e
     */
    protected function bootstrapLateListeners(EventInterface $e)
    {
        $application  = $e->getApplication();
        $eventManager = $application->getEventManager();
        $serviceManager = $application->getServiceManager();

        // Sets up the redirection strategy
        $setupAwareRedirectStrategy = $serviceManager->get(View\Strategy\SetupAwareRedirectStrategy::class);
        $setupAwareRedirectStrategy->attach($eventManager);

        // Sets up the RBAC listener
        $rbacListener = $serviceManager->get(Listener\RbacListener::class);
        $rbacListener->attach($eventManager);
    }

    /**
     * Sets up the session
     *
     * @param EventInterface $e
     */
    protected function bootstrapSession(EventInterface $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();

        $session = $serviceManager->get(SessionManager::class);

        try {
            $session->start();
        } catch (SessionRuntimeException $e) {
            session_unset();
            $session->start();
        }

        // Clearing user data after login.
        // Attaching directly to the according event without dedicated listener.
        $adapterChain = $serviceManager->get('ZfcUser\Authentication\Adapter\AdapterChain');
        $adapterChain->getEventManager()->attach('authenticate.success', function (EventInterface $e) {
            $container = new Container();
            $container->getManager()->getStorage()->clear('userSettings');
        });

        $container = new Container('initialized');

        if (isset($container->init)) {
            return;
        }

        $request = $serviceManager->get('Request');

        $session->regenerateId(true);
        $container->init = 1;
        $container->remoteAddr = $request->getServer()->get('REMOTE_ADDR');
        $container->httpUserAgent = $request->getServer()->get('HTTP_USER_AGENT');

        $config = ($serviceManager->has('config')) ? $serviceManager->get('config') : [];
        if (! is_array($config) ||
            ! array_key_exists('session', $config) ||
            ! is_array($config['session']) ||
            ! array_key_exists('validators', $config['session']) ||
            ! is_array($config['session']['validators']) ||
            (count($config['session']['validators']) === 0) ) {
            return;
        }

        $chain = $session->getValidatorChain();

        foreach ($config['session']['validators'] as $validator) {
            if (! ($validator instanceof ValidatorInterface) &&
                ! (
                    class_exists($validator) &&
                    ($implements = class_implements($validator)) &&
                    in_array(ValidatorInterface::class, $implements))
                ) {
                continue;
            }

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

            $chain->attach('session.validate', [$validator, 'isValid']);
        }
    }

    /**
     * Sets up the translator
     *
     * @param EventInterface $e
     */
    protected function bootstrapTranslator(EventInterface $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();

        $translatorCache = null;
        $config = ($serviceManager->has('config')) ? $serviceManager->get('config') : [];
        if (is_array($config) &&
            array_key_exists('settings', $config) &&
            is_array($config['settings']) &&
            array_key_exists('translator_cache', $config['settings']) &&
            is_string($config['settings']['translator_cache']) &&
            ! empty($cacheName = trim($config['settings']['translator_cache'])) &&
            $serviceManager->has($cacheName)) {
            try {
                $translatorCache = $serviceManager->get($cacheName);
                if (! $translatorCache instanceof AbstractAdapter) {
                    $translatorCache = null;
                }
            } catch (ContainerExceptionInterface $e) {
                $translatorCache = null;
            }
        }

        $translator = $serviceManager->get('MvcTranslator');

        if ($this->userSettings && $this->userSettings->locale) {
            $translator->getTranslator()->setLocale($this->userSettings->locale);
        }

        $translator->setFallbackLocale(Locale::getPrimaryLanguage($translator->getTranslator()->getLocale()));
        if (! is_null($translatorCache)) {
            $translator->getTranslator()->setCache($translatorCache);
        }
        AbstractValidator::setDefaultTranslator($translator);
    }

    /**
     * Sets up cached user settings
     *
     * @param EventInterface $e
     */
    protected function bootstrapUserSettings(EventInterface $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $auth = $serviceManager->get('zfcuser_auth_service');

        if ($auth->hasIdentity()) {
            $this->userSettings = new Container('userSettings');

            if (isset($this->userSettings->init)) {
                return;
            }

            $userSettingsTable = $serviceManager->get(Model\UserSettingsTable::class);
            try {
                $userSettings = $userSettingsTable->getUserSettings($auth->getIdentity()->getId());
            } catch (RuntimeException $e) {echo $e->getMessage();
                $this->userSettings = null;
                return;
            }

            $this->userSettings->exchangeArray($userSettings->getArrayCopy());
            $this->userSettings->init = 1;
        }
    }

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
     * @see \Zend\ModuleManager\Feature\ServiceProviderInterface::getServiceConfig()
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\UserTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\UserTableGateway::class);
                    $userMapper = $container->get('zfcuser_user_mapper');
                    return new Model\UserTable($tableGateway, $userMapper);
                },
                Model\UserTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $feature = new SequenceFeature('id', 'user_user_id_seq');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\User);
                    return new TableGateway('user', $dbAdapter, $feature, $resultSetPrototype);
                },
                Model\UserLanguagesTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\UserLanguagesTableGateway::class);
                    return new Model\UserLanguagesTable($tableGateway);
                },
                Model\UserLanguagesTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\UserLanguages);
                    return new TableGateway('user_languages', $dbAdapter, null, $resultSetPrototype);
                },
                Model\UserSettingsTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\UserSettingsTableGateway::class);
                    return new Model\UserSettingsTable($tableGateway);
                },
                Model\UserSettingsTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\UserSettings());
                    return new TableGateway('user_settings', $dbAdapter, null, $resultSetPrototype);
                },
                SessionManager::class => function (ContainerInterface $container) {
                    $config = $container->has('config') ? $container->get('config') : [];
                    if (! isset($config['session'])) {
                        $sessionManager = new SessionManager();
                        Container::setDefaultManager($sessionManager);
                        return $sessionManager;
                    }

                    $session = $config['session'];

                    $sessionConfig = null;
                    if (isset($session['config'])) {
                        $class = isset($session['config']['class'])
                            ? $session['config']['class']
                            : SessionConfig::class;

                        $options = isset($session['config']['options'])
                            ? $session['config']['options']
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
     * {@inheritDoc}
     * @see \Zend\ModuleManager\Feature\BootstrapListenerInterface::onBootstrap()
     */
    public function onBootstrap(EventInterface $e)
    {
        // In console all following initializations aren't needed.
        if (Console::isConsole()) {
            return;
        }

        $this->bootstrapSession($e);
        $this->bootstrapUserSettings($e);
        $this->bootstrapTranslator($e);
        $this->bootstrapLateListeners($e);
    }
}
