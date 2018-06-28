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

namespace TranslationsTest\Factory\Controller;

use PHPUnit\Framework\TestCase;
use Translations\Controller\TranslationsController;
use Translations\Factory\Controller\TranslationsControllerFactory;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Translations\Model\EntryCommonTable;
use Translations\Model\EntryStringTable;
use Translations\Model\ResourceFileEntryTable;
use Translations\Model\ResourceTypeTable;
use Translations\Model\SuggestionStringTable;
use Translations\Model\SuggestionTable;
use Translations\Model\SuggestionVoteTable;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Renderer\PhpRenderer;
use Translations\Settings\TranslationSettings;

class TranslationsControllerFactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new TranslationsControllerFactory();

        $serviceManager = new ServiceManager();

        $appTable = $this->prophesize(AppTable::class);
        $serviceManager->setService(AppTable::class, $appTable->reveal());

        $appResourceTable = $this->prophesize(AppResourceTable::class);
        $serviceManager->setService(AppResourceTable::class, $appResourceTable->reveal());

        $resourceTypeTable = $this->prophesize(ResourceTypeTable::class);
        $serviceManager->setService(ResourceTypeTable::class, $resourceTypeTable->reveal());

        $resourceFileEntryTable = $this->prophesize(ResourceFileEntryTable::class);
        $serviceManager->setService(ResourceFileEntryTable::class, $resourceFileEntryTable->reveal());

        $entryCommonTable = $this->prophesize(EntryCommonTable::class);
        $serviceManager->setService(EntryCommonTable::class, $entryCommonTable->reveal());

        $entryStringTable = $this->prophesize(EntryStringTable::class);
        $serviceManager->setService(EntryStringTable::class, $entryStringTable->reveal());

        $suggestionTable = $this->prophesize(SuggestionTable::class);
        $serviceManager->setService(SuggestionTable::class, $suggestionTable->reveal());

        $suggestionStringTable = $this->prophesize(SuggestionStringTable::class);
        $serviceManager->setService(SuggestionStringTable::class, $suggestionStringTable->reveal());

        $suggestionVoteTable = $this->prophesize(SuggestionVoteTable::class);
        $serviceManager->setService(SuggestionVoteTable::class, $suggestionVoteTable->reveal());

        $translationSettings = $this->prophesize(TranslationSettings::class);
        $serviceManager->setService(TranslationSettings::class, $translationSettings->reveal());

        $translator = $this->prophesize(Translator::class);
        $serviceManager->setService(Translator::class, $translator->reveal());

        $phpRenderer = $this->prophesize(PhpRenderer::class);
        $serviceManager->setService(PhpRenderer::class, $phpRenderer->reveal());

        $translationsController = $factory($serviceManager, null);
        $this->assertInstanceOf(TranslationsController::class, $translationsController);
    }
}
