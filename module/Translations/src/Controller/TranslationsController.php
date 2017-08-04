<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Translations\Model\ResourceFileEntryStringTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Escaper\Escaper;

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
     * @var ResourceFileEntryStringTable
     */
    private $resourceFileEntryStringTable;

    /**
     * Check if current user has permission to the app and return it
     *
     * @param int $appId
     * @return boolean|\Translations\Model\App
     */
    private function getApp($appId)
    {
        $appId = (int) $appId;

        if (0 === $appId) {
            return false;
        }

        try {
            $app = $this->appTable->getApp($appId);
        } catch (\Exception $e) {
            return false;
        }

        if (!$this->isGranted('app.viewAll') &&
            !$this->appTable->hasUserPermissionForApp(
                $this->zfcUserAuthentication()->getIdentity()->getId(),
                $app->Id)) {
            return false;
        }

        return $app;
    }

    /**
     * Constructor
     *
     * @param AppTable $appTable
     * @param AppResourceTable $appResourceTable
     * @param ResourceFileEntryStringTable $resourceFileEntryStringTable
     * @param Translator $translator
     */
    public function __construct(AppTable $appTable, AppResourceTable $appResourceTable, ResourceFileEntryStringTable $resourceFileEntryStringTable, Translator $translator)
    {
        $this->appTable = $appTable;
        $this->appResourceTable = $appResourceTable;
        $this->resourceFileEntryStringTable = $resourceFileEntryStringTable;
        $this->translator = $translator;
    }

    /**
     * Translations overview action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $userId = 0;
        if (!$this->isGranted('team.viewAll')) {
            $userId = $this->zfcUserAuthentication()->getIdentity()->getId();
        }

        $localeNames = $this->configHelp('settings')->locale_names->toArray();
        $localeNames = $localeNames[$this->translator->getLocale()];

        $apps = [];
        $resources = [];
        $values = $this->appTable->getAllAppsAndResourcesAllowedToUser($userId);
        foreach ($values as $value) {
            if (!array_key_exists($value['app_id'], $apps)) {
                $apps[$value['app_id']] = $value['app_name'];
            }

            $resources[$value['app_id']][$value['app_resource_id']] = $localeNames[$value['locale']];
        }

        return [
            'apps' => $apps,
            'resources' => $resources,
        ];
    }

    /**
     * App resource add action
     *
     * @throws RuntimeException
     * @return \Zend\View\Model\ViewModel
     */
    public function listtranslationsAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $resourceId = (int) $this->params()->fromRoute('resourceId', 0);
        $defaultId = (int) $this->params()->fromRoute('defaultId', 0);

        $app = $this->getApp($appId);

        $viewModel = new JsonModel();
        $viewModel->setTerminal(true);

        if ($app === false) {
            return new JsonModel();
        }

        try {
            $resource = $this->appResourceTable->fetchAll([
                'id'     => $resourceId,
                'app_id' => $appId,
            ]);
        } catch (\Exception $e) {
            $resource === false;
        }

        if ($resource === false) {
            return new JsonModel();
        }

        $escaper = new Escaper('utf-8');

        $output = [];
        $entries = $this->resourceFileEntryStringTable->getAllResourceFileEntryStringsForTranslations($appId, $resourceId, $defaultId);
        foreach ($entries as $entry) {
            $output[] = [
                'defaultId' => $entry['default_id'],
                'name' => $entry['name'],
                'defaultValue' => $escaper->escapeHtml($entry['default_value']),
                'translatedValue' => $escaper->escapeHtml($entry['value']),
                'buttons' => '',
            ];
        }
        return new JsonModel($output);
    }
}
