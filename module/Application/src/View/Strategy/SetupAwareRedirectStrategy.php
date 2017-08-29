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

namespace Application\View\Strategy;

use Setup\Model\DatabaseHelper as SetupDatabaseHelper;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use ZfcRbac\Exception\UnauthorizedExceptionInterface;
use ZfcRbac\Options\RedirectStrategyOptions;
use ZfcRbac\View\Strategy\AbstractStrategy;

/**
 * This strategy redirects to another route when a user is unauthorized.
 * If setup hasn't run yet, it will redirect to it.
 * Based on ZfcRbac\View\Strategy\RedirectStrategy
 */
class SetupAwareRedirectStrategy extends AbstractStrategy
{
    /**
     * @var RedirectStrategyOptions
     */
    protected $options;

    /**
     * @var AuthenticationServiceInterface
     */
    protected $authenticationService;

    /**
     * @var SetupDatabaseHelper
     */
    protected $setupDatabaseHelper;

    /**
     * Constructor
     *
     * @param RedirectStrategyOptions        $options
     * @param AuthenticationServiceInterface $authenticationService
     * @param SetupDatabaseHelper            $setupDatabaseHelper
     */
    public function __construct(RedirectStrategyOptions $options, AuthenticationServiceInterface $authenticationService, SetupDatabaseHelper $setupDatabaseHelper)
    {
        $this->options               = $options;
        $this->authenticationService = $authenticationService;
        $this->setupDatabaseHelper   = $setupDatabaseHelper;
    }

    /**
     * {@inheritDoc}
     * @see \ZfcRbac\View\Strategy\AbstractStrategy::onError()
     */
    public function onError(MvcEvent $event)
    {
        // Do nothing if no error or if response is not HTTP response
        if (!($event->getParam('exception') instanceof UnauthorizedExceptionInterface)
            || ($event->getResult() instanceof HttpResponse)
            || !($event->getResponse() instanceof HttpResponse)
        ) {
            return;
        }

        $router = $event->getRouter();

        if ($this->authenticationService->hasIdentity()) {
            if (!$this->options->getRedirectWhenConnected()) {
                return;
            }

            $redirectRoute = $this->options->getRedirectToRouteConnected();
        } else {
            if ($this->setupDatabaseHelper->isSetupComplete()) {
                $redirectRoute = $this->options->getRedirectToRouteDisconnected();
            } else {
                $redirectRoute = 'setup';
            }
        }

        $uri = $router->assemble([], ['name' => $redirectRoute]);

        if ($this->options->getAppendPreviousUri()) {
            $redirectKey = $this->options->getPreviousUriQueryKey();
            $previousUri = $event->getRequest()->getUriString();

            $uri = $router->assemble(
                [],
                [
                    'name' => $redirectRoute,
                    'query' => [$redirectKey => $previousUri]
                ]
            );
        }

        $response = $event->getResponse() ?: new HttpResponse();

        $response->getHeaders()->addHeaderLine('Location', $uri);
        $response->setStatusCode(302);

        $event->setResponse($response);
        $event->setResult($response);
    }
}
