<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use Translations\Model\AppTable;
use Zend\Mvc\Controller\AbstractActionController;
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
     */
    public function __construct(AppTable $appTable)
    {
        $this->appTable = $appTable;
    }

    /**
     * Translations overview action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        if ($this->isGranted('team.viewAll')) {
            $apps = $this->appTable->fetchAll();
        } else {
            $apps = $this->appTable->fetchAllAllowedToUser($this->zfcUserAuthentication()->getIdentity()->getId());
        }

        return [
            'apps'  => $apps,
        ];
    }
}
