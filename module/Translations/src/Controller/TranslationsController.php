<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use Translations\Model\AppTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\ViewModel;

class TranslationsController extends AbstractActionController
{
    /**
     * @var AppTable
     */
    private $appTable;

    /**
     * Constructor
     *
     * @param AppTable $appTable
     * @param Translator $translator
     */
    public function __construct(AppTable $appTable, Translator $translator)
    {
        $this->appTable = $appTable;
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
}
