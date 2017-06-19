<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model\Helper;

use Translations\Model\App;

trait AppHelperTrait
{
    /**
     * @var string
     */
    private $appDirectory;

    /**
     * Helper for getting absolute path to app resource default values directory
     *
     * @param App $app
     * @throws RuntimeException
     * @return string
     */
    public function getAbsoluteAppResValuesPath(App $app)
    {
        if (($path = realpath($this->appDirectory)) === false) {
            throw new RuntimeException(sprintf(
                'Configured path app directory "%s" does not exist',
                $this->configHelp('tmfaa')->app_dir));
        }
        return FileHelper::concatenatePath($path, $this->getRelativeAppResValuesPath($app));
    }

    /**
     * Helper for getting relative path to app resource default values directory
     *
     * @param App $app
     * @return string
     */
    public function getRelativeAppResValuesPath(App $app)
    {
        $path = FileHelper::concatenatePath((string) $app->Id, $app->pathToResFolder);
        $path = FileHelper::concatenatePath($path, 'res');
        return FileHelper::concatenatePath($path, 'values');
    }

    /**
     * Sets the configured app root directory
     *
     * @param string $appDirectory
     */
    public function setAppDirectory($appDirectory)
    {
        $appDirectory = (string) $appDirectory;
        $this->appDirectory = $appDirectory;
    }
}
