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

namespace Translations\Model;

use Translations\Model\Helper\AppHelperInterface;
use Translations\Model\Helper\AppHelperTrait;
use Translations\Model\Helper\FileHelper;
use Zend\Dom\Document;
use Zend\Dom\Document\Query;
use Zend\Json\Json;
use Zend\Log\Logger;

/**
 * @codeCoverageIgnore
 */
class ResXmlParserResult
{
    public $entriesProcessed;
    public $entriesUpdated;
    public $entriesSkippedExistOnlyInDb;
    public $entriesSkippedNotInDefault;

    public function __construct()
    {
        $this->entriesProcessed = 0;
        $this->entriesUpdated = 0;
        $this->entriesSkippedExistOnlyInDb = 0;
        $this->entriesSkippedNotInDefault = 0;
    }
}

class ResXmlParser implements AppHelperInterface
{
    use AppHelperTrait;

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
     * @var EntryCommonTable
     */
    private $entryCommonTable;

    /**
     * @var EntryStringTable
     */
    private $entryStringTable;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $resourceTypes;

    /**
     * @var boolean|string
     */
    private $nodeSelector;

    /**
     * Decodes the translation into readable form
     *
     * @param string $translationString
     * @throws \RuntimeException
     * @return string
     */
    private function decodeAndroidTranslationString(string $translationString)
    {
        if (($translationString == '') || $translationString == '""') {
            return '';
        }

        // Fixing strings stored in multiline format. Why, is it relevant to copypaste Android strings like "font_size_preview_text_body" this way?
        // 1) Be paranoid about strings form files with Windows newlines
        $translationString = str_replace("\r\n", "\n", $translationString);
        // 2) Be paranoid about strings form files with Mac newlines
        $translationString = str_replace("\r", "\n", $translationString);
        // 3) Remove newlines and empty spaces before actual text in lines
        $splitString = explode("\n", $translationString);

        $handleSpecialMultiline = ($splitString !== false) && (count($splitString) > 1);
        if ($handleSpecialMultiline) {
            $translationString = '';
            foreach ($splitString as $line) {
                $line = trim($line);

                if (empty($line)) {
                    continue;
                }

                $translationString .= (empty($line)) ? '' : ' ' . $line;
            }
        }

        $jsonTranslationString = str_replace('\\\'', '\'', $translationString);
        if (mb_substr($jsonTranslationString, 0, 1) !== '"') {
            $jsonTranslationString = '"' . $jsonTranslationString;
        }

        if (mb_strlen(mb_substr($jsonTranslationString, -1, 1) !== '"') || (mb_substr($jsonTranslationString, -1, 2) == '\"')) {
            $jsonTranslationString .= '"';
        }

        try {
            $decoded = Json::decode($jsonTranslationString);

            if ($handleSpecialMultiline) {
                $splitString = explode("\n", $decoded);
                $decodedArray = [];
                foreach ($splitString as $line) {
                    $decodedArray[] = trim($line);
                }
                $decoded = implode("\n", $decodedArray);
            }

            return $decoded;
        } catch (\RuntimeException $e) {
            throw new \RuntimeException('Android string couldn\'t be decoded.');
        }
    }

    /**
     * Handles parsing and export of XML
     *
     * @param string $oldXmlString
     * @param bool $deleteNotInDb
     * @param \ArrayObject $entries
     * @param \ArrayObject $entryCommons
     * @param \ArrayObject $entryStrings
     * @param ResXmlParserResult $result
     * @return string|null
     */
    private function exportXmlString(string $oldXmlString, bool $deleteNotInDb, \ArrayObject $entries, \ArrayObject $entryCommons, \ArrayObject $entryStrings, ResXmlParserResult $result)
    {
        $newDoc = $this->getEmptyResXML();

        $oldDom = new Document($oldXmlString);

        // TODO: Implement export

        return $newDoc->saveXML();
    }

    /**
     * Generates empty resource XML file
     *
     * @return \DOMDocument
     */
    private function getEmptyResXML()
    {
        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->formatOutput = true;
        $resources = $doc->createElement('resources');
        $resources->setAttribute('xmlns:xliff', 'urn:oasis:names:tc:xliff:document:1.2');
        $doc->appendChild($resources);

        return $doc;
    }

    /**
     * Generates an XML node selector with supported resource types
     *
     * @return boolean|string
     */
    private function getNodeSelector()
    {
        if (! is_bool($this->nodeSelector) || ! is_string($this->nodeSelector)) {
            $resourceTypes = $this->getResourceTypes();

            if (count($resourceTypes) == 0) {
                $this->nodeSelector = false;
            } else {
                $querySelectors = [];
                foreach ($resourceTypes as $resourceType) {
                    $querySelectors[] = '/resources/' . $resourceType;
                };

                $this->nodeSelector = implode('|', $querySelectors);
            }
        }

        return $this->nodeSelector;
    }

    /**
     * Generates array with supported resource types
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
     * Handles parsing and import of XML
     *
     * @param string $xmlString
     * @param bool $deleteDbOnly
     * @param AppResource $resource
     * @param AppResourceFile $resourceFile
     * @param \ArrayObject $entries
     * @param \ArrayObject $entryCommons
     * @param \ArrayObject $entryStrings
     * @param ResXmlParserResult $result
     */
    private function importXmlString(string $xmlString, bool $deleteDbOnly, AppResource $resource, AppResourceFile $resourceFile, \ArrayObject $entries, \ArrayObject $entryCommons, \ArrayObject $entryStrings, ResXmlParserResult $result)
    {
        $querySelector = $this->getNodeSelector();
        if ($querySelector === false) {
            return;
        }

        if ($resource->Name === 'values') {
            $entriesDbOnly = $entries->getArrayCopy();
        }

        $dom = new Document($xmlString);
        $query = new Query();
        $nodes = $query->execute($querySelector, $dom);
        $resourceTypes = $this->getResourceTypes();

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
            if (! is_null($attribute) && ! empty($attribute->value)) {
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

            if (! array_key_exists($combinedKey, $entries)) {
                if ($resource->Name === 'values') {
                    $resourceFileEntry = new ResourceFileEntry();
                    $resourceFileEntry->AppResourceFileId = $resourceFile->Id;
                    $resourceFileEntry->ResourceTypeId = array_search($node->tagName, $resourceTypes);
                    $resourceFileEntry->Name = $name;
                    $resourceFileEntry->Product = $product;
                    $resourceFileEntry->Description = $description;
                    $resourceFileEntry->Translatable = $translatable;
                    $entries[$combinedKey] = $this->resourceFileEntryTable->saveResourceFileEntry($resourceFileEntry);
                } else {
                    $result->entriesSkippedNotInDefault++;
                    continue;
                }
            }

            if (($resource->Name === 'values') && array_key_exists($combinedKey, $entriesDbOnly)) {
                unset($entriesDbOnly[$combinedKey]);
            }

            /**
             * @var ResourceFileEntry $resourceFileEntry
             */
            $resourceFileEntry = $entries[$combinedKey];
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
                    $entries[$combinedKey] = $this->resourceFileEntryTable->saveResourceFileEntry($resourceFileEntry);
                } elseif ($resourceFileEntry->Description != $description || $resourceFileEntry->Translatable !== $translatable) {
                    $resourceFileEntry->Description = $description;
                    $resourceFileEntry->Translatable = $translatable;
                    $this->resourceFileEntryTable->saveResourceFileEntry($resourceFileEntry);

                    $result->entriesUpdated++;
                    $entryAlreadyUpdated = true;
                }
            }

            if ($resourceFileEntry->ResourceTypeId === array_search('string', $resourceTypes)) {
                if (! array_key_exists($resourceFileEntry->Id, $entryCommons)) {
                    $entryCommon = new EntryCommon();
                    $entryCommon->AppResourceId = $resource->Id;
                    $entryCommon->ResourceFileEntryId = $resourceFileEntry->Id;
                    $entryCommon->LastChange = 0;

                    $entryCommon = $this->entryCommonTable->saveEntryCommon($entryCommon);

                    $entryCommons[$resourceFileEntry->Id] = $entryCommon;
                }

                $entryCommon = $entryCommons[$resourceFileEntry->Id];

                if (! array_key_exists($entryCommon->Id, $entryStrings)) {
                    $entryString = new EntryString();
                    $entryString->EntryCommonId = $entryCommon->Id;

                    $entryStrings[$entryCommon->Id] = $entryString;
                }

                $entryString = $entryStrings[$entryCommon->Id];

                try {
                    $decodedString = $this->decodeAndroidTranslationString($node->textContent);
                } catch (\RuntimeException $e) {
                    $decodedString = $node->textContent;
                    $message = sprintf('Android string: %s
String name: %s
String product: %s
Exception message: %s
Exception trace:
%s', $node->textContent, $name, $product, $e->getMessage(), $e->getTraceAsString());
                    $this->logger->err('An error during decoding of Android string', ['messageExtended' => $message]);
                }

                if ($entryString->Value !== $decodedString) {
                    $entryString->Value = $decodedString;
                    $entryCommon->LastChange = time();

                    $this->entryCommonTable->saveEntryCommon($entryCommon);
                    $this->entryStringTable->saveEntryString($entryString);

                    if (! $entryAlreadyUpdated) {
                        $result->entriesUpdated++;
                    }
                }
            }

            $result->entriesProcessed++;
        }

        if ($resource->Name === 'values') {
            $result->entriesSkippedExistOnlyInDb += count($entriesDbOnly);

            if ($deleteDbOnly) {
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

    /**
     * Constructor
     *
     * @param AppResourceTable $appResourceTable
     * @param AppResourceFileTable $appResourceFileTable
     * @param ResourceTypeTable $resourceTypeTable
     * @param ResourceFileEntryTable $resourceFileEntryTable
     * @param EntryCommonTable  $entryCommonTable
     * @param EntryStringTable $entryStringTable
     * @param Logger $logger
     * @codeCoverageIgnore
     */
    public function __construct(AppResourceTable $appResourceTable, AppResourceFileTable $appResourceFileTable, ResourceTypeTable $resourceTypeTable, ResourceFileEntryTable $resourceFileEntryTable, EntryCommonTable  $entryCommonTable, EntryStringTable $entryStringTable, Logger $logger)
    {
        $this->appResourceTable = $appResourceTable;
        $this->appResourceFileTable = $appResourceFileTable;
        $this->resourceTypeTable = $resourceTypeTable;
        $this->resourceFileEntryTable = $resourceFileEntryTable;
        $this->entryCommonTable = $entryCommonTable;
        $this->entryStringTable = $entryStringTable;
        $this->logger = $logger;
    }

    /**
     * Export resources to XML files
     *
     * @param App $app
     * @param bool $deleteNotInDb
     * @return \Translations\Model\ResXmlParserResult
     * @codeCoverageIgnore
     */
    public function exportResourcesOfApp(App $app, bool $deleteNotInDb) {
        $result = new ResXmlParserResult();

        if ($this->getNodeSelector() === false) {
            return $result;
        }

        $path = $this->getAbsoluteAppResPath($app);

        $resources = $this->appResourceTable->fetchAll(['app_id' => $app->Id]);
        $resources->buffer();
        $resourceFiles = $this->appResourceFileTable->fetchAll(['app_id' => $app->Id]);
        $resourceFiles->buffer();

        $entries = new \ArrayObject();

        foreach ($resources as $resource) {
            $pathRes = FileHelper::concatenatePath($path, $resource->Name);

            $entryIds = [];
            $entryCommons = new \ArrayObject();
            foreach ($this->entryCommonTable->fetchAll(['app_resource_id' => $resource->Id]) as $entryCommon) {
                $entryIds[] = $entryCommon->Id;
                $entryCommons[$entryCommon->ResourceFileEntryId] = $entryCommon;
            }

            // If empty, make sure there is a valid SQL that returns no results.
            if (count($entryIds) == 0) {
                $entryIds = 0;
            }

            $entryStrings = new \ArrayObject();
            foreach ($this->entryStringTable->fetchAll(['entry_common_id' => $entryIds]) as $entryString) {
                $entryStrings[$entryString->EntryCommonId] = $entryString;
            }

            foreach ($resourceFiles as $resourceFile) {
                $pathResFile = FileHelper::concatenatePath($pathRes, $resourceFile->Name);

                if (!FileHelper::isFileWritable($pathResFile)) {
                    continue;
                }

                if (!array_key_exists($resourceFile->Name, $entries)) {
                    $entries[$resourceFile->Name] = new \ArrayObject();
                    foreach ($this->resourceFileEntryTable->fetchAll(['app_resource_file_id' => $resourceFile->Id, 'deleted' => 0]) as $entry) {
                        $combinedKey = $entry->Name . "\n" . $entry->Product;
                        $entries[$resourceFile->Name][$combinedKey] = $entry;
                    }
                }

                $xmlString = $this->exportXmlString(file_get_contents($pathResFile), $deleteNotInDb, $resourceFile, $entries[$resourceFile->Name], $entryCommons, $entryStrings, $result);
            }
        }

        return $result;
    }

    /**
     * Import resources from XML files
     *
     * @param App $app
     * @param bool $deleteDbOnly
     * @return \Translations\Model\ResXmlParserResult
     * @codeCoverageIgnore
     */
    public function importResourcesOfApp(App $app, bool $deleteDbOnly) {
        $result = new ResXmlParserResult();

        if ($this->getNodeSelector() === false) {
            return $result;
        }

        $path = $this->getAbsoluteAppResPath($app);

        $resources = $this->appResourceTable->fetchAll(['app_id' => $app->Id]);
        $resources->buffer();
        $resourceFiles = $this->appResourceFileTable->fetchAll(['app_id' => $app->Id]);
        $resourceFiles->buffer();

        $entries = new \ArrayObject();

        foreach ($resources as $resource) {
            $pathRes = FileHelper::concatenatePath($path, $resource->Name);

            $entryIds = [];
            $entryCommons = new \ArrayObject();
            foreach ($this->entryCommonTable->fetchAll(['app_resource_id' => $resource->Id]) as $entryCommon) {
                $entryIds[] = $entryCommon->Id;
                $entryCommons[$entryCommon->ResourceFileEntryId] = $entryCommon;
            }

            // If empty, make sure there is a valid SQL that returns no results.
            if (count($entryIds) == 0) {
                $entryIds = 0;
            }

            $entryStrings = new \ArrayObject();
            foreach ($this->entryStringTable->fetchAll(['entry_common_id' => $entryIds]) as $entryString) {
                $entryStrings[$entryString->EntryCommonId] = $entryString;
            }

            foreach ($resourceFiles as $resourceFile) {
                $pathResFile = FileHelper::concatenatePath($pathRes, $resourceFile->Name);

                if (!FileHelper::isFileValidResource($pathResFile)) {
                    continue;
                }

                if (!array_key_exists($resourceFile->Name, $entries)) {
                    $entries[$resourceFile->Name] = new \ArrayObject();
                    foreach ($this->resourceFileEntryTable->fetchAll(['app_resource_file_id' => $resourceFile->Id, 'deleted' => 0]) as $entry) {
                        $combinedKey = $entry->Name . "\n" . $entry->Product;
                        $entries[$resourceFile->Name][$combinedKey] = $entry;
                    }
                }

                $this->importXmlString(file_get_contents($pathResFile), $deleteDbOnly, $resource, $resourceFile, $entries[$resourceFile->Name], $entryCommons, $entryStrings, $result);
            }
        }

        return $result;
    }
}
