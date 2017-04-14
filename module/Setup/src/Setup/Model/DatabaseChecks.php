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
	protected $setupConfig;
	protected $lastMessage;
	protected $sql;

	public function __construct(array $dbConfigArray, Translator $translator, $setupConfig = null)
    {
        $this->dbAdapter = new \Zend\Db\Adapter\Adapter($dbConfigArray);
        $this->translator = $translator;
        $this->setupConfig = $setupConfig;
    }

    public function canConnect()
    {
        try {
            $this->dbAdapter->getDriver()->checkEnvironment();
            $connection = $this->dbAdapter->getDriver()->getConnection();
            if (!$connection->isConnected()) {
                $connection->connect();
            }
            $this->lastMessage = ($connection->isConnected()) ? $this->translator->translate('Database connection successfully established.') : $this->translator->translate('Could not establish database connection.');
            return $connection->isConnected();
        } catch (\Exception $e) {
            $this->lastMessage = $e->getMessage();
            return false;
        }
    }

    public function getLastMessage()
    {
        return $this->lastMessage;
    }

    protected function getSql()
    {
        if (is_null($this->sql)) {
            $this->sql = new \Zend\Db\Sql\Sql($this->dbAdapter);;
        }
        return $this->sql;
    }

    public function isInstalled() {
        $select = $this->getSql()
                       ->select($this->setupConfig->get('db_schema_version_table'))
                       ->where(['version' => 1]);
        $statement = $this->getSql()->prepareStatementForSqlObject($select);
        try {
            $results = $statement->execute();
            // TODO: Implement the result check.
            $this->lastMessage = 'UNDEFINED';
            return false;
        } catch (\Exception $e) {
            $this->lastMessage = $this->translator->translate('Database schema seems to not be installed yet.');
            return false;
        }
    }
}
