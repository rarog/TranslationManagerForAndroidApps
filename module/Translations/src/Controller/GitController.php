<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use GitWrapper\GitWrapper;
use RuntimeException;
use Translations\Form\GitCloneForm;
use Translations\Model\App;
use Translations\Model\AppTable;
use Translations\Model\Helper\FileHelper;
use Zend\InputFilter\InputFilter;
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
     * @var Translator
     */
    private $translator;

    /**
     * @var string
     */
    private $appPath;

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
     * Helper for getting path to app directory
     *
     * @param App $app
     * @throws RuntimeException
     * @return string
     */
    private function getAppPath(App $app)
    {
        if (!isset($this->appPath)) {
            if (($path = realpath($this->configHelp('tmfaa')->app_dir)) === false) {
                throw new RuntimeException(sprintf(
                    'Configured path app directory "%s" does not exist',
                    $this->configHelp('tmfaa')->app_dir
                    ));
            }
            $path = FileHelper::concatenatePath($path, (string) $app->Id);

            $this->appPath = $path;
        }

        return $this->appPath;
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
        $path = $this->getAppPath($app);

        $git = new GitWrapper();
        $git->setEnvVar('HOME', $path);
        $git->setTimeout(300);
        return $git->workingCopy($path);
    }

    /**
     * Constructor
     *
     * @param AppTable $appTable
     * @param Translator $translator
     */
    public function __construct(AppTable $appTable, Translator $translator)
    {
        $this->appTable = $appTable;
        $this->translator = $translator;
    }

    /**
     * Git clone action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function cloneAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        $inputFilter = new InputFilter();
        $inputFilter->add([
            'name'     => 'confirm_deletion',
            'required' => true,
        ]);

        $form = new GitCloneForm();
        $form->setInputFilter($inputFilter);

        $viewData = [
            'app'  => $app,
            'form' => $form,
            'messages' => [],
        ];

        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $viewData;
        }

        if ($request->getPost('back', '') === 'back') {
            return $this->redirect()->toRoute('git', [
                'appId'  => $app->Id,
                'action' => 'index',
            ]);
        }

        $form->setData($request->getPost());

        if (($request->getPost('clone', '') === 'clone') &&
            $form->isValid()) {
            $form->get('confirm_deletion')->setAttribute('disabled', 'disabled');
            $form->get('clone')->setAttribute('disabled', 'disabled');

            try {
                FileHelper::rmdirRecursive($this->getAppPath($app), true);

                $git = $this->getGit($app);
                $git->cloneRepository($app->GitRepository);

                $viewData['messages'][] = [
                    'canClose' => true,
                    'message'  => $this->translator->translate('Git cloning successful'),
                    'type'     => 'success',
                ];
            } catch (\Exception $e) {
                $viewData['messages'][] = [
                    'canClose' => true,
                    'message'  => $e->getMessage(),
                    'type'     => 'danger',
                ];
            }
        }

        return $viewData;
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