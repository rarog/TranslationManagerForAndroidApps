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
use RuntimeException;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Translations\Model\EntryCommonTable;
use Translations\Model\EntryStringTable;
use Translations\Model\ResourceFileEntryTable;
use Translations\Model\ResourceTypeTable;
use Translations\Model\Suggestion;
use Translations\Model\SuggestionString;
use Translations\Model\SuggestionStringTable;
use Translations\Model\SuggestionTable;
use Translations\Model\SuggestionVote;
use Translations\Model\SuggestionVoteTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer as Renderer;
use Translations\Model\EntryString;

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
     * @var EntryCommonTable
     */
    private $entryCommonTable;

    /**
     * @var EntryStringTable
     */
    private $entryStringTable;

    /**
     * @var SuggestionTable
     */
    private $suggestionTable;

    /**
     * @var SuggestionStringTable
     */
    private $suggestionStringTable;

    /**
     * @var SuggestionVoteTable
     */
    private $suggestionVoteTable;

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
        if (! is_array($this->resourceTypes)) {
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
     * @param EntryCommonTable $entryCommonTable
     * @param EntryStringTable $entryStringTable
     * @param SuggestionTable $suggestionTable
     * @param SuggestionStringTable $suggestionStringTable
     * @param SuggestionVoteTable $suggestionVoteTable
     * @param Translator $translator
     * @param Renderer $renderer
     */
    public function __construct(
        AppTable $appTable,
        AppResourceTable $appResourceTable,
        ResourceTypeTable $resourceTypeTable,
        ResourceFileEntryTable $resourceFileEntryTable,
        EntryCommonTable $entryCommonTable,
        EntryStringTable $entryStringTable,
        SuggestionTable $suggestionTable,
        SuggestionStringTable $suggestionStringTable,
        SuggestionVoteTable $suggestionVoteTable,
        Translator $translator,
        Renderer $renderer
    ) {
        $this->appTable = $appTable;
        $this->appResourceTable = $appResourceTable;
        $this->resourceTypeTable = $resourceTypeTable;
        $this->resourceFileEntryTable = $resourceFileEntryTable;
        $this->entryCommonTable = $entryCommonTable;
        $this->entryStringTable = $entryStringTable;
        $this->suggestionTable = $suggestionTable;
        $this->suggestionStringTable = $suggestionStringTable;
        $this->suggestionVoteTable = $suggestionVoteTable;
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

        $app = $this->getAppIfAllowed($appId);

        if ($app === false) {
            return new JsonModel();
        }

        $resource = $this->getResource($resourceId, $appId);

        if ($resource === false) {
            return new JsonModel();
        }

        try {
            $entry = $this->resourceFileEntryTable->getResourceFileEntry($entryId);
        } catch (RuntimeException $e) {
            return new JsonModel();
        }

        try {
            $type = $this->resourceTypeTable->getResourceType($entry->ResourceTypeId);
        } catch (RuntimeException $e) {
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
                $typedSuggestions = $this->suggestionStringTable->getAllSuggestionsForTranslations(
                    $typedEntry->id,
                    $this->zfcUserAuthentication()->getIdentity()->getId()
                );
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
        $values = $this->appTable->getAllAppsAndResourcesAllowedToUser(
            $this->zfcUserAuthentication()->getIdentity()->getId()
        );
        foreach ($values as $value) {
            if (! array_key_exists($value['app_id'], $apps)) {
                $apps[$value['app_id']] = $value['app_name'];
            }

            $resources[$value['app_id']][$value['app_resource_id']] = sprintf(
                '%s (%s)',
                $value['app_resource_name'],
                $localeNames[$value['locale']]
            );
        }

        $appsAll = [];
        $resourcesAll = [];
        if ($this->isGranted('team.viewAll')) {
            $values = $this->appTable->getAllAppsAndResourcesAllowedToUser(0);
            foreach ($values as $value) {
                if (! array_key_exists($value['app_id'], $appsAll)) {
                    $appsAll[$value['app_id']] = $value['app_name'];
                }

                $resourcesAll[$value['app_id']][$value['app_resource_id']] = sprintf(
                    '%s (%s)',
                    $value['app_resource_name'],
                    $localeNames[$value['locale']]
                );
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

        $app = $this->getAppIfAllowed($appId);

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
                'defaultId' => sprintf('translation-%d', $entry['defaultId']),
                'name' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/listtranslations-name.phtml'
                ),
                'product' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/listtranslations-product.phtml'
                ),
                'nameView' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/listtranslations-nameView.phtml'
                ),
                'defaultValue' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/listtranslations-defaultValue.phtml'
                ),
                'translatedValue' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/listtranslations-translatedValue.phtml'
                ),
                'buttons' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/listtranslations-buttons.phtml'
                ),
            ];
        }
        return new JsonModel($output);
    }

    /**
     * Translation entry notification status setting action
     *
     * @return JsonModel
     */
    public function setnotificationstatusAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $resourceId = (int) $this->params()->fromRoute('resourceId', 0);
        $entryId = (int) $this->params()->fromRoute('entryId', 0);
        $notificationStatus = (int) $this->params()->fromRoute('notificationStatus', 0);

        $app = $this->getAppIfAllowed($appId);

        if ($app === false) {
            return new JsonModel();
        }

        $resource = $this->getResource($resourceId, $appId);

        if ($resource === false) {
            return new JsonModel();
        }

        try {
            $entry = $this->resourceFileEntryTable->getResourceFileEntry($entryId);
        } catch (RuntimeException $e) {
            return new JsonModel();
        }

        try {
            $type = $this->resourceTypeTable->getResourceType($entry->getResourceTypeId());
        } catch (RuntimeException $e) {
            return new JsonModel();
        }

        switch ($type->Name) {
            case 'String':
                $typedEntry = $this->entryStringTable->getAllEntryStringsForTranslations(
                    $appId,
                    $resourceId,
                    $entryId
                );
                break;
            default:
                return new JsonModel();
        }

        if (count($typedEntry) == 1) {
            $typedEntry = $typedEntry[0];
        } else {
            return new JsonModel();
        }

        try {
            $entryCommon = $this->entryCommonTable->getEntryCommon($typedEntry->id);
        } catch (RuntimeException $e) {
            return new JsonModel();
        }

        $entryCommon->setNotificationStatus($notificationStatus);

        $this->entryCommonTable->saveEntryCommon($entryCommon);

        return new JsonModel([
            'notificationStatus' => $notificationStatus,
        ]);
    }

    /**
     * Translation suggestion accept action
     *
     * @return JsonModel
     */
    public function suggestionacceptAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $resourceId = (int) $this->params()->fromRoute('resourceId', 0);
        $entryId = (int) $this->params()->fromRoute('entryId', 0);
        $suggestionId = (int) $this->params()->fromRoute('suggestionId', 0);

        $app = $this->getAppIfAllowed($appId);

        if ($app === false) {
            return new JsonModel(['accepted' => false]);
        }

        $resource = $this->getResource($resourceId, $appId);

        if ($resource === false) {
            return new JsonModel(['accepted' => false]);
        }

        try {
            $entry = $this->resourceFileEntryTable->getResourceFileEntry($entryId);
        } catch (RuntimeException $e) {
            return new JsonModel(['accepted' => false]);
        }

        try {
            $type = $this->resourceTypeTable->getResourceType($entry->ResourceTypeId);
        } catch (RuntimeException $e) {
            return new JsonModel(['accepted' => false]);
        }

        switch ($type->Name) {
            case 'String':
                $typedEntry = $this->entryStringTable->getAllEntryStringsForTranslations($appId, $resourceId, $entryId);
                break;
            default:
                return new JsonModel(['accepted' => false]);
        }

        if (count($typedEntry) == 1) {
            $typedEntry = $typedEntry[0];
        } else {
            return new JsonModel(['accepted' => false]);
        }

        if ($suggestionId !== 0) {
            try {
                $entryCommon = $this->entryCommonTable->getEntryCommon($typedEntry->id);
            } catch (RuntimeException $e) {
                return new JsonModel(['accepted' => false]);
            }

            switch ($type->Name) {
                case 'String':
                    try {
                        $suggestionString = $this->suggestionStringTable->getSuggestionString($suggestionId);
                    } catch (RuntimeException $e) {
                        return new JsonModel(['accepted' => false]);
                    }

                    try {
                        $entryString = $this->entryStringTable->getEntryString($typedEntry->id);
                    } catch (RuntimeException $e) {
                        $entryString = new EntryString([
                            'entry_common_id' => $typedEntry->id,
                        ]);
                    }

                    $entryString->Value = $suggestionString->Value;
                    $this->entryStringTable->saveEntryString($entryString);
                    break;
                default:
                    return new JsonModel(['accepted' => false]);
            }

            $entryCommon->LastChange = time();
            $this->entryCommonTable->saveEntryCommon($entryCommon);
        }

        $this->suggestionTable->deleteSuggestionByEntryCommonId($typedEntry->id);

        return new JsonModel(['accepted' => true]);
    }

    /**
     * Translation suggestion add and edit action
     *
     * @return JsonModel
     */
    public function suggestionaddeditAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $resourceId = (int) $this->params()->fromRoute('resourceId', 0);
        $entryId = (int) $this->params()->fromRoute('entryId', 0);
        $suggestionId = (int) $this->params()->fromRoute('suggestionId', 0);
        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();

        $app = $this->getAppIfAllowed($appId);

        if ($app === false) {
            return new JsonModel();
        }

        $resource = $this->getResource($resourceId, $appId);

        if ($resource === false) {
            return new JsonModel();
        }

        try {
            $entry = $this->resourceFileEntryTable->getResourceFileEntry($entryId);
        } catch (RuntimeException $e) {
            return new JsonModel();
        }

        try {
            $type = $this->resourceTypeTable->getResourceType($entry->ResourceTypeId);
        } catch (RuntimeException $e) {
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

        if ($suggestionId !== 0) {
            switch ($type->Name) {
                case 'String':
                    $typedSuggestion = $this->suggestionStringTable->getAllSuggestionsForTranslations(
                        $typedEntry->id,
                        $userId,
                        $suggestionId
                    );
                    break;
            }

            if (count($typedSuggestion) == 1) {
                $typedSuggestion = $typedSuggestion[0];
            } else {
                return new JsonModel();
            }

            if ($typedSuggestion->userId !== $userId) {
                return new JsonModel();
            }
        }

        $request = $this->getRequest();

        switch ($type->Name) {
            case 'String':
                $value = trim($request->getPost('value', ''));
                if (strlen($value) === 0) {
                    return new JsonModel();
                }
                break;
        }

        $typedSuggestion = null;

        if ($suggestionId === 0) {
            $suggestion = new Suggestion([
                'entry_common_id' => $typedEntry->id,
                'user_id' => $userId,
                'last_change' => time(),
            ]);
            $suggestion = $this->suggestionTable->saveSuggestion($suggestion);

            $suggestionVote = new SuggestionVote([
                'suggestion_id' => $suggestion->Id,
                'user_id' => $userId,
            ]);
            $this->suggestionVoteTable->saveSuggestionVote($suggestionVote);

            switch ($type->Name) {
                case 'String':
                    $suggestionString = new SuggestionString([
                        'suggestion_id' => $suggestion->Id,
                        'value' => $value,
                    ]);
                    $this->suggestionStringTable->saveSuggestionString($suggestionString);

                    $typedSuggestion = $this->suggestionStringTable->getAllSuggestionsForTranslations(
                        $typedEntry->id,
                        $userId,
                        $suggestion->Id
                    );
                    break;
            }
        } else {
            try {
                $suggestion = $this->suggestionTable->getSuggestion($suggestionId);
            } catch (RuntimeException $e) {
                return new JsonModel();
            }

            $suggestion->LastChange = time();
            $this->suggestionTable->saveSuggestion($suggestion);

            switch ($type->Name) {
                case 'String':
                    try {
                        $suggestionString = $this->suggestionStringTable->getSuggestionString($suggestionId);
                    } catch (RuntimeException $e) {
                        return new JsonModel();
                    }

                    $suggestionString->Value = $value;
                    $this->suggestionStringTable->saveSuggestionString($suggestionString);

                    $typedSuggestion = $this->suggestionStringTable->getAllSuggestionsForTranslations(
                        $typedEntry->id,
                        $userId,
                        $suggestionId
                    );
                    break;
            }
        }

        if (count($typedSuggestion) == 1) {
            $typedSuggestion = $typedSuggestion[0];
        } else {
            return new JsonModel();
        }

        $viewModel = $this->getViewModel();
        $viewModel->setVariables([
            'entryId' => $entryId,
            'suggestion' => $typedSuggestion,
            'type' => $type->Name,
        ]);

        return new JsonModel([
            'suggestion' => [
                'suggestionId' => sprintf('suggestion-%d', $typedSuggestion->id),
                'suggestion' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/details-suggestion-suggestion.phtml'
                ),
                'username' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/details-suggestion-username.phtml'
                ),
                'votes' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/details-suggestion-vote.phtml'
                ),
                'buttons' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/details-suggestion-buttons.phtml'
                ),
            ],
        ]);
    }


    /**
     * Translation suggestion add and edit action
     *
     * @return JsonModel
     */
    public function suggestiondeleteAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $resourceId = (int) $this->params()->fromRoute('resourceId', 0);
        $entryId = (int) $this->params()->fromRoute('entryId', 0);
        $suggestionId = (int) $this->params()->fromRoute('suggestionId', 0);
        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();

        $app = $this->getAppIfAllowed($appId);

        if ($app === false) {
            return new JsonModel(['deleted' => false]);
        }

        $resource = $this->getResource($resourceId, $appId);

        if ($resource === false) {
            return new JsonModel(['deleted' => false]);
        }

        try {
            $entry = $this->resourceFileEntryTable->getResourceFileEntry($entryId);
        } catch (RuntimeException $e) {
            return new JsonModel(['deleted' => false]);
        }

        try {
            $type = $this->resourceTypeTable->getResourceType($entry->ResourceTypeId);
        } catch (RuntimeException $e) {
            return new JsonModel(['deleted' => false]);
        }

        switch ($type->Name) {
            case 'String':
                $typedEntry = $this->entryStringTable->getAllEntryStringsForTranslations($appId, $resourceId, $entryId);
                break;
            default:
                return new JsonModel(['deleted' => false]);
        }

        if (count($typedEntry) == 1) {
            $typedEntry = $typedEntry[0];
        } else {
            return new JsonModel(['deleted' => false]);
        }

        if ($suggestionId !== 0) {
            switch ($type->Name) {
                case 'String':
                    $typedSuggestion = $this->suggestionStringTable->getAllSuggestionsForTranslations(
                        $typedEntry->id,
                        $userId,
                        $suggestionId
                    );
                    break;
            }

            if (count($typedSuggestion) == 1) {
                $typedSuggestion = $typedSuggestion[0];
            } else {
                return new JsonModel(['deleted' => false]);
            }

            if ($typedSuggestion->userId !== $userId) {
                return new JsonModel(['deleted' => false]);
            }
        }

        $this->suggestionTable->deleteSuggestion($suggestionId);

        return new JsonModel(['deleted' => true]);
    }

    /**
     * Translation suggestion vote action
     *
     * @return JsonModel
     */
    public function suggestionvoteAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $resourceId = (int) $this->params()->fromRoute('resourceId', 0);
        $entryId = (int) $this->params()->fromRoute('entryId', 0);
        $suggestionId = (int) $this->params()->fromRoute('suggestionId', 0);
        $vote = (bool) $this->params()->fromRoute('vote', 0);
        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();

        $app = $this->getAppIfAllowed($appId);

        if ($app === false) {
            return new JsonModel();
        }

        $resource = $this->getResource($resourceId, $appId);

        if ($resource === false) {
            return new JsonModel();
        }

        try {
            $entry = $this->resourceFileEntryTable->getResourceFileEntry($entryId);
        } catch (RuntimeException $e) {
            return new JsonModel();
        }

        try {
            $type = $this->resourceTypeTable->getResourceType($entry->ResourceTypeId);
        } catch (RuntimeException $e) {
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

        // 1) Check if this is a valid suggestion.
        switch ($type->Name) {
            case 'String':
                $typedSuggestion = $this->suggestionStringTable->getAllSuggestionsForTranslations(
                    $typedEntry->id,
                    $userId,
                    $suggestionId
                );
                break;
        }
        if (count($typedSuggestion) != 1) {
            return new JsonModel();
        }

        // 2) Cast or remove vote.
        if ($vote) {
            $suggestionVote = new SuggestionVote([
                'suggestion_id' => $suggestionId,
                'user_id' => $userId,
            ]);
            $this->suggestionVoteTable->saveSuggestionVote($suggestionVote);
        } else {
            $this->suggestionVoteTable->deleteSuggestionVote($suggestionId, $userId);
        }

        // 3) Get new version of the suggestion with updated vote count.
        switch ($type->Name) {
            case 'String':
                $typedSuggestion = $this->suggestionStringTable->getAllSuggestionsForTranslations(
                    $typedEntry->id,
                    $userId,
                    $suggestionId
                );
                break;
        }

        if (count($typedSuggestion) == 1) {
            $typedSuggestion = $typedSuggestion[0];
        } else {
            return new JsonModel();
        }

        $viewModel = $this->getViewModel();
        $viewModel->setVariables([
            'entryId' => $entryId,
            'suggestion' => $typedSuggestion,
            'type' => $type->Name,
        ]);

        return new JsonModel([
            'suggestion' => [
                'suggestionId' => sprintf('suggestion-%d', $typedSuggestion->id),
                'suggestion' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/details-suggestion-suggestion.phtml'
                ),
                'username' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/details-suggestion-username.phtml'
                ),
                'votes' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/details-suggestion-vote.phtml'
                ),
                'buttons' => $this->renderTemplate(
                    $viewModel,
                    'translations/translations/partial/details-suggestion-buttons.phtml'
                ),
            ],
        ]);
    }
}
