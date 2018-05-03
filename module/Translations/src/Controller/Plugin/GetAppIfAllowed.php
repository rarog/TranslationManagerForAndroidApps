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

namespace Translations\Controller\Plugin;

use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use ZfcRbac\Service\AuthorizationService;
use RuntimeException;

class GetAppIfAllowed extends AbstractPlugin
{
    /**
     * @var AppTable
     */
    private $appTable;

    /**
     * @var AppResourceTable
     */
    private $appResourceTable;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * Constructor
     *
     * @param AppTable $appTable
     * @param AppResourceTable $appResourceTable
     * @param AuthorizationService $authorizationService
     */
    public function __construct(
        AppTable $appTable,
        AppResourceTable $appResourceTable,
        AuthorizationService $authorizationService
    ) {
        $this->appTable = $appTable;
        $this->appResourceTable = $appResourceTable;
        $this->authorizationService = $authorizationService;
    }

    /**
     * Check if current user has permission to the app and return it
     * @param int $appId
     * @param bool $checkHasDefaultValues
     * @return boolean|\Translations\Model\App
     */
    public function __invoke(int $appId, bool $checkHasDefaultValues = false)
    {
        if (0 === $appId) {
            return false;
        }

        try {
            $app = $this->appTable->getApp($appId);
        } catch (RuntimeException $e) {
            return false;
        }

        if (! $this->authorizationService->isGranted('app.viewAll') &&
            ! $this->appTable->hasUserPermissionForApp(
                $this->getController()->zfcUserAuthentication()->getIdentity()->getId(),
                $app->getId()
            )
        ) {
            return false;
        }

        if ($checkHasDefaultValues) {
            try {
                $this->appResourceTable->getAppResourceByAppIdAndName($app->getId(), 'values');
            } catch (RuntimeException $e) {
                return false;
            }
        }

        return $app;
    }
}
