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
namespace Setup\Command;

use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use ZF\Console\Route;
use Setup\Model\DatabaseHelper;

class PrintSchema
{
    /**
     * Database helper model
     *
     * @var DatabaseHelper
     */
    private $databaseHelper;

    /**
     * Constructor
     *
     * @param DatabaseHelper $databaseHelper
     */
    public function __construct(DatabaseHelper $databaseHelper)
    {
        $this->databaseHelper = $databaseHelper;
    }

    /**
     * Main routine
     *
     * @param Route $route
     * @param AdapterInterface $console
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $result = $this->databaseHelper->printSchema($route->getMatchedParam('schemafile'), $route->getMatchedParam('sql'));

        if ($result === false) {
            $console->writeLine('File does not exist or is invalid.', ColorInterface::RED);
            return 1;
        }

        $console->writeLine('----------', ColorInterface::NORMAL);
        $console->writeLine($result, ColorInterface::NORMAL);
        $console->writeLine('----------', ColorInterface::NORMAL);
        return 0;
    }
}
