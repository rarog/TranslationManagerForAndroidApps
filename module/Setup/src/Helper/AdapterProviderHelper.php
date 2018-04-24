<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */
namespace Setup\Helper;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterInterface;

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
                'driver' => 'Pdo'
            ];
        }

        $this->dbAdapter = new Adapter($dbConfig);
        $this->dbDriverName = $dbConfig['driver'];
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
}
