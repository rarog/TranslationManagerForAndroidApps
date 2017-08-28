<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace TranslationsTest\Model;

use PHPUnit\Framework\TestCase;
use Translations\Model\ResXmlParser;

class ResXmlParserTest extends TestCase
{
    // security_settings_fingerprint_preference_summary_none - https://github.com/android/platform_packages_apps_settings/blob/nougat-release/res/values/strings.xml
    private $emptyStringWithoutQuotes = '';
    private $emptyStringWithoutQuotesDecoded = '';

    // ringtone_summary - https://github.com/android/platform_packages_apps_settings/blob/nougat-release/res/values/strings.xml
    private $emptyStringWithQuotes = '""';
    private $emptyStringWithQuotesDecoded = '';

    // create - https://github.com/android/platform_packages_apps_settings/blob/nougat-release/res/values/strings.xml
    private $stringWithoutQuotes = 'Create';
    private $stringWithoutQuotesDecoded = 'Create';

    // yes - https://github.com/android/platform_packages_apps_settings/blob/nougat-release/res/values/strings.xml
    private $stringWithQuotes= '"Yes"';
    private $stringWithQuotesDecoded= 'Yes';

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

    private $resXmlParser;

    private function getResXmlParser()
    {
        if (is_null($this->resXmlParser)) {
            $this->resXmlParser = new ResXmlParser(
                $this->createMock(\Translations\Model\AppResourceTable::class),
                $this->createMock(\Translations\Model\AppResourceFileTable::class),
                $this->createMock(\Translations\Model\ResourceTypeTable::class),
                $this->createMock(\Translations\Model\ResourceFileEntryTable::class),
                $this->createMock(\Translations\Model\ResourceFileEntryStringTable::class),
                $this->createMock(\Zend\Log\Logger::class)
            );
        }
        return $this->resXmlParser;
    }

    public function testEmptyStringWithoutQuotes()
    {
        $result = $this->getResXmlParser()->decodeAndroidTranslationString($this->emptyStringWithoutQuotes);
        $this->assertEquals($this->emptyStringWithoutQuotesDecoded, $result);
    }

    public function testEmptyStringWithQuotes()
    {
        $result = $this->getResXmlParser()->decodeAndroidTranslationString($this->emptyStringWithQuotes);
        $this->assertEquals($this->emptyStringWithQuotesDecoded, $result);
    }

    public function testStringWithoutQuotes()
    {
        $result = $this->getResXmlParser()->decodeAndroidTranslationString($this->stringWithoutQuotes);
        $this->assertEquals($this->stringWithoutQuotesDecoded, $result);
    }

    public function testStringWithQuotes()
    {
        $result = $this->getResXmlParser()->decodeAndroidTranslationString($this->stringWithQuotes);
        $this->assertEquals($this->stringWithQuotesDecoded, $result);
    }

    public function testBrokenStringNotBeginnungButEndingWithQuote()
    {
        $result = $this->getResXmlParser()->decodeAndroidTranslationString($this->brokenStringNotBeginnungButEndingWithQuote);
        $this->assertEquals($this->brokenStringNotBeginnungButEndingWithQuoteDecoded, $result);
    }

    public function testMultilineStringWithRealNewlines()
    {
        $result = $this->getResXmlParser()->decodeAndroidTranslationString($this->multilineStringWithRealNewlines);
        $this->assertEquals($this->multilineStringWithRealNewlinesDecoded, $result);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testBadUndecodableStringLeadingToException()
    {
        $this->getResXmlParser()->decodeAndroidTranslationString($this->badUndecodableStringLeadingToException);
    }
}
