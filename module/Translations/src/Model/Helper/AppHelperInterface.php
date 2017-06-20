<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model\Helper;

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
    public function setAppDirectory($appDirectory);
}
