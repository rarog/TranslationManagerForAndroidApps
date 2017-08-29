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

namespace Application\Delegator;

use Interop\Container\ContainerInterface;
use Zend\I18n\Translator\Resources;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

class TranslatorDelegator implements DelegatorFactoryInterface
{
	/**
	 * {@inheritDoc}
	 * @see \Zend\ServiceManager\Factory\DelegatorFactoryInterface::__invoke()
	 */
	public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null) {
        $translator = $callback();

        $translator->addTranslationFilePattern(
            'phparray',
            Resources::getBasePath(),
            Resources::getPatternForValidator()
        );
        $translator->addTranslationFilePattern(
            'phparray',
            Resources::getBasePath(),
            Resources::getPatternForCaptcha()
        );

        return $translator;
	}
}
