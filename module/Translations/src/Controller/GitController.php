<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use RuntimeException;
use Translations\Form\AppForm;
use Translations\Form\DeleteHelperForm;
use Translations\Model\App;
use Translations\Model\AppTable;
use Translations\Model\Helper\FileHelper;
use Translations\Model\TeamTable;
use Translations\Model\UserSettingsTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\ViewModel;

class GitController extends AbstractActionController
{
    /**
     * @var AppTable
     */
    private $appTable;

    /**
     * Helper for getting path to app directory
     *
     * @param int $id
     * @throws RuntimeException
     * @return string
     */
    private function getAppPath($id)
    {
        if (($path = realpath($this->configHelp('tmfaa')->app_dir)) === false) {
            throw new RuntimeException(sprintf(
                    'Configured path app directory "%s" does not exist',
                    $this->configHelp('tmfaa')->app_dir
                    ));
        }
        return FileHelper::concatenatePath($path, (string) $id);
    }

    /**
     * Constructor
     *
     * @param AppTable $appTable
     */
    public function __construct(AppTable $appTable)
    {
        $this->appTable = $appTable;
    }

    /**
     * Git overview action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return [
        ];
    }
}
