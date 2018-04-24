<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Setup\Helper;

use Zend\Config\Config;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\AbstractPreparableSql;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\SqlInterface;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Ddl\Column\AbstractLengthColumn;
use Zend\Db\Sql\Ddl\Column\BigInteger;
use Zend\Db\Sql\Ddl\Column\Integer;
use Zend\Db\Sql\Ddl\Column\Text;
use Zend\Db\Sql\Ddl\Column\Varchar;
use Zend\Db\Sql\Ddl\Constraint\ForeignKey;
use Zend\Db\Sql\Ddl\Constraint\PrimaryKey;
use Zend\Db\Sql\Ddl\Constraint\UniqueKey;
use Zend\Db\Sql\Ddl\Index\Index;
use Zend\Mvc\I18n\Translator;
use ZfcUser\Options\ModuleOptions as ZUModuleOptions;
use Exception;
use RuntimeException;

class DatabaseHelper
{

    /**
     * Adapter provider helper
     *
     * @var AdapterProviderHelper
     */
    private $adapterProvider;

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
     * Installation schema file regular expression
     *
     * @var string
     */
    private $installationSchemaRegex;

    /**
     * Update schema file regular expression
     *
     * @var string
     */
    private $updateSchemaRegex;

    /**
     * @var int
     */
    private $lastParsedSchemaVersion = 0;

    /**
     * @var int
     */
    private $lastStatus = 0;

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
     * Generates installation schema regular expression.
     *
     * @throws \RuntimeException
     * @return string
     */
    private function getInstallationSchemaRegex()
    {
        if (is_null($this->installationSchemaRegex)) {
            $schemaNaming = $this->setupConfig->get('db_schema_naming')->toArray();
            $driver = $this->adapterProvider->getDbDriverName();

            if (! array_key_exists($driver, $schemaNaming)) {
                throw new RuntimeException(sprintf('Database config contains unsupported driver "%s".', $driver));
            }

            $this->installationSchemaRegex = sprintf(
                '/schema\.%s\.(\d+)\.sql/',
                $schemaNaming[$driver]
            );
        }

        return $this->installationSchemaRegex;
    }

    /**
     * Generates update schema regular expression.
     *
     * @throws \RuntimeException
     * @return string
     */
    private function getUpdateSchemaRegex()
    {
        if (is_null($this->updateSchemaRegex)) {
            $schemaNaming = $this->setupConfig->get('db_schema_naming')->toArray();
            $driver = $this->adapterProvider->getDbDriverName();

            if (! array_key_exists($driver, $schemaNaming)) {
                throw new RuntimeException(sprintf('Database config contains unsupported driver "%s".', $driver));
            }

            $this->updateSchemaRegex = sprintf(
                '/schemaUpdate\.%s\.(\d+)\.sql/',
                $schemaNaming[$driver]
            );
        }

        return $this->updateSchemaRegex;
    }

    /**
     * Constructor
     *
     * @param Config $config
     * @param AdapterProviderHelper $adapterProvider
     * @param Translator $translator
     * @param ZUModuleOptions $zuModuleOptions
     */
    public function __construct(Config $config, AdapterProviderHelper $adapterProvider, Translator $translator, ZUModuleOptions $zuModuleOptions)
    {
        $this->adapterProvider = $adapterProvider;
        $this->translator = $translator;
        $this->setupConfig = $config->setup;
        $this->zuModuleOptions = $zuModuleOptions;

        $dbConfig = $config->db;
        $this->setDbConfigArray(($dbConfig) ? $dbConfig->toArray() : []);
    }

    /**
     * Helper function to test, if database connection can be established with provided credentials.
     *
     * @return boolean
     */
    public function canConnect()
    {
        try {
            $this->adapterProvider->getDbAdapter()->getDriver()->checkEnvironment();
            $connection = $this->adapterProvider->getDbAdapter()->getDriver()->getConnection();
            if (!$connection->isConnected()) {
                $connection->connect();
            }
            $this->lastMessage = ($connection->isConnected()) ? $this->translator->translate('Database connection successfully established.') : $this->translator->translate('Could not establish database connection.');
            return $connection->isConnected();
        } catch (Exception $e) {
            $this->lastMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * Helper function to return last status parsed schema version.
     *
     * @return int
     */
    public function getLastParsedSchemaVersion()
    {
        return $this->lastParsedSchemaVersion;
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
            $this->sql = new Sql($this->adapterProvider->getDbAdapter());
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
        $path = $this->normalizePath($this->setupConfig->get('db_schema_path'));
        $pattern = $this->getInstallationSchemaRegex();

        $max = 0;
        $maxFilename = '';
        foreach (scandir($path) as $file) {
            if (preg_match($pattern, $file, $matches) == 1) {
                $maxTemp = (int) $matches[1];
                if ($maxTemp > $max) {
                    $max = $maxTemp;
                    $maxFilename = $file;
                }
            }
        }

        $this->lastParsedSchemaVersion = $max;

        if ($max === 0) {
            throw new RuntimeException('No valid installation schema file found.');
        }

        return $path . '/' . $maxFilename;
    }

    /**
     * Executes a prepared SQL statement in the configured database.
     *
     * @param \Zend\Db\Sql\SqlInterface|string  $sql
     */
    private function executeSqlStatement($sql)
    {
        if ($sql instanceof \Zend\Db\Sql\SqlInterface) {
            $sqlString = $this->getSql()->buildSqlString($sql, $this->adapterProvider->getDbAdapter());
            $this->adapterProvider->getDbAdapter()->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        } else if (is_string($sql)) {
            $sqlString = trim($sql);
            $this->adapterProvider->getDbAdapter()->getDriver()->getConnection()->getResource()->exec($sqlString);
        } else {
            throw new Exception(sprintf(
                'Function executeSqlStatement was called with unsupport parameter of type "%s".',
                (is_object($sql)) ? get_class($sql) : gettype($sql)
            ));
        }
    }

    /**
     * Parses schema file with array of abstract commands into series of prepared SqlInterface commands.
     *
     * @param string $schemaFilenamePath
     * @return boolean|\Zend\Db\Sql\SqlInterface[]
     */
    private function parseSchemaFile(string $schemaFilenamePath) {
        if (! file_exists($schemaFilenamePath)) {
            return false;
        }

        // Catch any possible unwanted output during during inclusion.
        ob_start();
        $schema = include $schemaFilenamePath;
        ob_end_clean();

        if (! is_array($schema)) {
            return false;
        }

        $supportedCommands = [
            'CreateTable',
            'Insert',
        ];
        $supportedColumns = [
            'BigInteger' => BigInteger::class,
            'Integer' => Integer::class,
            'Text' => Text::class,
            'Varchar' => Varchar::class,
        ];
        $supportedConstraints = [
            'Index' => Index::class,
            'ForeignKey' => ForeignKey::class,
            'PrimaryKey' => PrimaryKey::class,
            'UniqueKey' => UniqueKey::class,
        ];
        $supportedForeignKeyRules = [
            'cascade' => 'CASCADE',
            'noAction' => 'NO ACTION',
            'restrict' => 'RESTRICT',
            'setDefault' => 'SET DEFAULT',
            'setNull' => 'SET NULL',
        ];

        $processedCommands = [];

        foreach ($schema as $command) {
            $sql = null;

            if (! array_key_exists('commandName', $command) ||
                ! in_array($command['commandName'], $supportedCommands, true) ||
                ! array_key_exists('tableName', $command) ||
                ! is_string($command['tableName'])) {
                continue;
            }

            $commandName = $command['commandName'];

            if ($commandName === 'CreateTable') {
                if (! array_key_exists('addColumn', $command) ||
                    ! is_array($command['addColumn'])) {
                    continue;
                }

                $sql = new CreateTable($command['tableName']);

                foreach ($command['addColumn'] as $col) {
                    if (! array_key_exists('type', $col) ||
                        ! array_key_exists($col['type'], $supportedColumns) ||
                        ! array_key_exists('name', $col) ||
                        ! is_string($col['name'])) {
                        continue;
                    }

                    $sqlCol = new $supportedColumns[$col['type']]($col['name']);

                    if ($sqlCol instanceof AbstractLengthColumn &&
                        array_key_exists('length', $col) &&
                        is_int($col['length'])) {
                        $sqlCol->setLength($col['length']);
                    }
                    if (array_key_exists('nullable', $col) &&
                        is_bool($col['nullable'])) {
                        $sqlCol->setNullable($col['nullable']);
                    }
                    if (array_key_exists('default', $col)) {
                        $sqlCol->setDefault($col['default']);
                    }
                    if (array_key_exists('options', $col) &&
                        is_array($col['options'])) {
                        $sqlCol->setOptions($col['options']);
                    }

                    $sql->addColumn($sqlCol);
                }

                if (array_key_exists('addConstraint', $command) &&
                    is_array($command['addConstraint'])) {
                    foreach ($command['addConstraint'] as $constr) {
                        if (! array_key_exists('type', $constr) ||
                            ! array_key_exists($constr['type'], $supportedConstraints) ||
                            ! array_key_exists('column', $constr) ||
                            ! (is_string($constr['column']) || is_array($constr['column']))) {
                            continue;
                        }

                        $name = null;
                        if (array_key_exists('name', $constr) &&
                            is_string($constr['name'])) {
                            $name = $constr['name'];
                        }

                        if ($constr['type'] === 'Index') {
                            $lengths = [];
                            if (array_key_exists('lengths', $constr) &&
                                is_array($constr['lengths'])) {
                                $lengths = $constr['lengths'];
                            }
                            $sqlConstr = new $supportedConstraints[$constr['type']]($constr['column'], $name, $lengths);
                        } elseif ($constr['type'] === 'ForeignKey') {
                            if (! array_key_exists('referenceTable', $constr) ||
                                ! is_string($constr['referenceTable']) ||
                                ! array_key_exists('referenceColumn', $constr) ||
                                ! is_string($constr['referenceColumn'])) {
                                continue;
                            }

                            $onDelete = null;
                            if (array_key_exists('onDelete', $constr) &&
                                is_string($constr['onDelete']) &&
                                array_key_exists($constr['onDelete'], $supportedForeignKeyRules)) {
                                $onDelete = $supportedForeignKeyRules[$constr['onDelete']];
                            }
                            $onUpdate = null;
                            if (array_key_exists('onUpdate', $constr) &&
                                is_string($constr['onUpdate']) &&
                                array_key_exists($constr['onUpdate'], $supportedForeignKeyRules)) {
                                $onUpdate = $supportedForeignKeyRules[$constr['onUpdate']];
                            }
                            $sqlConstr = new $supportedConstraints[$constr['type']]($name, $constr['column'], $constr['referenceTable'], $constr['referenceColumn'], $onDelete, $onUpdate);
                        } else {
                            $sqlConstr = new $supportedConstraints[$constr['type']]($constr['column'], $name);
                        }

                        $sql->addConstraint($sqlConstr);
                    }
                }
            } elseif ($commandName === 'Insert') {
                if (! array_key_exists('columns', $command) ||
                    ! is_array($command['columns']) ||
                    ! array_key_exists('values', $command) ||
                    ! is_array($command['values']) ||
                    count($command['columns']) === 0 ||
                    count($command['columns']) !== count($command['values'])) {
                    continue;
                }

                $sql = new Insert($command['tableName']);
                $sql->columns($command['columns'])
                    ->values($command['values']);
            }

            if ($sql instanceof SqlInterface) {
                $processedCommands[] = $sql;
            }
        }

        if (empty($processedCommands)) {
            return false;
        }

        return $processedCommands;
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
                $table = new CreateTable($this->setupConfig->get('db_schema_version_table'));
                $table->addColumn(new BigInteger('version'))
                    ->addColumn(new Varchar('setupid', 32))
                    ->addColumn(new BigInteger('timestamp'))
                    ->addConstraint(new PrimaryKey('version'));
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
                        'version' => $this->lastParsedSchemaVersion,
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
        } catch (Exception $e) {
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
            ->columns(array('count' => new Expression('count(*)')));
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
            } catch (Exception $e) {
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
        $this->adapterProvider->setDbAdapter($dbConfig);

        // Resetting dependant objects
        $this->sql = null;
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

    public function printSchema(string $filename, string $sqlType) {
        $path = $this->normalizePath($this->setupConfig->get('db_schema_path'));
        $schemaFile = $path . '/' . $filename;

        $driver = null;
        switch ($sqlType) {
            case 'mysql':
            case 'mariadb':
                $driver = [
                    'driver' => 'Pdo_Mysql',
                ];
                break;
            case 'pgsql':
                $driver = [
                    'driver' => 'Pdo_Pgsql',
                ];
                break;
            case 'sqlite':
                $driver = [
                    'driver' => 'Pdo_Sqlite',
                ];
                break;
            default:
                return false;
        }

        if (is_null($driver)) {
            return false;
        }

        $processedSchema = $this->parseSchemaFile($schemaFile);

        if ($processedSchema === false) {
            return false;
        }

        $typedAdapter = new Adapter($driver);
        $fallbackAdapter = new Adapter([
            'driver' => 'Pdo',
        ]);
        // AbstractPlatform::quoteValue is throwing errors. This function will suppress them.
        $suppressError = function($errno, $errstr, $errfile, $errline, $errcontext){};

        $sqlString = '';
        foreach ($processedSchema as $sqlCommand) {
            $tempAdapter = $typedAdapter;
            if ($sqlCommand instanceof AbstractPreparableSql) {
                $tempAdapter = $fallbackAdapter;
                set_error_handler($suppressError);
            }

            $sqlString .= $this->getSql()->buildSqlString($sqlCommand, $tempAdapter);

            if ($sqlCommand instanceof AbstractPreparableSql) {
                restore_error_handler();
            }

            if (mb_substr($sqlString, -1) !== ';') {
                $sqlString .= ';';
            }
            $sqlString .= "\n";
        }
        return $sqlString;
    }
}
