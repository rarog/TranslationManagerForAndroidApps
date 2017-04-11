<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Setup\Model;

use Zend\Mvc\I18n\Translator;

class DatabaseChecks
{
	protected $dbAdapter;
	protected $translator;
	protected $connection;
	protected $lastMessage;

	public function __construct(array $dbConfigArray, Translator $translator)
    {
        $this->dbAdapter = new \Zend\Db\Adapter\Adapter($dbConfigArray);
        $this->translator = $translator;
    }

    public function canConnect()
    {
        try {
            $this->dbAdapter->getDriver()->checkEnvironment();
            $this->connection = $this->dbAdapter->getDriver()->getConnection();
            if (!$this->connection->isConnected()) {
                $this->connection->connect();
            }
            $this->lastMessage = ($this->connection->isConnected()) ? $this->translator->translate('Database connection successfully established.') : $this->translator->translate('Could not establish database connection.');
            return $this->connection->isConnected();
        } catch (\Exception $e) {
            $this->lastMessage = $e->getMessage();
            return false;
        }
    }

    public function getLastMessage()
    {
        return $this->lastMessage;
    }
}
