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
use Application\Model\UserTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;

class UsersController extends AbstractActionController
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
     * @var UserTable
     */
    private $userTable;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * Constructor
     *
     * @param UserLanguagesTable $userLanguagesTable
     * @param UserSettingsTable $userSettingsTable
     * @param UserTable $userTable
     * @param Translator $translator
     */
    public function __construct(
        UserLanguagesTable $userLanguagesTable,
        UserSettingsTable $userSettingsTable,
        UserTable $userTable,
        Translator $translator
    ) {
        $this->userLanguagesTable = $userLanguagesTable;
        $this->userSettingsTable = $userSettingsTable;
        $this->userTable = $userTable;
        $this->translator = $translator;
    }

    public function indexAction()
    {
        if ($this->isGranted('users.viewAll')) {
            $users = $this->userTable->fetchAllPlus();
        } else {
            $users = $this->userTable->fetchAllPlusAllowedToUser($this->zfcUserAuthentication()->getIdentity()->getId());
        }

        return [
            'users' => $users,
        ];
    }

    public function userlanguagesAction()
    {
        $userId = (int) $this->params()->fromRoute('userId', 0);

        if (0 === $userId) {
            return $this->redirect()->toRoute('users', ['action' => 'index']);
        }

        try {
            $user = $this->userTable->getUser($userId);
        } catch (\RuntimeException $e) {
            return $this->redirect()->toRoute('users', ['action' => 'index']);
        }

        $localeNamesAll = $this->configHelp('settings')->locale_names_primary->toArray();
        $localeNames = $localeNamesAll[$this->translator->getLocale()];

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
