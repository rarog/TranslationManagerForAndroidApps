<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application;

class Module
{
    const VERSION = '0.1-dev';

    public function bootstrapSession(\Zend\Mvc\MvcEvent $e)
    {
        $session = $e->getApplication()
                     ->getServiceManager()
                     ->get('Zend\Session\SessionManager');

        try {
            $session->start();
        } catch (\Exception $e) {
            session_unset();
            $session->start();
        }

        $container = new \Zend\Session\Container('initialized');
        if (!isset($container->init)) {
            $serviceManager = $e->getApplication()
                                ->getServiceManager();
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
                        case 'Zend\Session\Validator\HttpUserAgent':
                            $validator = new $validator($container->httpUserAgent);
                            break;
                        case 'Zend\Session\Validator\RemoteAddr':
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

    public function bootstrapTranslator(\Zend\Mvc\MvcEvent $e)
    {
        $serviceManager = $e->getApplication()
                            ->getServiceManager();

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

        if (!is_null($translatorCache)) {
            $translator = $serviceManager->get('translator');
            $translator->setCache($translatorCache);
        }
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function onBootstrap(\Zend\Mvc\MvcEvent $e)
    {
        $this->bootstrapSession($e);
        $this->bootstrapTranslator($e);
    }
}
