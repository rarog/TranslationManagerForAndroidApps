<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */
namespace Setup\Helper;

use Zend\Db\Adapter\Adapter;

class AdapterProviderHelper
{

    /**
     *
     * @var Adapter
     */
    const SUPPORTED_ADAPTERS = [
        'Pdo_Mysql',
        'Pdo_Pgsql',
        'Pdo_Sqlite',
    ];

    /**
     * Creates adapter
     *
     * @param array $dbConfig
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getDbAdapter(array $dbConfig)
    {
        // Ensuring, that a valid adapter is returned even if config array is invalid
        if (! array_key_exists('driver', $dbConfig) || ! is_string($dbConfig['driver']) ||
            ! in_array($dbConfig['driver'], self::SUPPORTED_ADAPTERS)) {
            $dbConfig = [
                'driver' => 'Pdo',
            ];
        }

        return new Adapter($dbConfig);
    }
}
