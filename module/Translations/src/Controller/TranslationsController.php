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

use ArrayObject;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Translations\Model\EntryStringTable;
use Translations\Model\ResourceFileEntryTable;
use Translations\Model\ResourceTypeTable;
use Translations\Model\SuggestionStringTable;
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
     * @var ResourceTypeTable
     */
    private $resourceTypeTable;

    /**
     * @var ResourceFileEntryTable
     */
    private $resourceFileEntryTable;

    /**
     * @var EntryStringTable
     */
    private $entryStringTable;

    /**
     * @var SuggestionStringTable
     */
    private $suggestionsStringTable;

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
     * @var array
     */
    private $resourceTypes;

    /**
     * Check if current user has permission to the app and return it
     *
     * @param int $appId
     * @return boolean|\Translations\Model\App
     */
    private function getApp(int $appId)
    {
        return $this->getAppIfAllowed($appId);
    }

    /**
     * Gets app resource
     *
     * @param int $resourceId
     * @param int $appId
     * @return boolean|\Translations\Model\AppResource
     */
    private function getResource(int $resourceId, int $appId)
    {
        if ((0 === $resourceId) || (0 === $appId)) {
            return false;
        }

        $rowset = $this->appResourceTable->fetchAll([
            'id' => $resourceId,
            'app_id' => $appId,
        ]);
        $resource = $rowset->current();
        if (! $resource) {
            return false;
        }

        return $resource;
    }

    /**
     * Gets array of all supported resource types
     *
     * @return array
     */
    private function getResourceTypes()
    {
        if (!is_array($this->resourceTypes)) {
            $this->resourceTypes = [];
            foreach ($this->resourceTypeTable->fetchAll() as $resourceType) {
                $this->resourceTypes[$resourceType->Id] = $resourceType->NodeName;
            }
        }

        return $this->resourceTypes;
    }

    /**
     * Get ViewModel for partial rendering
     *
     * @return ViewModel
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
     * @param string $template
     * @return string
     */
    private function renderTemplate(ViewModel $viewModel, string $template)
    {
        $viewModel->setTemplate($template);
        return $this->renderer->render($viewModel);
    }

    /**
     * Constructor
     *
     * @param AppTable $appTable
     * @param AppResourceTable $appResourceTable
     * @param ResourceTypeTable $resourceTypeTable
     * @param ResourceFileEntryTable $resourceFileEntryTable
     * @param EntryStringTable $entryStringTable
     * @param SuggestionStringTable $suggestionsStringTable
     * @param Translator $translator
     * @param Renderer $renderer
     */
    public function __construct(AppTable $appTable, AppResourceTable $appResourceTable, ResourceTypeTable $resourceTypeTable, ResourceFileEntryTable $resourceFileEntryTable, EntryStringTable $entryStringTable, SuggestionStringTable $suggestionsStringTable, Translator $translator, Renderer $renderer)
    {
        $this->appTable = $appTable;
        $this->appResourceTable = $appResourceTable;
        $this->resourceTypeTable = $resourceTypeTable;
        $this->resourceFileEntryTable = $resourceFileEntryTable;
        $this->entryStringTable = $entryStringTable;
        $this->suggestionsStringTable = $suggestionsStringTable;
        $this->translator = $translator;
        $this->renderer = $renderer;
    }

    /**
     * Translation detail management action
     *
     * @return JsonModel
     */
    public function detailsAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $resourceId = (int) $this->params()->fromRoute('resourceId', 0);
        $entryId = (int) $this->params()->fromRoute('entryId', 0);

        $app = $this->getApp($appId);

        if ($app === false) {
            return new JsonModel();
        }

        $resource = $this->getResource($resourceId, $appId);

        if ($resource === false) {
            return new JsonModel();
        }

        try {
            $entry = $this->resourceFileEntryTable->getResourceFileEntry($entryId);
        } catch (\RuntimeException$e) {
            return new JsonModel();
        }

        try {
            $type = $this->resourceTypeTable->getResourceType($entry->ResourceTypeId);
        } catch (\RuntimeException $e) {
            return new JsonModel();
        }

        switch ($type->Name) {
            case 'String':
                $typedEntry = $this->entryStringTable->getAllEntryStringsForTranslations($appId, $resourceId, $entryId);
                break;
            default:
                return new JsonModel();
        }

        if (count($typedEntry) == 1) {
            $typedEntry = $typedEntry[0];
        } else {
            return new JsonModel();
        }

        switch ($type->Name) {
            case 'String':
                $typedSuggestions = $this->suggestionsStringTable->getAllSuggestionsForTranslations($typedEntry['id']);
                break;
        }

        $viewModel = $this->getViewModel();
        $viewModel->setVariables([
            'entry' => $typedEntry,
            'suggestions' => $typedSuggestions,
            'type' => $type->Name,
        ]);

        return new JsonModel([
            'modal' => $this->renderTemplate($viewModel, 'translations/translations/details.phtml'),
        ]);
    }

    /**
     * Translations overview action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $localeNames = $this->configHelp('settings')->locale_names->toArray();
        $localeNames = $localeNames[$this->translator->getLocale()];

        $apps = [];
        $resources = [];
        $values = $this->appTable->getAllAppsAndResourcesAllowedToUser($this->zfcUserAuthentication()->getIdentity()->getId());
        foreach ($values as $value) {
            if (!array_key_exists($value['app_id'], $apps)) {
                $apps[$value['app_id']] = $value['app_name'];
            }

            $resources[$value['app_id']][$value['app_resource_id']] = sprintf('%s (%s)', $value['app_resource_name'], $localeNames[$value['locale']]);
        }

        $appsAll = [];
        $resourcesAll = [];
        if ($this->isGranted('team.viewAll')) {
            $values = $this->appTable->getAllAppsAndResourcesAllowedToUser(0);
            foreach ($values as $value) {
                if (!array_key_exists($value['app_id'], $appsAll)) {
                    $appsAll[$value['app_id']] = $value['app_name'];
                }

                $resourcesAll[$value['app_id']][$value['app_resource_id']] = sprintf(
                        '%s (%s)',
                        $value['app_resource_name'],
                        $localeNames[$value['locale']]);
            }
        }

        return [
            'apps' => $apps,
            'appsAll' => $appsAll,
            'resources' => $resources,
            'resourcesAll' => $resourcesAll,
        ];
    }

    /**
     * Translation listing action
     *
     * @return JsonModel
     */
    public function listtranslationsAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $resourceId = (int) $this->params()->fromRoute('resourceId', 0);
        $entryId = (int) $this->params()->fromRoute('entryId', 0);

        $app = $this->getApp($appId);

        if ($app === false) {
            return new JsonModel();
        }

        $resource = $this->getResource($resourceId, $appId);

        if ($resource === false) {
            return new JsonModel();
        }

        $output = [];
        $entries = $this->entryStringTable->getAllEntryStringsForTranslations($appId, $resourceId, $entryId);
        foreach ($entries as $entry) {
            $viewModel = $this->getViewModel();
            $viewModel->setVariables([
                'entry' => new ArrayObject($entry, ArrayObject::ARRAY_AS_PROPS),
                'resourceTypes' => $this->getResourceTypes(),
            ]);

            $output[] = [
                'defaultId' => $entry['defaultId'],
                'name' => $this->renderTemplate($viewModel, 'translations/translations/partial/listtranslations-name.phtml'),
                'product' => $this->renderTemplate($viewModel, 'translations/translations/partial/listtranslations-product.phtml'),
                'nameView' => $this->renderTemplate($viewModel, 'translations/translations/partial/listtranslations-nameView.phtml'),
                'defaultValue' => $this->renderTemplate($viewModel, 'translations/translations/partial/listtranslations-defaultValue.phtml'),
                'translatedValue' => $this->renderTemplate($viewModel, 'translations/translations/partial/listtranslations-translatedValue.phtml'),
                'buttons' => $this->renderTemplate($viewModel, 'translations/translations/partial/listtranslations-buttons.phtml'),
            ];
        }
        return new JsonModel($output);
    }
}
