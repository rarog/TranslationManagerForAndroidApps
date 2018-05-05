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

namespace ApplicationTest\View\Helper;

use Application\View\Helper\DataTablesInitHelper;
use PHPUnit\Framework\TestCase;
use Zend\I18n\Translator\Translator;
use Zend\I18n\View\Helper\Translate;
use Zend\View\Renderer\PhpRenderer;
use ReflectionClass;
use stdClass;

class DataTablesInitHelperTest extends TestCase
{
    private $locale = 'en';

    private $translator;

    private $translate;

    private $renderer;

    private $helper;

    private $headersSetProperty;

    protected function setUp()
    {
        $this->translator = new Translator();
        $this->translator->setFallbackLocale($this->locale);

        $this->translate = new Translate();
        $this->translate->setTranslator($this->translator);

        $this->renderer = new PhpRenderer();
        $this->renderer->getHelperPluginManager()->get('basePath')->setBasePath('/some/path/');
        $this->renderer->getHelperPluginManager()->setService('translate', $this->translate);

        $this->helper = new DataTablesInitHelper();
        $this->helper->setView($this->renderer);

        $reflection = new ReflectionClass(DataTablesInitHelper::class);
        $this->headersSetProperty = $reflection->getProperty('headersSet');
        $this->headersSetProperty->setAccessible(true);
    }

    protected function tearDown()
    {
        unset($this->headersSetProperty);
        unset($this->helper);
        unset($this->renderer);
        unset($this->translate);
        unset($this->translator);
    }

    public function testInvoke()
    {
        $expectedHeadScript1 = new stdClass();
        $expectedHeadScript1->type = 'text/javascript';
        $expectedHeadScript1->attributes = [
            'src' => '/some/path/js/jquery.dataTables.min.js',
        ];
        $expectedHeadScript1->source = null;

        $expectedHeadScript2 = new stdClass();
        $expectedHeadScript2->type = 'text/javascript';
        $expectedHeadScript2->attributes = [
            'src' => '/some/path/js/dataTables.bootstrap.min.js',
        ];
        $expectedHeadScript2->source = null;

        $expectedHeadLink = new stdClass();
        $expectedHeadLink->rel = 'stylesheet';
        $expectedHeadLink->type = 'text/css';
        $expectedHeadLink->href = '/some/path/css/dataTables.bootstrap.min.css';
        $expectedHeadLink->media = 'screen';
        $expectedHeadLink->conditionalStylesheet = null;

        $tableName1 = '#someTable';

        $expectedInlineScriptSource1 = sprintf(
            '$("%s").dataTable({"language":{"url":"\/some\/path\/js\/dataTables.%s.json"},"stateSave":true});',
            $tableName1,
            $this->locale
        );

        $expectedInlineScript1 = new stdClass();
        $expectedInlineScript1->type = 'text/javascript';
        $expectedInlineScript1->attributes = [];
        $expectedInlineScript1->source = $expectedInlineScriptSource1;

        $parameterArray1 = [
            'table' => $tableName1,
        ];

        $tableName2 = '#anotherTable';

        $expectedInlineScriptSource2 = sprintf(
            'function %sFunction() {$("%s").dataTable({"language":{"url":"\/some\/path\/js\/dataTables.%s.json"}' .
                ',"stateSave":false,"newAttribute":"42"});}',
            $tableName2,
            $tableName2,
            $this->locale
        );

        $expectedInlineScript2 = new stdClass();
        $expectedInlineScript2->type = 'text/javascript';
        $expectedInlineScript2->attributes = [];
        $expectedInlineScript2->source = $expectedInlineScriptSource2;

        $parameterArray2 = [
            'table' => $tableName2,
            'initOptions' => [
                'stateSave' => false,
                'newAttribute' => '42',
            ],
            'functionName' => $tableName2 . 'Function',
        ];

        $expectedInlineScriptSource3 = $expectedInlineScriptSource1 . $expectedInlineScriptSource2;

        $expectedInlineScript3 = new stdClass();
        $expectedInlineScript3->type = 'text/javascript';
        $expectedInlineScript3->attributes = [];
        $expectedInlineScript3->source = $expectedInlineScriptSource3;

        $parameterArray3 = [
            $parameterArray1,
            $parameterArray2,
        ];

        $pluginManager = $this->renderer->getHelperPluginManager();
        $helper = $this->helper;

        $this->assertEquals(0, $pluginManager->get('headScript')->getContainer()->count());
        $this->assertEquals(0, $pluginManager->get('headLink')->getContainer()->count());
        $this->assertEquals(0, $pluginManager->get('inlineScript')->getContainer()->count());
        $this->assertEquals(false, $this->headersSetProperty->getValue($helper));

        // Calling with null / no array.
        $helper();

        $this->assertEquals(0, $pluginManager->get('headScript')->getContainer()->count());
        $this->assertEquals(0, $pluginManager->get('headLink')->getContainer()->count());
        $this->assertEquals(0, $pluginManager->get('inlineScript')->getContainer()->count());
        $this->assertEquals(false, $this->headersSetProperty->getValue($helper));

        // Calling with empty array.
        $helper([]);

        $this->assertEquals(0, $pluginManager->get('headScript')->getContainer()->count());
        $this->assertEquals(0, $pluginManager->get('headLink')->getContainer()->count());
        $this->assertEquals(0, $pluginManager->get('inlineScript')->getContainer()->count());
        $this->assertEquals(false, $this->headersSetProperty->getValue($helper));

        // Calling with invalid table property.
        $helper([
            'table' => 0,
        ]);

        $this->assertEquals(0, $pluginManager->get('headScript')->getContainer()->count());
        $this->assertEquals(0, $pluginManager->get('headLink')->getContainer()->count());
        $this->assertEquals(0, $pluginManager->get('inlineScript')->getContainer()->count());
        $this->assertEquals(false, $this->headersSetProperty->getValue($helper));

        // Calling with empty table property.
        $helper([
            'table' => '',
        ]);

        $this->assertEquals(0, $pluginManager->get('headScript')->getContainer()->count());
        $this->assertEquals(0, $pluginManager->get('headLink')->getContainer()->count());
        $this->assertEquals(0, $pluginManager->get('inlineScript')->getContainer()->count());
        $this->assertEquals(false, $this->headersSetProperty->getValue($helper));

        // 1st call with valid table name.
        $helper($parameterArray1);

        $this->assertEquals(2, $pluginManager->get('headScript')->getContainer()->count());
        $this->assertEquals($expectedHeadScript1, $pluginManager->get('headScript')->getContainer()->offsetGet(0));
        $this->assertEquals($expectedHeadScript2, $pluginManager->get('headScript')->getContainer()->offsetGet(1));
        $this->assertEquals(1, $pluginManager->get('headLink')->getContainer()->count());
        $this->assertEquals($expectedHeadLink, $pluginManager->get('headLink')->getContainer()->offsetGet(0));
        $this->assertEquals(1, $pluginManager->get('inlineScript')->getContainer()->count());
        $this->assertEquals($expectedInlineScript1, $pluginManager->get('inlineScript')->getContainer()->offsetGet(0));
        $this->assertEquals(true, $this->headersSetProperty->getValue($helper));

        // 2nd call with valid table name, merged attributes and a function name.
        $helper($parameterArray2);

        $this->assertEquals(2, $pluginManager->get('headScript')->getContainer()->count());
        $this->assertEquals($expectedHeadScript1, $pluginManager->get('headScript')->getContainer()->offsetGet(0));
        $this->assertEquals($expectedHeadScript2, $pluginManager->get('headScript')->getContainer()->offsetGet(1));
        $this->assertEquals(1, $pluginManager->get('headLink')->getContainer()->count());
        $this->assertEquals($expectedHeadLink, $pluginManager->get('headLink')->getContainer()->offsetGet(0));
        $this->assertEquals(2, $pluginManager->get('inlineScript')->getContainer()->count());
        $this->assertEquals($expectedInlineScript1, $pluginManager->get('inlineScript')->getContainer()->offsetGet(0));
        $this->assertEquals($expectedInlineScript2, $pluginManager->get('inlineScript')->getContainer()->offsetGet(1));
        $this->assertEquals(true, $this->headersSetProperty->getValue($helper));

        // 3rd call with combined call of 2 tables.
        $helper($parameterArray3);

        $this->assertEquals(2, $pluginManager->get('headScript')->getContainer()->count());
        $this->assertEquals($expectedHeadScript1, $pluginManager->get('headScript')->getContainer()->offsetGet(0));
        $this->assertEquals($expectedHeadScript2, $pluginManager->get('headScript')->getContainer()->offsetGet(1));
        $this->assertEquals(1, $pluginManager->get('headLink')->getContainer()->count());
        $this->assertEquals($expectedHeadLink, $pluginManager->get('headLink')->getContainer()->offsetGet(0));
        $this->assertEquals(3, $pluginManager->get('inlineScript')->getContainer()->count());
        $this->assertEquals($expectedInlineScript1, $pluginManager->get('inlineScript')->getContainer()->offsetGet(0));
        $this->assertEquals($expectedInlineScript2, $pluginManager->get('inlineScript')->getContainer()->offsetGet(1));
        $this->assertEquals($expectedInlineScript3, $pluginManager->get('inlineScript')->getContainer()->offsetGet(2));
        $this->assertEquals(true, $this->headersSetProperty->getValue($helper));
    }
}
