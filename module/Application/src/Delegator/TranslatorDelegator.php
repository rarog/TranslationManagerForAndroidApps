<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
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
