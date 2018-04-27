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

namespace Setup\Helper;

use Zend\Config\Config;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\AbstractPreparableSql;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\SqlInterface;
use Zend\Db\Sql\Ddl\AlterTable;
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
     * @var array
     */
    private $supportedParsingCommands = [
        'AlterTable',
        'CreateTable',
        'Insert',
    ];

    /**
     * @var array
     */
    private $supportedParsingColumns = [
        'BigInteger' => BigInteger::class,
        'Integer' => Integer::class,
        'Text' => Text::class,
        'Varchar' => Varchar::class,
    ];

    /**
     * @var array
     */
    private $supportedParsingConstraints = [
        'Index' => Index::class,
        'ForeignKey' => ForeignKey::class,
        'PrimaryKey' => PrimaryKey::class,
        'UniqueKey' => UniqueKey::class,
    ];

    /**
     * @var array
     */
    private $supportedParsingForeignKeyRules = [
        'cascade' => 'CASCADE',
        'noAction' => 'NO ACTION',
        'restrict' => 'RESTRICT',
        'setDefault' => 'SET DEFAULT',
        'setNull' => 'SET NULL',
    ];

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
    const SCHEMAUPDATED = 32;

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
     * Assemblies path of the database schema installation file. Works only with *nix file separators correctly.
     *
     * @return string
     */
    private function getSchemaInstallationFilepath()
    {
        $path = FileHelper::normalizePath($this->setupConfig->get('db_schema_path'));
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
     * Generates update schema pattern.
     *
     * @param int $version
     * @throws \RuntimeException
     * @return string
     */
    private function getUpdateSchemaPattern(int $version)
    {
        $schemaNaming = $this->setupConfig->get('db_schema_naming')->toArray();
        $driver = $this->adapterProvider->getDbDriverName();

        if (! array_key_exists($driver, $schemaNaming)) {
            throw new RuntimeException(sprintf('Database config contains unsupported driver "%s".', $driver));
        }

        return sprintf(
            'schemaUpdate.%s.%d.sql',
            $schemaNaming[$driver],
            $version
        );
    }

    /**
     * Processes addColumn commands
     *
     * @param CreateTable|AlterTable $sql
     * @param array $command
     * @return number
     */
    private function parseAddColumn($sql, array $command)
    {
        $processedEntries = 0;

        if (! ($sql instanceof CreateTable) && ! ($sql instanceof AlterTable)) {
            return $processedEntries;
        }

        if (array_key_exists('addColumn', $command) && is_array($command['addColumn'])) {
            foreach ($command['addColumn'] as $col) {
                if (! array_key_exists('type', $col) ||
                    ! array_key_exists($col['type'], $this->supportedParsingColumns) ||
                    ! array_key_exists('name', $col) ||
                    ! is_string($col['name'])) {
                    continue;
                }

                $sqlCol = new $this->supportedParsingColumns[$col['type']]($col['name']);

                if ($sqlCol instanceof AbstractLengthColumn && array_key_exists('length', $col) &&
                    is_int($col['length'])) {
                    $sqlCol->setLength($col['length']);
                }
                if (array_key_exists('nullable', $col) && is_bool($col['nullable'])) {
                    $sqlCol->setNullable($col['nullable']);
                }
                if (array_key_exists('default', $col)) {
                    $sqlCol->setDefault($col['default']);
                }
                if (array_key_exists('options', $col) && is_array($col['options'])) {
                    $sqlCol->setOptions($col['options']);
                }

                $sql->addColumn($sqlCol);
                $processedEntries++;
            }
        }

        return $processedEntries;
    }

    /**
     * Constructor
     *
     * @param Config $config
     * @param AdapterProviderHelper $adapterProvider
     * @param Translator $translator
     * @param ZUModuleOptions $zuModuleOptions
     */
    public function __construct(
        Config $config,
        AdapterProviderHelper $adapterProvider,
        Translator $translator,
        ZUModuleOptions $zuModuleOptions
    ) {
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
        return $this->adapterProvider->canConnect();
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
     * Parses schema file with array of abstract commands into series of prepared SqlInterface commands.
     *
     * @param string $schemaFilenamePath
     * @return boolean|\Zend\Db\Sql\SqlInterface[]
     */
    private function parseSchemaFile(string $schemaFilenamePath)
    {
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

        $processedCommands = [];

        foreach ($schema as $command) {
            $sql = null;

            if (! array_key_exists('commandName', $command) ||
                ! in_array($command['commandName'], $this->supportedParsingCommands, true) ||
                ! array_key_exists('tableName', $command) ||
                ! is_string($command['tableName'])) {
                continue;
            }

            $commandName = $command['commandName'];

            if ($commandName === 'AlterTable') {
                $changeColumnCount = 0;
                if (array_key_exists('changeColumn', $command) &&
                    is_array($command['changeColumn'])) {
                    $changeColumnCount = count($command['changeColumn']);
                }

                $sql = new AlterTable($command['tableName']);

                $addColumnCount = $this->parseAddColumn($sql, $command);

                if (($addColumnCount + $changeColumnCount) === 0) {
                    continue;
                }

                if ($changeColumnCount > 0) {
                    foreach ($command['changeColumn'] as $col) {
                        if (! array_key_exists('type', $col) ||
                            ! array_key_exists($col['type'], $this->supportedParsingColumns) ||
                            ! array_key_exists('name', $col) ||
                            ! is_string($col['name'])) {
                            continue;
                        }

                        $sqlCol = new $this->supportedParsingColumns[$col['type']]($col['name']);

                        if ($sqlCol instanceof AbstractLengthColumn && array_key_exists('length', $col) &&
                            is_int($col['length'])) {
                            $sqlCol->setLength($col['length']);
                        }
                        if (array_key_exists('nullable', $col) && is_bool($col['nullable'])) {
                            $sqlCol->setNullable($col['nullable']);
                        }
                        if (array_key_exists('default', $col)) {
                            $sqlCol->setDefault($col['default']);
                        }
                        if (array_key_exists('options', $col) && is_array($col['options'])) {
                            $sqlCol->setOptions($col['options']);
                        }

                        $sql->changeColumn($col['name'], $sqlCol);
                    }
                }
            } elseif ($commandName === 'CreateTable') {
                $sql = new CreateTable($command['tableName']);

                $addColumnCount = $this->parseAddColumn($sql, $command);

                if ($addColumnCount === 0) {
                    continue;
                }

                if (array_key_exists('addConstraint', $command) &&
                    is_array($command['addConstraint'])) {
                    foreach ($command['addConstraint'] as $constr) {
                        if (! array_key_exists('type', $constr) ||
                            ! array_key_exists($constr['type'], $this->supportedParsingConstraints) ||
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
                            $sqlConstr = new $this->supportedParsingConstraints[$constr['type']](
                                $constr['column'],
                                $name,
                                $lengths
                            );
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
                                array_key_exists($constr['onDelete'], $this->supportedParsingForeignKeyRules)) {
                                $onDelete = $this->supportedParsingForeignKeyRules[$constr['onDelete']];
                            }
                            $onUpdate = null;
                            if (array_key_exists('onUpdate', $constr) &&
                                is_string($constr['onUpdate']) &&
                                array_key_exists($constr['onUpdate'], $this->supportedParsingForeignKeyRules)) {
                                $onUpdate = $this->supportedParsingForeignKeyRules[$constr['onUpdate']];
                            }
                            $sqlConstr = new $this->supportedParsingConstraints[$constr['type']](
                                $name,
                                $constr['column'],
                                $constr['referenceTable'],
                                $constr['referenceColumn'],
                                $onDelete,
                                $onUpdate
                            );
                        } else {
                            $sqlConstr = new $this->supportedParsingConstraints[$constr['type']](
                                $constr['column'],
                                $name
                            );
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
        if (! $this->isSchemaInstalled()
            && ($this->lastStatus = self::DBNOTINSTALLEDORTABLENOTPRESENT)) {
            // Creating version table.
            $table = new CreateTable($this->setupConfig->get('db_schema_version_table'));
            $table->addColumn(new BigInteger('version'))
                ->addColumn(new Varchar('setupid', 32))
                ->addColumn(new BigInteger('timestamp'))
                ->addConstraint(new PrimaryKey('version'));
            $this->adapterProvider->executeSqlStatement($table);

            // Installing the custom application database schema script.
            $schemaFile = $this->getSchemaInstallationFilepath();
            if (file_exists($schemaFile)) {
                $schema = file_get_contents($schemaFile);
                $this->adapterProvider->executeSqlStatement($schema);
            }

            // Inserting version information.
            $insert = $this->adapterProvider->getSql()->insert($this->setupConfig->get('db_schema_version_table'));
            $insert->columns([
                'version',
                'setupid',
                'timestamp'
            ])->values(
                [
                    'version' => $this->lastParsedSchemaVersion,
                    'setupid' => $this->setupConfig->get('setup_id'),
                    'timestamp' => time()
                ]
            );
            $this->adapterProvider->executeSqlStatement($insert);
            if ($this->setupConfig->get('db_schema_init_version') > 1) {
                $insert->values(
                    [
                        'version' => $this->setupConfig->get('db_schema_init_version')
                    ],
                    $insert::VALUES_MERGE
                );
                $this->adapterProvider->executeSqlStatement($insert);
            }
        }
    }

    /**
     * Checks the installation status of the schema.
     *
     * @return boolean
     */
    public function isSchemaInstalled()
    {
        if (! $this->canConnect()) {
            $this->lastStatus = self::NODBCONNECTION;
            $this->lastMessage = $this->translator->translate('Database connection can\'t be established.');
            return false;
        }

        $select = $this->adapterProvider->getSql()
            ->select($this->setupConfig->get('db_schema_version_table'))
            ->where([
                'version' => 1
            ]);

        try {
            $resultSet = $this->adapterProvider->executeSqlStatement($select);
            $result = $resultSet->current();
            if (is_null($result)) {
                $this->lastStatus = self::TABLEEXISTSBUTISEMPTY;
                $this->lastMessage = $this->translator->translate('The database version table exists but is empty.');
                return false;
            } elseif (! array_key_exists('setupid', $result)) {
                $this->lastStatus = self::TABLEEXISTSBUTHASWRONGSTRUCTURE;
                $this->lastMessage = $this->translator->translate(
                    'The database version table exists but has wrong structure.'
                );
                return false;
            } elseif ($result['setupid'] != (string) $this->setupConfig->get('setup_id')) {
                $this->lastStatus = self::TABLEEXISTSBUTHASWRONGSETUPID;
                $this->lastMessage = $this->translator->translate(
                    'The database version exists but contains wrong setup id. This means, that another application is installed in this database.'
                );
                return false;
            } else {
                $this->lastStatus = self::DBSCHEMASEEMSTOBEINSTALLED;
                $this->lastMessage = $this->translator->translate(
                    'Database schema seems to be installed correctly. Proceed with the next step.'
                );
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
            $select = $this->adapterProvider->getSql()
                ->select($this->zuModuleOptions->getTableName())
                ->columns(
                    [
                        'count' => new Expression('count(*)')
                    ]
                );

            try {
                $resultSet = $this->adapterProvider->executeSqlStatement($select);
                $result = $resultSet->current();

                $exists = ($result['count'] > 0);
                $this->lastStatus = self::USERTABLESEEMSTOBEOK;
                if ($exists) {
                    $this->lastMessage = $this->translator->translate(
                        'A user already exists in the database. Proceed with next step.'
                    );
                } else {
                    $this->lastMessage = $this->translator->translate('No user exists yet, please create one.');
                }
                return $exists;
            } catch (Exception $e) {
                $this->lastStatus = self::SOMETHINGISWRONGWITHWITHUSERTABLE;
                $this->lastMessage = sprintf(
                    $this->translator->translate(
                        'Something is wrong with the user table, setup can\'t proceed. Error message: %s'
                    ),
                    $e->getMessage()
                );
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Sets database config array and creates an adapter with it
     *
     * @param array $dbConfig
     */
    public function setDbConfigArray(array $dbConfig)
    {
        $this->adapterProvider->setDbAdapter($dbConfig);
    }

    /**
     * Updates database schema version
     */
    public function updateSchema()
    {
        if (! $this->isSetupComplete()) {
            $this->lastStatus = self::SETUPINCOMPLETE;
            return;
        }

        $select = $this->adapterProvider->getSql()
            ->select($this->setupConfig->get('db_schema_version_table'))
            ->columns([
                'version' => new Expression('max(version)')
            ]);

        // If isSetupComplete() = true, it shouldn't happen, that max version isn't returned.
        // But better be safe than sorry.
        try {
            $resultSet = $this->adapterProvider->executeSqlStatement($select);
            $result = $resultSet->current();
            if (is_null($result) || (! array_key_exists('version', $result))) {
                $this->lastStatus = self::SETUPINCOMPLETE;
                return;
            }

            $version = (int) $result['version'];
        } catch (Exception $e) {
            $this->lastStatus = self::SETUPINCOMPLETE;
            return;
        }

        $this->lastStatus = self::CURRENTSCHEMAISLATEST;

        $path = FileHelper::normalizePath($this->setupConfig->get('db_schema_path'));
        $insert = null;
        while (file_exists($schemaUpdateFile = $path . '/' . $this->getUpdateSchemaPattern(++$version))) {
            $this->lastStatus = self::SCHEMAUPDATED;

            $schema = file_get_contents($schemaUpdateFile);
            $this->adapterProvider->executeSqlStatement($schema);

            // Inserting version information.
            if (is_null($insert)) {
                $insert = $this->adapterProvider->getSql()->insert($this->setupConfig->get('db_schema_version_table'));
                $insert->columns([
                    'version',
                    'setupid',
                    'timestamp'
                ])->values([
                    'setupid' => $this->setupConfig->get('setup_id')
                ]);
            }
            $insert->values(
                [
                    'version' => $version,
                    'timestamp' => time()
                ],
                $insert::VALUES_MERGE
            );
            $this->adapterProvider->executeSqlStatement($insert);
        }
    }

    public function printSchema(string $filename, string $sqlType)
    {
        $path = FileHelper::normalizePath($this->setupConfig->get('db_schema_path'));
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
        $suppressError = function ($errno, $errstr, $errfile, $errline, $errcontext) {
        };

        $sqlString = '';
        foreach ($processedSchema as $sqlCommand) {
            $tempAdapter = $typedAdapter;
            if ($sqlCommand instanceof AbstractPreparableSql) {
                $tempAdapter = $fallbackAdapter;
                set_error_handler($suppressError);
            }

            $sqlString .= $this->adapterProvider->getSql()->buildSqlString($sqlCommand, $tempAdapter);

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
