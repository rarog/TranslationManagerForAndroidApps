<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Validator;

use Zend\Validator\AbstractValidator;

class ResValuesName extends AbstractValidator
{
    const VALUESNAME= 'valuesname';

    protected $messageTemplates = [
        self::VALUESNAME => 'Resource values folder name must be equal to "values" or begin with "values-"',
    ];

    public function isValid($value)
    {
        $this->setValue($value);

        $isValid = true;

        if (!preg_match('/values$/', $value) &&
            !preg_match('/values-.*[a-zA-Z0-9-]/', $value)) {
            $this->error(self::VALUESNAME);
            $isValid = false;
        }

        return $isValid;
    }
}
