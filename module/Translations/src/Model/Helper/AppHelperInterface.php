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

namespace Translations\Model\Helper;

use RuntimeException;
use Translations\Model\App;

interface AppHelperInterface
{
    /**
     * Helper for getting absolute path to app resource default values directory
     *
     * @param App $app
     * @throws RuntimeException
     * @return string
     */
    public function getAbsoluteAppResValuesPath(App $app);

    /**
    * Check if app has default values
    *
    * @param App $app
    * @return boolean
    */
    public function getHasAppDefaultValues(App $app);

    /**
     * Helper for getting relative path to app resource default values directory
     *
     * @param App $app
     * @return string
     */
    public function getRelativeAppResValuesPath(App $app);

    /**
     * Sets the configured app root directory
     *
     * @param string $appDirectory
     */
    public function setAppDirectory(string $appDirectory);
}
