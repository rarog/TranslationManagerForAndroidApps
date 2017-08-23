<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application\Factory\Log;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class LoggerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $logger = new \Zend\Log\Logger([
            'processors' => [
                ['name' => 'backtrace'],
            ],
        ]);

        $logLevel = 3; // Default log level 3 = ERR

        // Set log level from config if present.
        $config = $container->get('Config');
        if (isset($config['settings']) && isset($config['settings']['log_level']) && is_int($config['settings']['log_level'])) {
            $logLevel = $config['settings']['log_level'];
        }

        $dbAdapter = $container->get(\Zend\Db\Adapter\AdapterInterface::class);

        $writer = new \Zend\Log\Writer\Db($dbAdapter, 'log', [
            'timestamp' => 'timestamp',
            'priority' => 'priority',
            'priorityName' => 'priority_name',
            'message' => 'message',
            'extra' => [
                'messageExtended' => 'message_extended',
                'file' => 'file',
                'class' => 'class',
                'line' => 'line',
                'function' => 'function',
            ],
        ]);
        $writer->addFilter(\Zend\Log\Filter\Priority::class, [
            'priority' => $logLevel,
        ]);
        $logger->addWriter($writer);

        return $logger;
    }
}
