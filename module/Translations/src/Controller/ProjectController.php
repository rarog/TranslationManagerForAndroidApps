<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use Translations\Model\ProjectTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ProjectController extends AbstractActionController
{
    private $table;

    /**
     * Constructor
     *
     * @param ProjectTable $table
     */
    public function __construct(ProjectTable $table)
    {
        $this->table = $table;
    }

    /**
     * Project add action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        return new ViewModel();
    }

    /**
     * Project delete action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        return new ViewModel();
    }

    /**
     * Project edit action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        return new ViewModel();
    }


    /**
     * Project overview action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return new ViewModel([
            'projects' => $this->table->fetchAll(),
        ]);
    }
}
