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

namespace ApplicationTest\Helper;

use Application\Delegator\TranslatorDelegator;
use PHPUnit\Framework\TestCase;
use Zend\I18n\Translator\Resources;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

class TranslatorDelegatorTest extends TestCase
{
    private $serviceManager;

    private $translator;

    private $translatorDelegator;

    protected function setUp()
    {
        $this->serviceManager = $this->prophesize(ServiceManager::class);
        $this->translator = $this->prophesize(Translator::class);
        $this->translatorDelegator = new TranslatorDelegator();
    }

    protected function tearDown()
    {
        unset($this->translatorDelegator);
        unset($this->translator);
        unset($this->serviceManager);
    }

    public function testInvoke()
    {
        $translatorDelegator = $this->translatorDelegator;

        $this->translator->addTranslationFilePattern(
            'phparray',
            Resources::getBasePath(),
            Resources::getPatternForCaptcha()
        )->shouldBeCalledTimes(1);
        $this->translator->addTranslationFilePattern(
            'phparray',
            Resources::getBasePath(),
            Resources::getPatternForValidator()
        )->shouldBeCalledTimes(1);

        $name = Translator::class;
        $options = null;

        // This is a short version of what ServiceManager->createDelegatorFromName() does.
        $creationCallback = function () {
            return $this->translator->reveal();
        };
        $creationCallback = function () use ($translatorDelegator, $name, $creationCallback, $options) {
            return $translatorDelegator($this->serviceManager->reveal(), $name, $creationCallback, $options);
        };

        $result = $creationCallback(
            $this->serviceManager->reveal(),
            $name,
            $creationCallback,
            $options
        );

        $this->assertSame($this->translator->reveal(), $result);
    }
}
