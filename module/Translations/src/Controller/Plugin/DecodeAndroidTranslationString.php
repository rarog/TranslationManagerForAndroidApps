<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller\Plugin;

use Zend\Log\Logger;
use Zend\Json\Json;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class DecodeAndroidTranslationString extends AbstractPlugin
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Normalizes the translation
     *
     * @param string $translationString
     * @return string
     */
    public function __invoke($translationString)
    {
        $translationString = (string) $translationString;

        if (mb_substr($translationString, 0, 1) === '"') {
            $jsonTranslationString = $translationString;
        } else {
            $jsonTranslationString = '"' . str_replace('\\\'', '\'', $translationString) . '"';
        }

        try {
            return Json::decode($jsonTranslationString);
        } catch (\Exception $e) {
            $message = sprintf('Android string: %s
Exception message: %s
Exception trace:
%s', $translationString, $e->getMessage(), $e->getTraceAsString());
            $this->logger->err('An error during decoding of Android string', ['messageExtended' => $message]);

            return $translationString;
        }
    }
}
