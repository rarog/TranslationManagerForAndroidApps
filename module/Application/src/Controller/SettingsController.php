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

namespace Application\Controller;

use Application\Form\UserLanguagesForm;
use Application\Model\UserLanguages;
use Application\Model\UserLanguagesTable;
use Application\Model\UserSettings;
use Application\Model\UserSettingsTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\Session\Container;

class SettingsController extends AbstractActionController
{
    /**
     * @var UserLanguagesTable
     */
    private $userLanguagesTable;

    /**
     * @var UserSettingsTable
     */
    private $userSettingsTable;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * Constructor
     *
     * @param UserLanguagesTable $userLanguagesTable
     * @param Translator $translator
     */
    public function __construct(UserLanguagesTable $userLanguagesTable, UserSettingsTable $userSettingsTable, Translator $translator)
    {
        $this->userLanguagesTable = $userLanguagesTable;
        $this->userSettingsTable = $userSettingsTable;
        $this->translator = $translator;
    }

    public function indexAction()
    {
        return $this->redirect()->toRoute('home');
    }

    public function userlanguagesAction()
    {
        $localeNamesAll = $this->configHelp('settings')->locale_names_primary->toArray();
        $localeNames = $localeNamesAll[$this->translator->getLocale()];

        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();

        try {
            $userSettings = $this->userSettingsTable->getUserSettings($userId);
        } catch (\RuntimeException $e) {
            $userSettings = new UserSettings([
                'user_id' => $userId,
                'locale' => 'en_US',
            ]);
        }

        $userLanguages = [];
        foreach ($this->userLanguagesTable->fetchAllOfUser($userId) as $userLanguage) {
            $userLanguages[] = $userLanguage->Locale;
        }

        $form = new UserLanguagesForm();
        $form->get('interfacelanguage')
            ->setValueOptions($this->configHelp('settings')->supported_languages->toArray())
            ->setValue($userSettings->Locale);
        $form->get('languages')
            ->setValueOptions($localeNames)
            ->setValue($userLanguages);

        $viewData = [
            'form'     => $form,
            'messages' => [],
        ];

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setData($request->getPost());

        $userSettings->Locale = $form->get('interfacelanguage')->getValue();
        $this->userSettingsTable->saveUserSettings($userSettings);

        $userSettingsContainer = new Container('userSettings');
        if (isset($userSettingsContainer->init) &&
            ($userSettingsContainer->locale !== $userSettings->Locale)) {
            // Let bootstrapUserSettings renew the settings cache on next page load.
            unset($userSettingsContainer->init);

            // Set the locale for current output.
            $this->translator->setLocale($userSettings->locale);
            $this->translator->setFallbackLocale(\Locale::getPrimaryLanguage($userSettings->locale));
        }

        $newUserLanguages = $form->get('languages')->getValue();

        foreach($userLanguages as $locale) {
            if (!in_array($locale, $newUserLanguages)) {
                $this->userLanguagesTable->deleteUserLanguage($userId, $locale);
            }
        }

        foreach($newUserLanguages as $locale) {
            if (!in_array($locale, $userLanguages)) {
                $userLanguage = new UserLanguages([
                    'user_id' => $userId,
                    'locale'  => $locale,
                ]);
                $this->userLanguagesTable->saveUserLanguage($userLanguage);
            }
        }

        $viewData['messages'][] = [
            'canClose' => true,
            'message'  => $this->translator->translate('The selected languages were saved successfully.'),
            'type'     => 'success',
        ];

        return $viewData;
    }
}
