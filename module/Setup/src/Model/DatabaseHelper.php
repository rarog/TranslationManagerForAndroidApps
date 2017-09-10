<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Setup\Model;

use Zend\Config\Config;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Ddl;
use Zend\Db\Sql\Ddl\Column;
use Zend\Db\Sql\Ddl\Constraint;
use Zend\Db\Sql\Sql;
use Zend\Mvc\I18n\Translator;
use ZfcUser\Options\ModuleOptions as ZUModuleOptions;

class DatabaseHelper
{
    /**
     * @var mixed
     */
    private $dbConfig;

    /**
     * @var Adapter
     */
    private $dbAdapter;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var mixed
     */
    private $setupConfig;

    /**
     * @var ZUModuleOptions
     */
    private $zuModuleOptions;

    /**
     * @var int
     */
    private $lastStatus;

	/**
	 * @var string
	 */
	private $lastMessage;

	/**
	 * @var Sql
	 */
	private $sql;

	const NODBCONNECTION = 0;
	const DBNOTINSTALLEDORTABLENOTPRESENT = 1;
	const TABLEEXISTSBUTISEMPTY = 2;
	const TABLEEXISTSBUTHASWRONGSTRUCTURE = 3;
	const TABLEEXISTSBUTHASWRONGSETUPID = 4;
	const DBSCHEMASEEMSTOBEINSTALLED = 10;

	const SOMETHINGISWRONGWITHWITHUSERTABLE = 20;
	const USERTABLESEEMSTOBEOK = 21;

	const SETUPINCOMPLETE = 30;
	const CURRENTSCHEMAISLATEST = 31;

	/**
	 * Generates installation schema RexEx
	 * @throws \RuntimeException
	 * @return string
	 */
	private function getInstallationSchemaRegex()
	{
	    $schemaNaming = $this->setupConfig->get('db_schema_naming');

	    if (! array_key_exists($this->dbConfig['driver'], $schemaNaming)) {
	        throw new \RuntimeException('Database config contains unsupported driver.');
	    }

	    return sprintf(
	        '/schema\.%s\.(\d)\.sql/',
	        $schemaNaming[$this->dbConfig['driver']]
	    );
	}


	/**
	 * Constructor
	 *
	 * @param Config $config
	 * @param Translator $translator
	 * @param ZUModuleOptions $zuModuleOptions
	 */
	public function __construct(Config $config, Translator $translator, ZUModuleOptions $zuModuleOptions)
    {
        $dbConfig = $config->db;

        $this->setDbConfigArray(($dbConfig) ? $dbConfig->toArray() : []);
        $this->translator = $translator;
        $this->setupConfig = $config->setup;
        $this->zuModuleOptions = $zuModuleOptions;
    }

    /**
     * Helper function to test, if database connection can be established with provided credentials
     *
     * @return boolean
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
     * @return int
     */
    public function getLastStatus()
    {
        return $this->lastStatus;
    }

    /**
     * Helper function to return last message.
     *
     * @return string
     */
    public function getLastMessage()
    {
        return $this->lastMessage;
    }


    /**
     * Gets SQL object from adapter.
     *
     * @return Sql
     */
    private function getSql()
    {
        if (is_null($this->sql)) {
            $this->sql = new Sql($this->dbAdapter);
        }
        return $this->sql;
    }

    /**
     * Assemblies path of the database schema installation file. Works only with *nix file separators correctly.
     *
     * @return string
     */
    private function getSchemaInstallationFilepath()
    {
        $filename = $this->normalizePath($this->setupConfig->get('db_schema_path'));
        $filename .= sprintf(
            '/schema.%s.sql',
            $this->setupConfig->get('db_schema_naming')[$this->dbConfig['driver']]
        );
        return $filename;
    }

    /**
     * Executes a prepared SQL statement in the configured database.
     *
     * @param \Zend\Db\Sql\SqlInterface|string  $sql
     */
    private function executeSqlStatement($sql)
    {
        if ($sql instanceof \Zend\Db\Sql\SqlInterface) {
            $sqlString = $this->getSql()->buildSqlString($sql, $this->dbAdapter);
        } else if (is_string($sql)) {
            $sqlString = trim($sql);
        } else {
            throw new \Exception(sprintf(
                'Function executeSqlStatement was called with unsupport parameter of type "%s".',
                (is_object($sql)) ? get_class($sql) : gettype($sql)
            ));
        }

        $this->dbAdapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
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
        if (!$this->isSchemaInstalled() &&
            ($this->lastStatus = self::DBNOTINSTALLEDORTABLENOTPRESENT)) {
            // Creating version table.
            $table = new Ddl\CreateTable($this->setupConfig->get('db_schema_version_table'));
            $table->addColumn(new Column\BigInteger('version'))
                ->addColumn(new Column\Varchar('setupid', 32))
                ->addColumn(new Column\BigInteger('timestamp'))
                ->addConstraint(new Constraint\PrimaryKey('version'));
            $this->executeSqlStatement($table);

            // Installing the custom application database schema script.
            $schemaFile = $this->getSchemaInstallationFilepath();
            if (file_exists($schemaFile)) {
                $schema = file_get_contents($schemaFile);
                $this->executeSqlStatement($schema);
            }

            // Inserting version information.
            $insert = $this->getSql()->insert($this->setupConfig->get('db_schema_version_table'));
            $insert->columns(['version', 'setupid', 'timestamp'])
                ->values([
                    'version' => 1,
                    'setupid' => $this->setupConfig->get('setup_id'),
                    'timestamp' => time(),
                ]);
            $this->executeSqlStatement($insert);
            if ($this->setupConfig->get('db_schema_init_version') > 1){
                $insert->values([
                    'version' => $this->setupConfig->get('db_schema_init_version'),
                ], $insert::VALUES_MERGE);
                $this->executeSqlStatement($insert);
            }
        }
    }

    /**
     * Checks the installation status of the schema.
     *
     * @return boolean
     */
    public function isSchemaInstalled() {
        if (!$this->canConnect()) {
            $this->lastStatus = self::NODBCONNECTION;
            $this->lastMessage = $this->translator->translate('Database connection can\'t be established.');
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
                $this->lastMessage = $this->translator->translate('The database version table exists but is empty.');
                return false;
            } else if (!array_key_exists('setupid', $result)) {
                $this->lastStatus = self::TABLEEXISTSBUTHASWRONGSTRUCTURE;
                $this->lastMessage = $this->translator->translate('The database version table exists but has wrong structure.');
                return false;
            } else if ($result['setupid'] != (string) $this->setupConfig->get('setup_id')) {
                $this->lastStatus = self::TABLEEXISTSBUTHASWRONGSETUPID;
                $this->lastMessage = $this->translator->translate('The database version exists but contains wrong setup id. This means, that another application is installed in this database.');
                return false;
            } else {
                $this->lastStatus = self::DBSCHEMASEEMSTOBEINSTALLED;
                $this->lastMessage = $this->translator->translate('Database schema seems to be installed correctly. Proceed with the next step.');
                return true;
            }
        } catch (\Exception $e) {
            $this->lastStatus = self::DBNOTINSTALLEDORTABLENOTPRESENT;
            $this->lastMessage = $this->translator->translate('Database schema seems to not be installed yet.');
            return false;
        }
    }

    /**
     * Checks, if setup is complete, which requires at least one user to exist in the database
     *
     * @return boolean
     */
    public function isSetupComplete()
    {
        if ($this->isSchemaInstalled()) {
            $select = $this->getSql()
                ->select($this->zuModuleOptions->getTableName())
                ->columns(array('count' => new \Zend\Db\Sql\Expression('count(*)')));
            $statement = $this->getSql()->prepareStatementForSqlObject($select);

            try {
                $resultSet = $statement->execute();
                $result = $resultSet->current();

                $exists = ($result['count'] > 0);
                $this->lastStatus = self::USERTABLESEEMSTOBEOK;
                if ($exists) {
                    $this->lastMessage = $this->translator->translate('A user already exists in the database. Proceed with next step.');
                } else {
                    $this->lastMessage = $this->translator->translate('No user exists yet, please create one.');
                }
                return $exists;
            } catch (\Exception $e) {
                $this->lastStatus = self::SOMETHINGISWRONGWITHWITHUSERTABLE;
                $this->lastMessage = sprintf(
                    $this->translator->translate('Something is wrong with the user table, setup can\'t proceed. Error message: %s'),
                    $e->getMessage()
                );
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Normalize a path for insertion in the stack
     *
     * @param  string $path
     * @return string
     */
    private function normalizePath(string $path)
    {
        $path = rtrim($path, '/');
        $path = rtrim($path, '\\');
        return $path;
    }

    /**
     * Sets database config array and creates an adapter with it
     *
     * @param array $dbConfig
     */
    public function setDbConfigArray(array $dbConfig) {
        $this->dbConfig = $dbConfig;
        $this->dbAdapter = new Adapter($dbConfig);
    }

    /**
     * Updates database schema version
     */
    public function updateSchema()
    {
        if (!$this->isSetupComplete()) {
            $this->lastStatus = self::SETUPINCOMPLETE;
            return;
        }

        // TODO: Write proper logic

        $this->lastStatus = self::CURRENTSCHEMAISLATEST;
        return;
    }
}
