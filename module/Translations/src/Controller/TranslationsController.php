<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Translations\Model\ResourceFileEntryStringTable;
use Zend\Escaper\Escaper;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer as Renderer;

class TranslationsController extends AbstractActionController
{
    /**
     * @var AppTable
     */
    private $appTable;

    /**
     * @var AppResourceTable
     */
    private $appResourceTable;

    /**
     * @var ResourceFileEntryStringTable
     */
    private $resourceFileEntryStringTable;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var ViewModel
     */
    private $viewModel;

    /**
     * Check if current user has permission to the app and return it
     *
     * @param int $appId
     * @return boolean|\Translations\Model\App
     */
    private function getApp($appId)
    {
        $appId = (int) $appId;

        if (0 === $appId) {
            return false;
        }

        try {
            $app = $this->appTable->getApp($appId);
        } catch (\Exception $e) {
            return false;
        }

        if (!$this->isGranted('app.viewAll') &&
            !$this->appTable->hasUserPermissionForApp(
                $this->zfcUserAuthentication()->getIdentity()->getId(),
                $app->Id)) {
            return false;
        }

        return $app;
    }

    /**
     * Get ViewModel for partial rendering
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function getViewModel()
    {
        if ($this->viewModel) {
            $this->viewModel->clearVariables();
        } else {
            $this->viewModel = new ViewModel();
            $this->viewModel->setTerminal(true);
        }

        return $this->viewModel;
    }

    /**
     * Renders ViewModel in template
     *
     * @param ViewModel $viewModel
     * @param unknown $template
     * @return string
     */
    private function renderTemplate(ViewModel $viewModel, $template)
    {
        $template = (string) $template;

        $viewModel->setTemplate($template);
        return $this->renderer->render($viewModel);
    }

    /**
     * Constructor
     *
     * @param AppTable $appTable
     * @param AppResourceTable $appResourceTable
     * @param ResourceFileEntryStringTable $resourceFileEntryStringTable
     * @param Translator $translator
     * @param Renderer $renderer
     */
    public function __construct(AppTable $appTable, AppResourceTable $appResourceTable, ResourceFileEntryStringTable $resourceFileEntryStringTable, Translator $translator, Renderer $renderer)
    {
        $this->appTable = $appTable;
        $this->appResourceTable = $appResourceTable;
        $this->resourceFileEntryStringTable = $resourceFileEntryStringTable;
        $this->translator = $translator;
        $this->renderer = $renderer;
    }

    /**
     * Translations overview action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $userId = 0;
        if (!$this->isGranted('team.viewAll')) {
            $userId = $this->zfcUserAuthentication()->getIdentity()->getId();
        }

        $localeNames = $this->configHelp('settings')->locale_names->toArray();
        $localeNames = $localeNames[$this->translator->getLocale()];

        $apps = [];
        $resources = [];
        $values = $this->appTable->getAllAppsAndResourcesAllowedToUser($userId);
        foreach ($values as $value) {
            if (!array_key_exists($value['app_id'], $apps)) {
                $apps[$value['app_id']] = $value['app_name'];
            }

            $resources[$value['app_id']][$value['app_resource_id']] = $localeNames[$value['locale']];
        }

        return [
            'apps' => $apps,
            'resources' => $resources,
        ];
    }

    /**
     * App resource add action
     *
     * @throws RuntimeException
     * @return \Zend\View\Model\ViewModel
     */
    public function listtranslationsAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $resourceId = (int) $this->params()->fromRoute('resourceId', 0);
        $defaultId = (int) $this->params()->fromRoute('defaultId', 0);

        $app = $this->getApp($appId);

        $viewModel = new JsonModel();
        $viewModel->setTerminal(true);

        if ($app === false) {
            return new JsonModel();
        }

        try {
            $resource = $this->appResourceTable->fetchAll([
                'id'     => $resourceId,
                'app_id' => $appId,
            ]);
        } catch (\Exception $e) {
            $resource === false;
        }

        if ($resource === false) {
            return new JsonModel();
        }

        $escaper = new Escaper('utf-8');

        $output = [];
        $entries = $this->resourceFileEntryStringTable->getAllResourceFileEntryStringsForTranslations($appId, $resourceId, $defaultId);
        foreach ($entries as $entry) {
            $viewModel = $this->getViewModel();
            $viewModel->setVariables($entry);

            $output[] = [
                'defaultId' => $entry['defaultId'],
                'name' => $entry['name'],
                'defaultValue' => $escaper->escapeHtml($entry['defaultValue']),
                'translatedValue' => $this->renderTemplate($viewModel, 'partial/translations-translatedValue.phtml'),//$escaper->escapeHtml($entry['value']),
                'buttons' => '',
            ];
        }
        return new JsonModel($output);
    }
}
