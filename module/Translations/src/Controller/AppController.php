<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use RuntimeException;
use Translations\Form\AppForm;
use Translations\Form\DeleteHelperForm;
use Translations\Model\App;
use Translations\Model\AppTable;
use Translations\Model\FileHelper;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AppController extends AbstractActionController
{
    /**
     * @var AppTable
     */
    private $table;

    /**
     * Constructor
     *
     * @param AppTable $table
     */
    public function __construct(AppTable $table)
    {
        $this->table = $table;
    }

    private function getAppDir($id)
    {
        if (($path = realpath($this->configHelp('tmfaa')->app_dir)) === false) {
            throw new RuntimeException(sprintf(
                'Configured path app directory "%s" does not exist',
                $this->configHelp('tmfaa')->app_dir
            ));
        }
        return FileHelper::concatenatePath($path, (string) $id);
    }

    /**
     * App add action
     *
     * @throws RuntimeException
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $form = new AppForm();

        $request = $this->getRequest();

        if (!$request->isPost()) {
            return ['form' => $form];
        }

        $app = new App();
        $form->setInputFilter($app->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return ['form' => $form];
        }

        $app->exchangeArray($form->getData());
        $app = $this->table->saveApp($app);;

        $path = $this->getAppDir($app->id);echo $path;
        if (!mkdir($path, 0775)) {
            throw new RuntimeException(sprintf(
                'Could not create path "%s"',
                $path
            ));
        }

        return $this->redirect()->toRoute('app', ['action' => 'index']);
    }

    /**
     * App delete action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        try {
            $app = $this->table->getApp($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('app', ['action' => 'index']);
        }

        $form = new DeleteHelperForm();
        $form->bind($app);

        $request = $this->getRequest();
        $viewData = [
            'id'   => $id,
            'name' => $app->name,
            'form' => $form,
        ];

        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($app->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        if ($request->getPost('del', 'false') === 'true') {
            $id = (int) $request->getPost('id');
            $this->table->deleteApp($id);
            FileHelper::rmdirRecursive($this->getAppDir($id));
        }

        return $this->redirect()->toRoute('app', ['action' => 'index']);
    }

    /**
     * App edit action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {

        $id = (int) $this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('app', ['action' => 'add']);
        }

        try {
            $app = $this->table->getApp($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('app', ['action' => 'index']);
        }

        $form = new AppForm();
        $form->bind($app);

        $request = $this->getRequest();
        $viewData = [
            'id'   => $id,
            'form' => $form,
        ];

        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($app->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        $this->table->saveApp($app);

        return $this->redirect()->toRoute('app', ['action' => 'index']);
    }


    /**
     * App overview action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return new ViewModel([
            'apps' => $this->table->fetchAll(),
        ]);
    }
}
