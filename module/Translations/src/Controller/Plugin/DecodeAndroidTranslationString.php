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

        if (($translationString == '') || $translationString == '""') {
            return '';
        }

        // TODO: Add following test cases:
        // - String encapsulated by "
        // - String not encapsulated by "
        // - String not beginning with " but ending with " (values/strings.xml -> reset_network_desc)
        // - Multiline strings with real (non-escaped) newlines (values/strings.xml -> font_size_preview_text_body)

        // Fixing strings stored in multiline format. Why, is it relevant to copypaste Android strings like "font_size_preview_text_body" this way?
        // 1) Be paranoid about strings form files with Windows newlines
        $translationString = str_replace("\r\n", "\n", $translationString);
        // 2) Be paranoid about strings form files with Mac newlines
        $translationString = str_replace("\r", "\n", $translationString);
        // 3) Remove newlines and empty spaces before actual text in lines
        $splitString = explode("\n", $translationString);
        if (($splitString !== false) && !empty($splitString)) {
            $translationString = '';
            foreach ($splitString as $line) {
                $translationString .= ltrim($line);
            }
        }

        $jsonTranslationString = str_replace('\\\'', '\'', $translationString);
        if (mb_substr($jsonTranslationString, 0, 1) !== '"') {
            $jsonTranslationString = '"' . $jsonTranslationString;
        }

        if (mb_strlen(mb_substr($jsonTranslationString, -1, 1) !== '"') || (mb_substr($jsonTranslationString, -1, 2) == '\"')) {
            $jsonTranslationString .= '"';
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
