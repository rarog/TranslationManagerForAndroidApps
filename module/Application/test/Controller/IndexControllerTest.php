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
namespace ApplicationTest\Controller;

use Application\Controller\IndexController;
use PHPUnit\Framework\TestCase;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Router\RouteMatch;

class IndexControllerTest extends TestCase
{

    public function setUp()
    {
        $this->controller = new IndexController();
        $this->request = new Request();
        $this->routeMatch = new RouteMatch([
            'controller' => 'application'
        ]);
        $this->event = new MvcEvent();
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'index');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAboutActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'about');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInvalidRouteDoesNotCrash()
    {
        $this->routeMatch->setParam('action', 'invalid-action');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }
}
