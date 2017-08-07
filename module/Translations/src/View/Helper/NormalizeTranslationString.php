<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\View\Helper;

use Zend\Json\Json;
use Zend\View\Helper\AbstractHelper;

class NormalizeTranslationString extends AbstractHelper
{
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
            return $translationString;
        }
    }
}
