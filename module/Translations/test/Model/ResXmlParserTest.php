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

namespace TranslationsTest\Model;

use PHPUnit\Framework\TestCase;
use Translations\Model\AppResource;
use Translations\Model\EntryCommon;
use Translations\Model\EntryString;
use Translations\Model\ResXmlParser;
use Translations\Model\ResXmlParserExportResult;
use Translations\Model\ResourceFileEntry;

class ResXmlParserTest extends TestCase
{
    // security_settings_fingerprint_preference_summary_none - https://github.com/android/platform_packages_apps_settings/blob/nougat-release/res/values/strings.xml
    private $emptyStringWithoutQuotes = '';
    private $emptyStringWithoutQuotesDecoded = '';
    private $emptyStringWithoutQuotesEncoded = '';

    // ringtone_summary - https://github.com/android/platform_packages_apps_settings/blob/nougat-release/res/values/strings.xml
    private $emptyStringWithQuotes = '""';
    private $emptyStringWithQuotesDecoded = '';

    // create - https://github.com/android/platform_packages_apps_settings/blob/nougat-release/res/values/strings.xml
    private $stringWithoutQuotes = 'Create';
    private $stringWithoutQuotesDecoded = 'Create';
    private $stringWithoutQuotesEncoded = 'Create';

    // yes - https://github.com/android/platform_packages_apps_settings/blob/nougat-release/res/values/strings.xml
    private $stringWithQuotes = '"Yes"';
    private $stringWithQuotesDecoded = 'Yes';

    // reset_network_desc - https://github.com/android/platform_packages_apps_settings/blob/nougat-release/res/values/strings.xml
    private $brokenStringNotBeginnungButEndingWithQuote = 'This will reset all network settings, including:\n\n<li>Wi\u2011Fi</li>\n<li>Cellular data</li>\n<li>Bluetooth</li>"';
    private $brokenStringNotBeginnungButEndingWithQuoteDecoded = 'This will reset all network settings, including:' . "\n" .
        "\n" .
        '<li>Wi‑Fi</li>' . "\n" .
        '<li>Cellular data</li>' . "\n" .
        '<li>Bluetooth</li>';

    // font_size_preview_text_body - https://github.com/android/platform_packages_apps_settings/blob/nougat-release/res/values/strings.xml
    private $multilineStringWithRealNewlines = "\n" .
        '    Even with eyes protected by the green spectacles Dorothy and her friends were at first dazzled by the brilliancy of the wonderful City.' . "\n" .
        '    The streets were lined with beautiful houses all built of green marble and studded everywhere with sparkling emeralds.' . "\n" .
        '    They walked over a pavement of the same green marble, and where the blocks were joined together were rows of emeralds, set closely, and glittering in the brighness of the sun.' . "\n" .
        '    The window panes were of green glass; even the sky above the City had a green tint, and the rays of the sun were green.' . "\n" .
        '    \n\nThere were many people, men, women and children, walking about, and these were all dressed in green clothes and had greenish skins.' . "\n" .
        '    They looked at Dorothy and her strangely assorted company with wondering eyes, and the children all ran away and hid behind their mothers when they saw the Lion; but no one spoke to them.' . "\n" .
        '    Many shops stood in the street, and Dorothy saw that everything in them was green.' . "\n" .
        '    Green candy and green pop-corn were offered for sale, as well as green shoes, green hats and green clothes of all sorts.' . "\n" .
        '    At one place a man was selling green lemonade, and when the children bought it Dorothy could see that they paid for it with green pennies.' . "\n" .
        '    \n\nThere seemed to be no horses nor animals of any kind; the men carried things around in little green carts, which they pushed before them.' . "\n" .
        '    Everyone seeemed happy and contented and prosperous.' . "\n" .
        '    ';
    private $multilineStringWithRealNewlinesDecoded = 'Even with eyes protected by the green spectacles Dorothy and her friends were at first dazzled by the brilliancy of the wonderful City. The streets were lined with beautiful houses all built of green marble and studded everywhere with sparkling emeralds. They walked over a pavement of the same green marble, and where the blocks were joined together were rows of emeralds, set closely, and glittering in the brighness of the sun. The window panes were of green glass; even the sky above the City had a green tint, and the rays of the sun were green.' . "\n" .
        "\n" .
        'There were many people, men, women and children, walking about, and these were all dressed in green clothes and had greenish skins. They looked at Dorothy and her strangely assorted company with wondering eyes, and the children all ran away and hid behind their mothers when they saw the Lion; but no one spoke to them. Many shops stood in the street, and Dorothy saw that everything in them was green. Green candy and green pop-corn were offered for sale, as well as green shoes, green hats and green clothes of all sorts. At one place a man was selling green lemonade, and when the children bought it Dorothy could see that they paid for it with green pennies.' . "\n" .
        "\n" .
        'There seemed to be no horses nor animals of any kind; the men carried things around in little green carts, which they pushed before them. Everyone seeemed happy and contented and prosperous.';

    // master_clear_accounts - https://github.com/android/platform_packages_apps_settings/blob/nougat-release/res/values-de/strings.xml
    private $badUndecodableStringLeadingToException = '\n\n"Du bist zurzeit in folgenden Konten angemeldet:\n"';

    // Taken from https://developer.android.com/guide/topics/resources/string-resource.html - Escaping apostrophes and quotes
    private $androidExampleStringWithApostrophesDecoded = 'This\'ll work';
    private $androidExampleStringWithApostrophesEncoded = 'This\\\'ll work';
    private $androidExampleStringWithQuotesDecoded = 'This is a "good string".';
    private $androidExampleStringWithQuotesEncoded = 'This is a \"good string\".';

    private $emptyResXML = '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
        '<resources xmlns:xliff="urn:oasis:names:tc:xliff:document:1.2"/>' . "\n";

    /**
     * @var ResXmlParser
     */
    private $resXmlParser;

    /**
     * Generates an AppResource object.
     *
     * @param bool $default
     * @return \Translations\Model\AppResource
     */
    private function getAppResource(bool $default)
    {
        return new AppResource([
            'name' => ($default) ? 'values' : 'values-de',
        ]);
    }

    /**
     * Initialises a ResXmlParser object for tests.
     *
     * @return ResXmlParser
     */
    private function getResXmlParser()
    {
        if (is_null($this->resXmlParser)) {
            $this->resXmlParser = new ResXmlParser(
                $this->createMock(\Translations\Model\AppResourceTable::class),
                $this->createMock(\Translations\Model\AppResourceFileTable::class),
                $this->createMock(\Translations\Model\ResourceTypeTable::class),
                $this->createMock(\Translations\Model\ResourceFileEntryTable::class),
                $this->createMock(\Translations\Model\EntryCommonTable::class),
                $this->createMock(\Translations\Model\EntryStringTable::class),
                $this->createMock(\Zend\Log\Logger::class)
                );

            $reflection = new \ReflectionClass($this->resXmlParser);
            $resourceTypes = $reflection->getProperty('resourceTypes');
            $resourceTypes->setAccessible(true);
            $resourceTypes->setValue($this->resXmlParser, [
                1 => 'string',
            ]);
            $resourceTypes->setAccessible(false);
        }
        return $this->resXmlParser;
    }

    /**
     * Returns ResourceFileEntry with invalid id.
     *
     * @return \Translations\Model\ResourceFileEntry
     */
    private function getInvalidResourceFileEntry()
    {
        return new ResourceFileEntry([
            'resource_type_id' => -1,
            'translatable' => 1,
        ]);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object $object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     * @return mixed Method return.
     */
    private function invokeMethod($object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::decodeAndroidTranslationString
     */
    public function testDecodeEmptyStringWithoutQuotes()
    {
        $resXmlParser = $this->getResXmlParser();
        $result = $this->invokeMethod($resXmlParser, 'decodeAndroidTranslationString', [$this->emptyStringWithoutQuotes]);
        $this->assertEquals($this->emptyStringWithoutQuotesDecoded, $result);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::decodeAndroidTranslationString
     */
    public function testDecodeEmptyStringWithQuotes()
    {
        $resXmlParser = $this->getResXmlParser();
        $result = $this->invokeMethod($resXmlParser, 'decodeAndroidTranslationString', [$this->emptyStringWithQuotes]);
        $this->assertEquals($this->emptyStringWithQuotesDecoded, $result);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::decodeAndroidTranslationString
     */
    public function testDecodeStringWithoutQuotes()
    {
        $resXmlParser = $this->getResXmlParser();
        $result = $this->invokeMethod($resXmlParser, 'decodeAndroidTranslationString', [$this->stringWithoutQuotes]);
        $this->assertEquals($this->stringWithoutQuotesDecoded, $result);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::decodeAndroidTranslationString
     */
    public function testDecodeStringWithQuotes()
    {
        $resXmlParser = $this->getResXmlParser();
        $result = $this->invokeMethod($resXmlParser, 'decodeAndroidTranslationString', [$this->stringWithQuotes]);
        $this->assertEquals($this->stringWithQuotesDecoded, $result);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::decodeAndroidTranslationString
     */
    public function testDecodeBrokenStringNotBeginnungButEndingWithQuote()
    {
        $resXmlParser = $this->getResXmlParser();
        $result = $this->invokeMethod($resXmlParser, 'decodeAndroidTranslationString', [$this->brokenStringNotBeginnungButEndingWithQuote]);
        $this->assertEquals($this->brokenStringNotBeginnungButEndingWithQuoteDecoded, $result);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::decodeAndroidTranslationString
     */
    public function testDecodeMultilineStringWithRealNewlines()
    {
        $resXmlParser = $this->getResXmlParser();
        $result = $this->invokeMethod($resXmlParser, 'decodeAndroidTranslationString', [$this->multilineStringWithRealNewlines]);
        $this->assertEquals($this->multilineStringWithRealNewlinesDecoded, $result);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::decodeAndroidTranslationString
     * @expectedException RuntimeException
     */
    public function testBadUndecodableStringLeadingToException()
    {
        $resXmlParser = $this->getResXmlParser();
        $result = $this->invokeMethod($resXmlParser, 'decodeAndroidTranslationString', [$this->badUndecodableStringLeadingToException]);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::encodeAndroidTranslationString
     */
    public function testEncodeEmptyStringWithoutQuotes()
    {
        $resXmlParser = $this->getResXmlParser();
        $result = $this->invokeMethod($resXmlParser, 'encodeAndroidTranslationString', [$this->emptyStringWithoutQuotesDecoded]);
        $this->assertEquals($this->emptyStringWithoutQuotesEncoded, $result);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::encodeAndroidTranslationString
     */
    public function testEncodeStringWithoutQuotes()
    {
        $resXmlParser = $this->getResXmlParser();
        $result = $this->invokeMethod($resXmlParser, 'encodeAndroidTranslationString', [$this->stringWithoutQuotesDecoded]);
        $this->assertEquals($this->stringWithoutQuotesEncoded, $result);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::encodeAndroidTranslationString
     */
    public function testEncodeAndroidExampleStringWithApostrophes()
    {
        $resXmlParser = $this->getResXmlParser();
        $result = $this->invokeMethod($resXmlParser, 'encodeAndroidTranslationString', [$this->androidExampleStringWithApostrophesDecoded]);
        $this->assertEquals($this->androidExampleStringWithApostrophesEncoded, $result);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::encodeAndroidTranslationString
     */
    public function testEncodeAndroidExampleStringWithQuotes()
    {
        $resXmlParser = $this->getResXmlParser();
        $result = $this->invokeMethod($resXmlParser, 'encodeAndroidTranslationString', [$this->androidExampleStringWithQuotesDecoded]);
        $this->assertEquals($this->androidExampleStringWithQuotesEncoded, $result);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::exportXmlString
     */
    public function testExportGetEmptyResXML()
    {
        $resXmlParser = $this->getResXmlParser();
        $result = new ResXmlParserExportResult();
        $exportedXmlString = $this->invokeMethod($resXmlParser, 'exportXmlString', ['', true, new AppResource(), new \ArrayObject(), new \ArrayObject(), new \ArrayObject(), $result]);
        $this->assertEquals($this->emptyResXML, $exportedXmlString);
        $this->assertEquals($result->entriesProcessed, 0);
        $this->assertEquals($result->entriesSkippedUnknownType, 0);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::exportXmlString
     */
    public function testExportUnknownResourceTypeSkipped()
    {
        $resXmlParser = $this->getResXmlParser();
        $result = new ResXmlParserExportResult();
        $entries = new \ArrayObject([
            $this->getInvalidResourceFileEntry(),
        ]);
        $exportedXmlString = $this->invokeMethod($resXmlParser, 'exportXmlString', ['', true, new AppResource(), $entries, new \ArrayObject(), new \ArrayObject(), $result]);
        $this->assertEquals($this->emptyResXML, $exportedXmlString);
        $this->assertEquals($result->entriesProcessed, 0);
        $this->assertEquals($result->entriesSkippedUnknownType, 1);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::exportXmlString
     */
    public function testExportUnknownResourceTypeSkipped2()
    {
        $resXmlParser = clone $this->getResXmlParser();
        $reflection = new \ReflectionClass($resXmlParser);
        $resourceTypes = $reflection->getProperty('resourceTypes');
        $resourceTypes->setAccessible(true);
        $resourceTypes->setValue($resXmlParser, $resourceTypes->getValue($resXmlParser) + [-1 => 'definitelyinvalidtype']);
        $resourceTypes->setAccessible(false);
        $result = new ResXmlParserExportResult();
        $entries = new \ArrayObject([
            $this->getInvalidResourceFileEntry(),
        ]);
        $exportedXmlString = $this->invokeMethod($resXmlParser, 'exportXmlString', ['', true, new AppResource(), $entries, new \ArrayObject(), new \ArrayObject(), $result]);
        $this->assertEquals($this->emptyResXML, $exportedXmlString);
        $this->assertEquals($result->entriesProcessed, 0);
        $this->assertEquals($result->entriesSkippedUnknownType, 1);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::exportXmlString
     */
    public function testExportStringEntryWithDescription()
    {
        $resXmlParser = $this->getResXmlParser();
        $entry = new ResourceFileEntry([
            'id' => 1,
            'resource_type_id' => 1,
            'name' => 'example_string',
            'product' => 'default',
            'description' => 'Example description',
            'translatable' => 1,
        ]);
        $entries = new \ArrayObject([$entry]);
        $entryCommon = new EntryCommon([
            'id' => 1,
            'resource_file_entry_id' => '1',
        ]);
        $entriesCommon = new \ArrayObject([1 => $entryCommon]);
        $entryString = new EntryString([
            'entry_common_id' => 1,
            'value' => 'Example value',
        ]);
        $entriesString = new \ArrayObject([1 => $entryString]);
        $result = new ResXmlParserExportResult();

        $expectedXmlString = '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
            '<resources xmlns:xliff="urn:oasis:names:tc:xliff:document:1.2">' . "\n" .
            '  <string name="example_string" product="default" description="Example description">Example value</string>' . "\n" .
            '</resources>' . "\n";
        $exportedXmlString = $this->invokeMethod($resXmlParser, 'exportXmlString', ['', true, $this->getAppResource(true), $entries, $entriesCommon, $entriesString, $result]);
        $this->assertEquals($expectedXmlString, $exportedXmlString);
        $this->assertEquals($result->entriesProcessed, 1);
        $this->assertEquals($result->entriesSkippedUnknownType, 0);

        $expectedXmlString = '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
            '<resources xmlns:xliff="urn:oasis:names:tc:xliff:document:1.2">' . "\n" .
            '  <string name="example_string" product="default">Example value</string>' . "\n" .
            '</resources>' . "\n";
        $exportedXmlString = $this->invokeMethod($resXmlParser, 'exportXmlString', ['', true, $this->getAppResource(false), $entries, $entriesCommon, $entriesString, $result]);
        $this->assertEquals($expectedXmlString, $exportedXmlString);
        $this->assertEquals($result->entriesProcessed, 2);
        $this->assertEquals($result->entriesSkippedUnknownType, 0);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::exportXmlString
     */
    public function testExportStringEntryNotTranslatable()
    {
        $resXmlParser = $this->getResXmlParser();
        $entry = new ResourceFileEntry([
            'id' => 1,
            'resource_type_id' => 1,
            'name' => 'example_string',
            'product' => 'default',
            'translatable' => 0,
        ]);
        $entries = new \ArrayObject([$entry]);
        $entryCommon = new EntryCommon([
            'id' => 1,
            'resource_file_entry_id' => '1',
        ]);
        $entriesCommon = new \ArrayObject([1 => $entryCommon]);
        $entryString = new EntryString([
            'entry_common_id' => 1,
            'value' => 'Example value',
        ]);
        $entriesString = new \ArrayObject([1 => $entryString]);
        $result = new ResXmlParserExportResult();

        $expectedXmlString = '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
            '<resources xmlns:xliff="urn:oasis:names:tc:xliff:document:1.2">' . "\n" .
            '  <string name="example_string" product="default">Example value</string>' . "\n" .
            '</resources>' . "\n";
        $exportedXmlString = $this->invokeMethod($resXmlParser, 'exportXmlString', ['', true, $this->getAppResource(true), $entries, $entriesCommon, $entriesString, $result]);
        $this->assertEquals($expectedXmlString, $exportedXmlString);
        $this->assertEquals($result->entriesProcessed, 1);
        $this->assertEquals($result->entriesSkippedUnknownType, 0);
        $this->assertEquals($result->oldEntriesPreservedUnknownType, 0);

        $exportedXmlString = $this->invokeMethod($resXmlParser, 'exportXmlString', ['', true, $this->getAppResource(false), $entries, $entriesCommon, $entriesString, $result]);
        $this->assertEquals($this->emptyResXML, $exportedXmlString);
        $this->assertEquals($result->entriesProcessed, 1);
        $this->assertEquals($result->entriesSkippedUnknownType, 0);
        $this->assertEquals($result->oldEntriesPreservedUnknownType, 0);
    }

    public function testExportEntriesIgnoringWrongOldEntriesXml()
    {
        $resXmlParser = $this->getResXmlParser();
        $entry = new ResourceFileEntry([
            'id' => 1,
            'resource_type_id' => 1,
            'name' => 'example_string',
            'product' => 'default',
            'translatable' => 0,
        ]);
        $entries = new \ArrayObject([$entry]);
        $entryCommon = new EntryCommon([
            'id' => 1,
            'resource_file_entry_id' => '1',
        ]);
        $entriesCommon = new \ArrayObject([1 => $entryCommon]);
        $entryString = new EntryString([
            'entry_common_id' => 1,
            'value' => 'Example value',
        ]);
        $entriesString = new \ArrayObject([1 => $entryString]);
        $result = new ResXmlParserExportResult();

        $oldXmlString = '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
            '<resources xmlns:xliff="urn:oasis:names:tc:xliff:document:1.2">' . "\n" .
            '  <unknownEntry random="attribute">Whatever value</unknownEntry>' . "\n" .
            '</wrongEndTag>' . "\n";

        $expectedXmlString = '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
            '<resources xmlns:xliff="urn:oasis:names:tc:xliff:document:1.2">' . "\n" .
            '  <string name="example_string" product="default">Example value</string>' . "\n" .
            '</resources>' . "\n";
        $exportedXmlString = $this->invokeMethod($resXmlParser, 'exportXmlString', [$oldXmlString, false, $this->getAppResource(true), $entries, $entriesCommon, $entriesString, $result]);
        $this->assertEquals($expectedXmlString, $exportedXmlString);
        $this->assertEquals($result->entriesProcessed, 1);
        $this->assertEquals($result->entriesSkippedUnknownType, 0);
        $this->assertEquals($result->oldEntriesPreservedUnknownType, 0);
    }

    /**
     * @covers \Translations\Model\ResXmlParser::exportXmlString
     */
    public function testExportEntriesPreservingOldEntries()
    {
        $resXmlParser = $this->getResXmlParser();
        $entry = new ResourceFileEntry([
            'id' => 1,
            'resource_type_id' => 1,
            'name' => 'example_string',
            'product' => 'default',
            'translatable' => 0,
        ]);
        $entries = new \ArrayObject([$entry]);
        $entryCommon = new EntryCommon([
            'id' => 1,
            'resource_file_entry_id' => '1',
        ]);
        $entriesCommon = new \ArrayObject([1 => $entryCommon]);
        $entryString = new EntryString([
            'entry_common_id' => 1,
            'value' => 'Example value',
        ]);
        $entriesString = new \ArrayObject([1 => $entryString]);
        $result = new ResXmlParserExportResult();

        $oldXmlString = '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
            '<resources xmlns:xliff="urn:oasis:names:tc:xliff:document:1.2">' . "\n" .
            '  <unknownEntry random="attribute">Whatever value</unknownEntry>' . "\n" .
            '</resources>' . "\n";

        $expectedXmlString = '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
            '<resources xmlns:xliff="urn:oasis:names:tc:xliff:document:1.2">' . "\n" .
            '  <string name="example_string" product="default">Example value</string>' . "\n" .
            '  <unknownEntry random="attribute">Whatever value</unknownEntry>' . "\n" .
            '</resources>' . "\n";
        $exportedXmlString = $this->invokeMethod($resXmlParser, 'exportXmlString', [$oldXmlString, false, $this->getAppResource(true), $entries, $entriesCommon, $entriesString, $result]);
        $this->assertEquals($expectedXmlString, $exportedXmlString);
        $this->assertEquals($result->entriesProcessed, 1);
        $this->assertEquals($result->entriesSkippedUnknownType, 0);
        $this->assertEquals($result->oldEntriesPreservedUnknownType, 1);
    }
}
