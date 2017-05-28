<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use RuntimeException;
use Translations\Form\DeleteHelperForm;
use Translations\Form\TeamForm;
use Translations\Model\Team;
use Translations\Model\TeamTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TeamController extends AbstractActionController
{
    /**
     * @var TeamTable
     */
    private $table;

    /**
     * Constructor
     *
     * @param TeamTable $table
     */
    public function __construct(TeamTable $table)
    {
        $this->table = $table;
    }

    /**
     * Team add action
     *
     * @throws RuntimeException
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $form = new TeamForm();

        $request = $this->getRequest();
        $viewData = [
            'form' => $form,
        ];

        if (!$request->isPost()) {
            return $viewData;
        }

        $team = new Team();
        $form->setInputFilter($team->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        $team->exchangeArray($form->getData());
        $team = $this->table->saveTeam($team);

        return $this->redirect()->toRoute('team', [
            'action' => 'index',
        ]);
    }

    /**
     * Team delete action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        try {
            $team = $this->table->getTeam($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('team', [
                'action' => 'index',
            ]);
        }

        $form = new DeleteHelperForm();
        $form->add([
            'name' => 'id',
            'type' => 'hidden',
        ])->add([
            'name' => 'name',
            'type' => 'hidden',
        ])->bind($team);

        $request = $this->getRequest();
        $viewData = [
            'form' => $form,
            'team' => $team,
        ];

        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($team->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        if ($request->getPost('del', 'false') === 'true') {
            $id = (int) $request->getPost('id');
            $this->table->deleteTeam($id);
        }

        return $this->redirect()->toRoute('team', [
            'action' => 'index'
        ]);
    }

    /**
     * Team edit action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {

        $id = (int) $this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('team', [
                'action' => 'add',
            ]);
        }

        try {
            $team = $this->table->getTeam($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('team', [
                'action' => 'index',
            ]);
        }

        $form = new TeamForm();
        $form->bind($team);

        $request = $this->getRequest();
        $viewData = [
            'form' => $form,
            'team' => $team,
        ];

        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($team->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        $this->table->saveTeam($team);

        return $this->redirect()->toRoute('team', [
            'action' => 'index',
        ]);
    }


    /**
     * Team overview action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return [
            'teams' => $this->table->fetchAll(),
        ];
    }
}
