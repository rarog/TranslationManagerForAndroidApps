<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */
namespace Setup\Command;

use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use ZF\Console\Route;
use Setup\Model\DatabaseHelper;

class UpdateSchema
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
        $this->databaseHelper->updateSchema();

        switch ($this->databaseHelper->getLastStatus()) {
            case $this->databaseHelper::SETUPINCOMPLETE:
                $msg = 'Setup is incomplete.';
                break;
            case $this->databaseHelper::CURRENTSCHEMAISLATEST:
                $msg = 'Latest schema is already installed in the database.';
                break;
            default:
                $msg = 'Unknown status';
        }

        $console->writeLine($msg, ColorInterface::NORMAL);
        return 0;
    }
}
