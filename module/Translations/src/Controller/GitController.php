<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use GitWrapper\GitWrapper;
use RuntimeException;
use Translations\Model\App;
use Translations\Model\AppTable;
use Translations\Model\Helper\FileHelper;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class GitController extends AbstractActionController
{
    /**
     * @var AppTable
     */
    private $appTable;

    /**
     * Check if current user has permission to the app and return it
     *
     * @param int $appId
     * @return void|\Zend\Http\Response|\Translations\Model\App
     */
    private function getApp($appId)
    {
        $appId = (int) $appId;

        if (0 === $appId) {
            return $this->redirect()->toRoute('app', [
                'action' => 'index',
            ]);
        }

        try {
            $app = $this->appTable->getApp($appId);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('app', [
                'action' => 'index',
            ]);
        }

        if (!$this->isGranted('app.viewAll') &&
            !$this->appTable->hasUserPermissionForApp(
                $this->zfcUserAuthentication()->getIdentity()->getId(),
                $app->Id)) {
            return $this->redirect()->toRoute('app', [
                'action' => 'index',
            ]);
        }

        return $app;
    }

    /**
     * Returns local git repository of the app
     *
     * @param App $app
     * @throws RuntimeException
     * @return \GitWrapper\GitWorkingCopy
     */
    private function getGit(App $app)
    {
        if (($path = realpath($this->configHelp('tmfaa')->app_dir)) === false) {
            throw new RuntimeException(sprintf(
                    'Configured path app directory "%s" does not exist',
                    $this->configHelp('tmfaa')->app_dir
                    ));
        }
        $path = FileHelper::concatenatePath($path, (string) $app->Id);

        $git = new GitWrapper();
        $git->setEnvVar('HOME', $path);
        return $git->workingCopy($path);
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
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        return [
            'app' => $app,
            'git' => $this->getGit($app),
        ];
    }
}
