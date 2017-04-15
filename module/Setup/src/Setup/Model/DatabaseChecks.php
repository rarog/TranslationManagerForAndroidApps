<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Setup\Model;

use Zend\Db\Sql\Ddl;
use Zend\Db\Sql\Ddl\Column;
use Zend\Db\Sql\Ddl\Constraint;
use Zend\Mvc\I18n\Translator;

class DatabaseChecks
{
	protected $dbAdapter;
	protected $translator;
	protected $setupConfig;
	protected $lastStatus;
	protected $lastMessage;
	protected $sql;

	const NODBCONNECTION = 0;
	const DBNOTINSTALELDORTABLENOTPRESENT = 1;
	const TABLEEXISTSBUTISEMPTY = 2;
	const TABLEEXISTSBUTHASWRONGSTRUCTURE = 3;
	const TABLEEXISTSBUTHASWRONGSETUPID = 4;
	const DBSCHEMASEEMSTOBEINSTALLED = 10;

	public function __construct(array $dbConfigArray, Translator $translator, $setupConfig = null)
    {
        $this->dbAdapter = new \Zend\Db\Adapter\Adapter($dbConfigArray);
        $this->translator = $translator;
        $this->setupConfig = $setupConfig;
    }

    /**
     * Helper function to test, if database connection can be established with provided credentials
     * @return boolean  Database connection status
     */
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

    /**
     * Helper function to return last status.
     *
     * @return int  Status class constant
     */
    public function getLastStatus()
    {
        return $this->lastStatus;
    }

    /**
     * Helper function to return last message.
     *
     * @return string  Last message
     */
    public function getLastMessage()
    {
        return $this->lastMessage;
    }

    protected function getSql()
    {
        if (is_null($this->sql)) {
            $this->sql = new \Zend\Db\Sql\Sql($this->dbAdapter);
        }
        return $this->sql;
    }

    /**
     * Installation function. It starts only, if the check says, that the installation hasn't run yet.
     * 1) It creates the version table.
     * 2) It runs the the custom application schema script.
     * 3) It inserts the version information.
     * If it runs into problems in step 2, this can be recognised as a kind of inbeetween state.
     */
    public function installSchema()
    {
        if (!$this->isInstalled() &&
            ($this->lastStatus = self::DBNOTINSTALELDORTABLENOTPRESENT)) {
            $table = new Ddl\CreateTable($this->setupConfig->get('db_schema_version_table'));
            $table->addColumn(new Column\Integer('version'))
                ->addColumn(new Column\Varchar('setupid', 32))
                ->addColumn(new Column\Integer('timestamp'))
                ->addConstraint(new Constraint\PrimaryKey('version'));
            $sqlString = $this->getSql()->buildSqlString($table, $this->dbAdapter);
            $this->dbAdapter->query($sqlString, $this->dbAdapter::QUERY_MODE_EXECUTE);

            // TODO: Implement installation of the application database schema

            $insert = $this->getSql()->insert($this->setupConfig->get('db_schema_version_table'));
            $insert->columns(['version', 'setupid', 'timestamp'])
                ->values([
                    'version' => 1,
                    'setupid' => $this->setupConfig->get('setup_id'),
                    'timestamp' => time(),
                ]);
            $sqlString = $this->getSql()->buildSqlString($insert, $this->dbAdapter);
            $this->dbAdapter->query($sqlString, $this->dbAdapter::QUERY_MODE_EXECUTE);
            if ($this->setupConfig->get('db_schema_init_version') > 1 ){
                $insert->values([
                    'version' => $this->setupConfig->get('db_schema_init_version'),
                ], $insert::VALUES_MERGE);
                $sqlString = $this->getSql()->buildSqlString($insert, $this->dbAdapter);
                $this->dbAdapter->query($sqlString, $this->dbAdapter::QUERY_MODE_EXECUTE);
            }
        }
    }

    public function isInstalled() {
        if (!$this->canConnect()) {
            $this->lastStatus = self::NODBCONNECTION;
            $this->lastMessage = 'NODBCONNECTION';
            return false;
        }

        $select = $this->getSql()
                       ->select($this->setupConfig->get('db_schema_version_table'))
                       ->where(['version' => 1]);
        $statement = $this->getSql()->prepareStatementForSqlObject($select);
        try {
            $resultSet = $statement->execute();
            $result = $resultSet->current();
            if (empty($result)) {
                $this->lastStatus = self::TABLEEXISTSBUTISEMPTY;
                $this->lastMessage = 'TABLEEXISTSBUTISEMPTY';
                return false;
            } else if (!array_key_exists('setupid', $result)) {
                $this->lastStatus = self::TABLEEXISTSBUTHASWRONGSTRUCTURE;
                $this->lastMessage = 'TABLEEXISTSBUTHASWRONGSTRUCTURE';
                return false;
            } else if ($result['setupid'] != (string) $this->setupConfig->get('setup_id')) {
                $this->lastStatus = self::TABLEEXISTSBUTHASWRONGSETUPID;
                $this->lastMessage = 'TABLEEXISTSBUTHASWRONGSETUPID';
                return false;
            } else {
                $this->lastStatus = self::DBSCHEMASEEMSTOBEINSTALLED;
                $this->lastMessage = 'DBSCHEMASEEMSTOBEINSTALLED';
                return true;
            }
        } catch (\Exception $e) {
            $this->lastStatus = self::DBNOTINSTALELDORTABLENOTPRESENT;
            $this->lastMessage = $this->translator->translate('Database schema seems to not be installed yet.');
            return false;
        }
    }
}
