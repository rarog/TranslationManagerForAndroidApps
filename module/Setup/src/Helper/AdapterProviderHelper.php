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

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\SqlInterface;
use Exception;
use RuntimeException;
use Zend\Db\Sql\PreparableSqlInterface;

class AdapterProviderHelper
{
    /**
     * Array of supported adapters
     *
     * @var Adapter
     */
    const SUPPORTED_ADAPTERS = [
        'Pdo_Mysql',
        'Pdo_Pgsql',
        'Pdo_Sqlite',
    ];

    /**
     * Database config array, that can be used to initialise an instance of \Zend\Db\Adapter\Adapter
     *
     * @var array
     */
    private $dbConfigArray = [];

    /**
     * Database adapter
     *
     * @var null|Adapter
     */
    private $dbAdapter;

    /**
     * Driver name used to initialise the adapter
     *
     * @var string
     */
    private $dbDriverName = 'Pdo';

    /**
     * Sql instance initialised with database adapter
     *
     * @var null|Sql
     */
    private $sql;

    /**
     * Helper function to test, if database connection can be established with provided credentials.
     *
     * @return boolean
     */
    public function canConnect()
    {
        try {
            $driver = $this->getDbAdapter()->getDriver();
            $driver->checkEnvironment();
            $connection = $driver->getConnection();
            if (! $connection->isConnected()) {
                $connection->connect();
            }
            return $connection->isConnected();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Executes a prepared SQL statement in the configured database.
     *
     * @param PreparableSqlInterface|SqlInterface|string  $sql
     * @throws RuntimeException
     * @return \Zend\Db\Adapter\Driver\ResultInterface|\Zend\Db\Adapter\Driver\StatementInterface|\Zend\Db\ResultSet\ResultSet
     */
    public function executeSqlStatement($sql)
    {
        if ($sql instanceof PreparableSqlInterface) {
            $statement = $this->getSql()->prepareStatementForSqlObject($sql);
            return $statement->execute();
        } elseif ($sql instanceof SqlInterface) {
            $sqlString = $this->getSql()->buildSqlString($sql, $this->adapterProvider->getDbAdapter());
            return $this->getDbAdapter()->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        } elseif (is_string($sql)) {
            $sqlString = trim($sql);
            return $this->getDbAdapter()->getDriver()->getConnection()->getResource()->exec($sqlString);
        } else {
            throw new RuntimeException(sprintf(
                'Function executeSqlStatement was called with unsupport parameter of type "%s".',
                (is_object($sql)) ? get_class($sql) : gettype($sql)
            ));
        }
    }

    /**
     * Creates adapter
     *
     * @param array $dbConfig
     * @return \Zend\Db\Adapter\Adapter
     */
    public function setDbAdapter(array $dbConfig)
    {
        // Ensuring, that a valid adapter is created even if config array is invalid
        if (! array_key_exists('driver', $dbConfig) || ! is_string($dbConfig['driver']) ||
            ! in_array($dbConfig['driver'], self::SUPPORTED_ADAPTERS)) {
            $dbConfig = [
                'driver' => 'Pdo',
            ];
        }

        $this->dbAdapter = new Adapter($dbConfig);
        $this->dbDriverName = $dbConfig['driver'];
        $this->sql = null;
    }

    /**
     * Returns adapter
     *
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getDbAdapter()
    {
        // Ensuring, that a valid adapter is returned even if no valid adapter is present yet
        if (! $this->dbAdapter instanceof AdapterInterface) {
            $this->setDbAdapter([]);
        }

        return $this->dbAdapter;
    }

    /**
     * Returns driver name used to initialise the adapter
     *
     * @return string
     */
    public function getDbDriverName()
    {
        return $this->dbDriverName;
    }

    /**
     * Gets SQL object from adapter.
     *
     * @return Sql
     */
    public function getSql()
    {
        if (! $this->sql instanceof SqlInterface) {
            $this->sql = new Sql($this->getDbAdapter());
        }

        return $this->sql;
    }
}
