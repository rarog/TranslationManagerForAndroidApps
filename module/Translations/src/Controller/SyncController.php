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

namespace Translations\Controller;

use Translations\Form\SyncExportForm;
use Translations\Form\SyncImportForm;
use Translations\Model\AppTable;
use Translations\Model\ResXmlParser;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer as Renderer;

class SyncController extends AbstractActionController
{
    /**
     * @var AppTable
     */
    private $appTable;

    /**
     * @var ResXmlParser
     */
    private $resXmlParser;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * Creates error page
     *
     * @return ViewModel
     */
    private function getAjaxError()
    {
        $this->getResponse()->setStatusCode(428);
        $view = new ViewModel([
            'message' => 'An Ajax request was expected',
        ]);
        $view->setTemplate('error/index');
        return $view;
    }

    /**
     * Check if current user has permission to the app and return it
     *
     * @param int $appId
     * @param bool $noRedirect
     * @return boolean|\Zend\Http\Response|\Translations\Model\App
     */
    private function getApp(int $appId, bool $noRedirect = false)
    {
        $app = $this->getAppIfAllowed($appId, true);

        if ($app === false) {
            if ($noRedirect) {
                return false;
            }
            return $this->redirect()->toRoute('app', [
                'action' => 'index',
            ]);
        }

        return $app;
    }

    /**
     * Renders JSON result containing HTML alert
     *
     * @param string $type
     * @param string $message
     * @return JsonModel
     */
    private function getJsonAlert(string $type, string $message)
    {
        $viewModel = new ViewModel([
            'type'     => $type,
            'message'  => $message,
            'canClose' => true,
        ]);
        $viewModel->setTemplate('partial/alert.phtml')
            ->setTerminal(true);

        return new JsonModel([
            $this->renderer->render($viewModel),
        ]);
    }

    /**
     * Constructor
     *
     * @param AppTable $appTable
     * @param ResXmlParser $resXmlParser
     * @param Translator $translator
     * @param Renderer $renderer
     */
    public function __construct(AppTable $appTable, ResXmlParser $resXmlParser, Translator $translator, Renderer $renderer)
    {
        $this->appTable = $appTable;
        $this->resXmlParser = $resXmlParser;
        $this->translator = $translator;
        $this->renderer = $renderer;
    }

    /**
     * Sync export action
     *
     * @return JsonModel
     */
    public function exportAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->getAjaxError();
        }

        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId, true);

        if ($app === false) {
            return $this->getJsonAlert('danger', 'Invalid app or no permission to access it');
        }

        $form = new SyncExportForm();
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            return $this->getJsonAlert('danger', $this->translator->translate('Invalid request'));
        }

        $confirmDeletion = (bool) $form->get('confirm_deletion')->getValue();

        $result = $this->resXmlParser->exportResourcesOfApp($app, $confirmDeletion);

        return $this->getJsonAlert('warning', 'Not implemented');
    }

    /**
     * Sync import action
     *
     * @return JsonModel
     */
    public function importAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->getAjaxError();
        }

        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId, true);

        if ($app === false) {
            return $this->getJsonAlert('danger', 'Invalid app or no permission to access it');
        }

        $form = new SyncImportForm();
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            return $this->getJsonAlert('danger', $this->translator->translate('Invalid request'));
        }

        $confirmDeletion = (bool) $form->get('confirm_deletion')->getValue();

        $result = $this->resXmlParser->importResourcesOfApp($app, $confirmDeletion);

        $type = 'success';
        $message = $this->translator->translate('Import successful') . '<br>' . sprintf($this->translator->translate('%d entries processed, %d updated'), $result->entriesProcessed, $result->entriesUpdated);

        if ($result->entriesSkippedExistOnlyInDb > 0) {
            if ($confirmDeletion) {
                $message .= '<br>' . sprintf($this->translator->translate('%d entries deleted from database'), $result->entriesSkippedExistOnlyInDb);
            } else {
                $message .= '<br>' . sprintf($this->translator->translate('%d entries skipped (exist only in database)'), $result->entriesSkippedExistOnlyInDb);
            }
        }
        if ($result->entriesSkippedNotInDefault > 0) {
            $message .= '<br>' . sprintf($this->translator->translate('%d entries skipped (not in default)'), $result->entriesSkippedNotInDefault);
            $type = 'warning';
        }

        return $this->getJsonAlert($type, $message);
    }

    /**
     * Sync overview action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        if ($app instanceof HttpResponse) {
            return $app;
        }

        $formImport = new SyncImportForm();
        $formExport = new SyncExportForm();

        return [
            'app' => $app,
            'formExport' => $formExport,
            'formImport' => $formImport,
        ];
    }
}
