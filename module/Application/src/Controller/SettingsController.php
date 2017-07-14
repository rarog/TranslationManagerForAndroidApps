<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Translations\Form\UserLanguagesForm;

class SettingsController extends AbstractActionController
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * Constructor
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function indexAction()
    {
        return $this->redirect()->toRoute('home');
    }

    public function userlanguagesAction()
    {
        $localeNamesAll = $this->configHelp('settings')->locale_names->toArray();
        $localeNames = $localeNamesAll[$this->translator->getLocale()];

        $form = new UserLanguagesForm();
        $form->get('languages')->setValueOptions($localeNames);

        return [
            'form' => $form,
        ];
    }
}
