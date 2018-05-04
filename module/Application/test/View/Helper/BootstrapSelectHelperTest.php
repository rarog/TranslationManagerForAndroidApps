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

use Application\View\Helper\BootstrapSelectHelper;
use PHPUnit\Framework\TestCase;
use Zend\I18n\Translator\Translator;
use Zend\I18n\View\Helper\Translate;
use Zend\View\Renderer\PhpRenderer;
use stdClass;

class BootstrapSelectHelperTest extends TestCase
{
    private $locale = 'en';

    private $translator;

    private $translate;

    private $renderer;

    private $helper;

    protected function setUp()
    {
        $this->translator = new Translator();
        $this->translator->setLocale($this->locale);

        $this->translate = new Translate();
        $this->translate->setTranslator($this->translator);

        $this->renderer = new PhpRenderer();
        $this->renderer->getHelperPluginManager()->get('basePath')->setBasePath('/some/path/');
        $this->renderer->getHelperPluginManager()->setService('translate', $this->translate);

        $this->helper = new BootstrapSelectHelper();
        $this->helper->setView($this->renderer);
    }

    protected function tearDown()
    {
        unset($this->helper);
        unset($this->renderer);
        unset($this->translate);
        unset($this->translator);
    }

    public function testInvokeAppEqualsZero()
    {
        $expectedHeadScript1 = new stdClass();
        $expectedHeadScript1->type = 'text/javascript';
        $expectedHeadScript1->attributes = [
            'src' => '/some/path/js/bootstrap-select.min.js',
        ];
        $expectedHeadScript1->source = null;

        $expectedHeadScript2 = new stdClass();
        $expectedHeadScript2->type = 'text/javascript';
        $expectedHeadScript2->attributes = [
            'src' => sprintf('/some/path/js/i18n/defaults-%s.min.js', $this->locale),
        ];
        $expectedHeadScript2->source = null;

        $expectedHeadLink = new stdClass();
        $expectedHeadLink->rel = 'stylesheet';
        $expectedHeadLink->type = 'text/css';
        $expectedHeadLink->href = '/some/path/css/bootstrap-select.min.css';
        $expectedHeadLink->media = 'screen';
        $expectedHeadLink->conditionalStylesheet = null;

        $pluginManager = $this->renderer->getHelperPluginManager();

        $this->assertEquals(0, $pluginManager->get('headScript')->getContainer()->count());
        $this->assertEquals(0, $pluginManager->get('headLink')->getContainer()->count());

        $helper = $this->helper;
        $helper();

        $this->assertEquals(2, $pluginManager->get('headScript')->getContainer()->count());
        $this->assertEquals($expectedHeadScript1, $pluginManager->get('headScript')->getContainer()->offsetGet(0));
        $this->assertEquals($expectedHeadScript2, $pluginManager->get('headScript')->getContainer()->offsetGet(1));
        $this->assertEquals(1, $pluginManager->get('headLink')->getContainer()->count());
        $this->assertEquals($expectedHeadLink, $pluginManager->get('headLink')->getContainer()->offsetGet(0));
    }
}
