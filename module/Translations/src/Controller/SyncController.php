<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use RuntimeException;
use Translations\Form\SyncExportForm;
use Translations\Form\SyncImportForm;
use Translations\Model\App;
use Translations\Model\AppResourceFileTable;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Translations\Model\Helper\AppHelperInterface;
use Translations\Model\Helper\AppHelperTrait;
use Translations\Model\Helper\FileHelper;
use Translations\Model\ResourceFileEntry;
use Translations\Model\ResourceFileEntryString;
use Translations\Model\ResourceFileEntryStringTable;
use Translations\Model\ResourceFileEntryTable;
use Translations\Model\ResourceTypeTable;
use Translations\Model\ResXmlParser;
use Zend\Dom\Document;
use Zend\Dom\Document\Query;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer as Renderer;

class SyncController extends AbstractActionController implements AppHelperInterface
{
    use AppHelperTrait;

    /**
     * @var AppTable
     */
    private $appTable;

    /**
     * @var AppResourceTable
     */
    private $appResourceTable;

    /**
     * @var AppResourceFileTable
     */
    private $appResourceFileTable;

    /**
     * @var ResourceTypeTable
     */
    private $resourceTypeTable;

    /**
     * @var ResourceFileEntryTable
     */
    private $resourceFileEntryTable;

    /**
     * @var ResourceFileEntryStringTable
     */
    private $resourceFileEntryStringTable;

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
     * @var string
     */
    private $appPath;

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

        // Prevent further action, if default values don't exist.
        if (!$this->getHasAppDefaultValues($app)) {
            return $this->redirect()->toRoute('appresource', [
                'appId'  => $app->Id,
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
                throw new RuntimeException(sprintf('Configured path app directory "%s" does not exist', $this->configHelp('tmfaa')->app_dir));
            }
            $path = FileHelper::concatenatePath($path, (string) $app->Id);

            $this->appPath = $path;
        }

        return $this->appPath;
    }

    /**
     * Renders JSON result containing HTML alert
     *
     * @param string $type
     * @param string $message
     * @return JsonModel
     */
    private function getJsonAlert($type, $message)
    {
        $type = (string) $type;
        $message = (string) $message;

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
     * @param AppResourceTable $appResourceTable
     * @param AppResourceFileTable $appResourceFileTable
     * @param ResourceTypeTable $resourceTypeTable
     * @param ResourceFileEntryTable $resourceFileEntryTable
     * @param ResourceFileEntryStringTable $resourceFileEntryStringTable
     * @param ResXmlParser $resXmlParser
     * @param Translator $translator
     * @param Renderer $renderer
     */
    public function __construct(AppTable $appTable, AppResourceTable $appResourceTable, AppResourceFileTable $appResourceFileTable, ResourceTypeTable $resourceTypeTable, ResourceFileEntryTable $resourceFileEntryTable, ResourceFileEntryStringTable $resourceFileEntryStringTable, ResXmlParser $resXmlParser, Translator $translator, Renderer $renderer)
    {
        $this->appTable = $appTable;
        $this->appResourceTable = $appResourceTable;
        $this->appResourceFileTable = $appResourceFileTable;
        $this->resourceTypeTable = $resourceTypeTable;
        $this->resourceFileEntryTable = $resourceFileEntryTable;
        $this->resourceFileEntryStringTable = $resourceFileEntryStringTable;
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
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        $request = $this->getRequest();

        if (!$request->isXmlHttpRequest()) {
            return $this->getAjaxError();
        }

        $form = new SyncExportForm();
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            return $this->getJsonAlert('danger', $this->translator->translate('Invalid request'));
        }

        $confirmDeletion = (bool) $form->get('confirm_deletion')->getValue();

        $path = $this->getAbsoluteAppResValuesPath($app);

        $resources = $this->appResourceTable->fetchAll(['app_id' => $app->Id]);
        $resources->buffer();
        $resourceFiles = $this->appResourceFileTable->fetchAll(['app_id' => $app->Id]);
        $resourceFiles->buffer();
        $resourceTypes = [];
        foreach ($this->resourceTypeTable->fetchAll() as $resourceType) {
            $resourceTypes[$resourceType->Id] = $resourceType->NodeName;
        }

        foreach ($resources as $resource) {
            $pathRes = FileHelper::concatenatePath($path, $resource->Name);

            foreach ($resourceFiles as $resourceFile) {
                $pathResFile = FileHelper::concatenatePath($pathRes, $resourceFile->Name);
            }
        }

        return $this->getJsonAlert('warning', 'Not implemented');
    }

    /**
     * Sync import action
     *
     * @return JsonModel
     */
    public function importAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);
        $this->setAppDirectory($this->configHelp('tmfaa')->app_dir);

        $request = $this->getRequest();

        if (!$request->isXmlHttpRequest()) {
            return $this->getAjaxError();
        }

        $form = new SyncImportForm();
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            return $this->getJsonAlert('danger', $this->translator->translate('Invalid request'));
        }

        $confirmDeletion = (bool) $form->get('confirm_deletion')->getValue();

        $path = $this->getAbsoluteAppResPath($app);

        $resources = $this->appResourceTable->fetchAll(['app_id' => $app->Id]);
        $resources->buffer();
        $resourceFiles = $this->appResourceFileTable->fetchAll(['app_id' => $app->Id]);
        $resourceFiles->buffer();
        $resourceTypes = [];
        foreach ($this->resourceTypeTable->fetchAll() as $resourceType) {
            $resourceTypes[$resourceType->Id] = $resourceType->NodeName;
        }

        if (count($resourceTypes) == 0) {
            return $this->getJsonAlert('danger', $this->translator->translate('No resource types are known'));
        }

        $querySelectors = [];
        foreach ($resourceTypes as $resourceType) {
            $querySelectors[] = '/resources/' . $resourceType;
        };
        $querySelector = implode('|', $querySelectors);

        $resourceFileEntries = [];
        $resourceFileEntryKeys = [];
        $timestamp = strtotime(gmdate('Y-m-d H:i:s'));
        $entriesProcessed = 0;
        $entriesUpdated = 0;
        $entriesSkippedExistOnlyInDb = 0;
        $entriesSkippedNotInDefault = 0;

        foreach ($resources as $resource) {
            $pathRes = FileHelper::concatenatePath($path, $resource->Name);

            $resourceFileEntryStrings = [];
            foreach ($this->resourceFileEntryStringTable->fetchAll(['app_resource_id' => $resource->Id]) as $resourceFileEntryString) {
                $resourceFileEntryStrings[$resourceFileEntryString->ResourceFileEntryId] = $resourceFileEntryString;
            }

            foreach ($resourceFiles as $resourceFile) {
                $pathResFile = FileHelper::concatenatePath($pathRes, $resourceFile->Name);

                if (!FileHelper::isFileValidResource($pathResFile)) {
                    continue;
                }

                if (!array_key_exists($resourceFile->Name, $resourceFileEntries)) {
                    $resourceFileEntries[$resourceFile->Name] = [];
                    $resourceFileEntryKeys[$resourceFile->Name] = [];
                    foreach ($this->resourceFileEntryTable->fetchAll(['app_resource_file_id' => $resourceFile->Id, 'deleted' => 0]) as $entry) {
                        $combinedKey = $entry->Name . "\n" . $entry->Product;
                        $resourceFileEntries[$resourceFile->Name][$combinedKey] = $entry;
                        $resourceFileEntryKeys[$resourceFile->Name][$combinedKey] = $entry;
                    }
                }

                $dom = new Document(file_get_contents($pathResFile));
                $query = new Query();
                $nodes = $query->execute($querySelector, $dom);

                foreach ($nodes as $node) {
                    /**
                     * @var \DOMNamedNodeMap $attributes
                     */
                    $attributes = $node->attributes;
                    if (is_null($attributes)) {
                        continue;
                    }

                    $attribute = $attributes->getNamedItem('name');
                    if (is_null($attribute)) {
                        continue;
                    }
                    $name = $attribute->value;

                    $product = 'default';
                    $attribute = $attributes->getNamedItem('product');
                    if (!is_null($attribute) && !empty($attribute->value)) {
                        $product = $attribute->value;
                    }

                    $description = '';
                    $translatable = true;
                    if ($resource->Name === 'values') {
                        $attribute = $attributes->getNamedItem('translatable');
                        if (! is_null($attribute)) {
                            $translatable = $attribute->value !== 'false';
                        }

                        $attribute = $attributes->getNamedItem('translation_description');
                        if (! is_null($attribute)) {
                            $description = $attribute->value;
                        } else {
                            $previousSibling = $node->previousSibling;
                            while (! is_null($previousSibling) && ($previousSibling instanceof \DOMText) && $previousSibling->isWhitespaceInElementContent()) {
                                $previousSibling = $previousSibling->previousSibling;
                            }
                            if (! is_null($previousSibling) && ($previousSibling instanceof \DOMComment)) {
                                $description = $previousSibling->textContent;
                            }
                        }
                    }

                    $combinedKey = $name. "\n" . $product;

                    if (! array_key_exists($combinedKey, $resourceFileEntries[$resourceFile->Name])) {
                        if ($resource->Name === 'values') {
                            $resourceFileEntry = new ResourceFileEntry();
                            $resourceFileEntry->AppResourceFileId = $resourceFile->Id;
                            $resourceFileEntry->ResourceTypeId = array_search($node->tagName, $resourceTypes);
                            $resourceFileEntry->Name = $name;
                            $resourceFileEntry->Product = $product;
                            $resourceFileEntry->Description = $description;
                            $resourceFileEntry->Translatable = $translatable;
                            $resourceFileEntries[$resourceFile->Name][$combinedKey] = $this->resourceFileEntryTable->saveResourceFileEntry($resourceFileEntry);
                        } else {
                            $entriesSkippedNotInDefault++;
                            continue;
                        }
                    }

                    if (($resource->Name === 'values') && array_key_exists($combinedKey, $resourceFileEntryKeys[$resourceFile->Name])) {
                        unset($resourceFileEntryKeys[$resourceFile->Name][$combinedKey]);
                    }

                    /**
                     * @var ResourceFileEntry $resourceFileEntry
                     */
                    $resourceFileEntry = $resourceFileEntries[$resourceFile->Name][$combinedKey];
                    $entryAlreadyUpdated = false;

                    if ($resource->Name === 'values') {
                        if ($resourceFileEntry->ResourceTypeId !== array_search($node->tagName, $resourceTypes)) {
                            $resourceFileEntry->Deleted = true;
                            $this->resourceFileEntryTable->saveResourceFileEntry($resourceFileEntry);

                            $resourceFileEntry = new ResourceFileEntry();
                            $resourceFileEntry->AppResourceFileId = $resourceFile->Id;
                            $resourceFileEntry->ResourceTypeId = array_search($node->tagName, $resourceTypes);
                            $resourceFileEntry->Name = $name;
                            $resourceFileEntry->Product = $product;
                            $resourceFileEntry->Description = $description;
                            $resourceFileEntry->Translatable = $translatable;
                            $resourceFileEntries[$resourceFile->Name][$combinedKey] = $this->resourceFileEntryTable->saveResourceFileEntry($resourceFileEntry);
                        } elseif ($resourceFileEntry->Description != $description || $resourceFileEntry->Translatable !== $translatable) {
                            $resourceFileEntry->Description = $description;
                            $resourceFileEntry->Translatable = $translatable;
                            $this->resourceFileEntryTable->saveResourceFileEntry($resourceFileEntry);

                            $entriesUpdated++;
                            $entryAlreadyUpdated = true;
                        }
                    }

                    if ($resourceFileEntry->ResourceTypeId === array_search('string', $resourceTypes)) {
                        if (!array_key_exists($resourceFileEntry->Id, $resourceFileEntryStrings)) {
                            $resourceFileEntryString = new ResourceFileEntryString();
                            $resourceFileEntryString->AppResourceId = $resource->Id;
                            $resourceFileEntryString->ResourceFileEntryId = $resourceFileEntry->Id;
                            $resourceFileEntryStrings[$resourceFileEntry->Id] = $resourceFileEntryString;
                        }

                        $decodedString = $this->resXmlParser->decodeAndroidTranslationString(lo$node->textContent);
                        $resourceFileEntryString = $resourceFileEntryStrings[$resourceFileEntry->Id];
                        if ($resourceFileEntryString->Value !== $decodedString) {
                            $resourceFileEntryString->Value = $decodedString;
                            $resourceFileEntryString->LastChange = $timestamp;
                            $this->resourceFileEntryStringTable->saveResourceFileEntryString($resourceFileEntryString);

                            if (!$entryAlreadyUpdated) {
                                $entriesUpdated++;
                            }
                        }
                    }

                    $entriesProcessed++;
                }

                if ($resource->Name === 'values') {
                    $entriesSkippedExistOnlyInDb += count($resourceFileEntryKeys[$resourceFile->Name]);

                    if ($confirmDeletion) {
                        foreach ($resourceFileEntryKeys[$resourceFile->Name] as $key => $resourceFileEntry) {
                            $resourceFileEntry->Deleted = true;
                            $this->resourceFileEntryTable->saveResourceFileEntry($resourceFileEntry);

                            if (array_key_exists($key, $resourceFileEntries[$resourceFile->Name])) {
                                unset($resourceFileEntries[$resourceFile->Name][$key]);
                            }
                        }
                    }
                }
            }
        }

        $type = 'success';
        $message = $this->translator->translate('Import successful') . '<br>' . sprintf($this->translator->translate('%d entries processed, %d updated'), $entriesProcessed, $entriesUpdated);

        if ($entriesSkippedExistOnlyInDb > 0) {
            if ($confirmDeletion) {
                $message .= '<br>' . sprintf($this->translator->translate('%d entries deleted from database'), $entriesSkippedExistOnlyInDb);
            } else {
                $message .= '<br>' . sprintf($this->translator->translate('%d entries skipped (exist only in database)'), $entriesSkippedExistOnlyInDb);
            }
        }
        if ($entriesSkippedNotInDefault > 0) {
            $message .= '<br>' . sprintf($this->translator->translate('%d entries skipped (not in default)'), $entriesSkippedNotInDefault);
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

        $formImport = new SyncImportForm();
        $formExport = new SyncExportForm();

        return [
            'app' => $app,
            'formExport' => $formExport,
            'formImport' => $formImport,
        ];
    }
}
