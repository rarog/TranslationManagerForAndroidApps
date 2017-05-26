<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Validator;

use Zend\Validator\AbstractValidator;

class ResFileName extends AbstractValidator
{
    const FILENAME = 'filename';

    protected $messageTemplates = [
        self::FILENAME => 'Resource file name must end with ".xml"',
    ];

    public function isValid($value)
    {
        $this->setValue($value);

        $isValid = true;

        if (!preg_match('/.*[a-zA-Z0-9-_\.].xml$/', $value)) {
            $this->error(self::FILENAME);
            $isValid = false;
        }

        return $isValid;
    }
}
