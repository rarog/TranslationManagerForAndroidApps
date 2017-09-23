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

namespace Setup\Validator;

use Zend\Validator\AbstractValidator;

class PrintSchemaSql extends AbstractValidator
{
    const SQLPLATTFORM = 'sqlplattform';

    /**
     * @var array
     */
    private $supportedSqlPlatforms = [
        'mysql',
        'mariadb',
        'pgsql',
        'sqlite',
        'sql92',
    ];

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::SQLPLATTFORM => '\'%value%\' is not a supported sql plattform',
    ];

    /**
     * {@inheritDoc}
     * @see \Zend\Validator\ValidatorInterface::isValid()
     */
    public function isValid($value)
    {
        if (! in_array($value, $this->supportedSqlPlatforms, true)) {
            $this->error(self::SQLPLATTFORM);
            return false;
        }

        return true;
    }
}
