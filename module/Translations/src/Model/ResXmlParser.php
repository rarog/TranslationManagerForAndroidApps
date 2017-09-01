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

use ArrayObject;
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
     * @var ResourceFileEntryStringTable
     */
    private $resourceFileEntryStringTable;

    /**
     * @var Logger
     */
    private $logger;

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
     * Handles parsing and import of XML
     *
     * @param string $xmlString
     * @param string $querySelector
     * @param bool $delete
     * @param AppResource $resource
     * @param AppResourceFile $resourceFile
     * @param array $resourceTypes
     * @param ArrayObject $entries
     * @param ArrayObject $entryKeys
     * @param ArrayObject $resourceFileEntryStrings
     * @param ResXmlParserResult $result
     */
    private function importXmlString(string $xmlString, string $querySelector, bool $delete, AppResource $resource, AppResourceFile $resourceFile, array $resourceTypes, ArrayObject $entries, ArrayObject $entryKeys, ArrayObject $resourceFileEntryStrings, ResXmlParserResult $result)
    {
        $dom = new Document($xmlString);
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

            if (($resource->Name === 'values') && array_key_exists($combinedKey, $entryKeys)) {
                unset($entryKeys[$combinedKey]);
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
                if (!array_key_exists($resourceFileEntry->Id, $resourceFileEntryStrings)) {
                    $resourceFileEntryString = new ResourceFileEntryString();
                    $resourceFileEntryString->AppResourceId = $resource->Id;
                    $resourceFileEntryString->ResourceFileEntryId = $resourceFileEntry->Id;
                    $resourceFileEntryStrings[$resourceFileEntry->Id] = $resourceFileEntryString;
                }

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

                $resourceFileEntryString = $resourceFileEntryStrings[$resourceFileEntry->Id];
                if ($resourceFileEntryString->Value !== $decodedString) {
                    $resourceFileEntryString->Value = $decodedString;
                    $resourceFileEntryString->LastChange = time();
                    $this->resourceFileEntryStringTable->saveResourceFileEntryString($resourceFileEntryString);

                    if (!$entryAlreadyUpdated) {
                        $result->entriesUpdated++;
                    }
                }
            }

            $result->entriesProcessed++;
        }

        if ($resource->Name === 'values') {
            $result->entriesSkippedExistOnlyInDb += count($entryKeys);

            if ($delete) {
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
     * @param ResourceFileEntryStringTable $resourceFileEntryStringTable
     * @param Logger $logger
     * @codeCoverageIgnore
     */
    public function __construct(AppResourceTable $appResourceTable, AppResourceFileTable $appResourceFileTable, ResourceTypeTable $resourceTypeTable, ResourceFileEntryTable $resourceFileEntryTable, ResourceFileEntryStringTable $resourceFileEntryStringTable, Logger $logger)
    {
        $this->appResourceTable = $appResourceTable;
        $this->appResourceFileTable = $appResourceFileTable;
        $this->resourceTypeTable = $resourceTypeTable;
        $this->resourceFileEntryTable = $resourceFileEntryTable;
        $this->resourceFileEntryStringTable = $resourceFileEntryStringTable;
        $this->logger = $logger;
    }

    /**
     * Export resources to XML files
     *
     * @param App $app
     * @param bool $confirmDeletion
     * @return \Translations\Model\ResXmlParserResult
     * @codeCoverageIgnore
     */
    public function exportResourcesOfApp(App $app, bool $confirmDeletion) {
        $result = new ResXmlParserResult();

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

        // TODO: Implement export

        return $result;
    }

    /**
     * Import resources from XML files
     *
     * @param App $app
     * @param bool $confirmDeletion
     * @return \Translations\Model\ResXmlParserResult
     * @codeCoverageIgnore
     */
    public function importResourcesOfApp(App $app, bool $confirmDeletion) {
        $result = new ResXmlParserResult();

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
            return $result;
        }

        $querySelectors = [];
        foreach ($resourceTypes as $resourceType) {
            $querySelectors[] = '/resources/' . $resourceType;
        };
        $querySelector = implode('|', $querySelectors);

        $resourceFileEntries = new ArrayObject();
        $resourceFileEntryKeys = new ArrayObject();

        foreach ($resources as $resource) {
            $pathRes = FileHelper::concatenatePath($path, $resource->Name);

            $resourceFileEntryStrings = new ArrayObject();
            foreach ($this->resourceFileEntryStringTable->fetchAll(['app_resource_id' => $resource->Id]) as $resourceFileEntryString) {
                $resourceFileEntryStrings[$resourceFileEntryString->ResourceFileEntryId] = $resourceFileEntryString;
            }

            foreach ($resourceFiles as $resourceFile) {
                $pathResFile = FileHelper::concatenatePath($pathRes, $resourceFile->Name);

                if (!FileHelper::isFileValidResource($pathResFile)) {
                    continue;
                }

                if (!array_key_exists($resourceFile->Name, $resourceFileEntries)) {
                    $resourceFileEntries[$resourceFile->Name] = new ArrayObject();
                    $resourceFileEntryKeys[$resourceFile->Name] = new ArrayObject();
                    foreach ($this->resourceFileEntryTable->fetchAll(['app_resource_file_id' => $resourceFile->Id, 'deleted' => 0]) as $entry) {
                        $combinedKey = $entry->Name . "\n" . $entry->Product;
                        $resourceFileEntries[$resourceFile->Name][$combinedKey] = $entry;
                        $resourceFileEntryKeys[$resourceFile->Name][$combinedKey] = $entry;
                    }
                }

                $this->importXmlString(file_get_contents($pathResFile), $querySelector, $confirmDeletion, $resource, $resourceFile, $resourceTypes, $resourceFileEntries[$resourceFile->Name], $resourceFileEntryKeys[$resourceFile->Name], $resourceFileEntryStrings, $result);
            }
        }

        return $result;
    }
}
