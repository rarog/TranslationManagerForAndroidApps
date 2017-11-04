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

namespace Translations\Validator;

use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Sql\Select;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Db\NoRecordExists;

class ResValuesName extends AbstractValidator implements AdapterAwareInterface
{
    use AdapterAwareTrait;

    const VALUESNAME = 'valuesname';
    const INVALIDDATA = 'invaliddata';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::VALUESNAME => 'Resource values folder name must be equal to "values" or begin with "values-"',
        self::INVALIDDATA => 'Invalid data was passed',
    ];

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param mixed $value
     * @param mixed $context Additional context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        if (!preg_match('/values$/', $value) &&
            !preg_match('/values-.*[a-zA-Z0-9-]/', $value)) {
            $this->error(self::VALUESNAME);
            return false;
        }

        if (!is_array($context)) {
            $this->error(self::INVALIDDATA);
            return false;
        }

        $appId = (isset($context['app_id'])) ? (int) $context['app_id'] : 0;
        if ($appId <= 0) {
            $this->error(self::INVALIDDATA);
            return false;
        }

        $id = (isset($context['id'])) ? (int) $context['id'] : 0;

        $select = new Select('app_resource');
        $select->where->equalTo('name', $value)
            ->where->equalTo('app_id', $appId);

        if ($id > 0) {
            $select->where->notEqualTo('id', $id);
        }

        $validator = new NoRecordExists($select);
        $validator->setAdapter($this->adapter);
        if (!$validator->isValid($value)) {
            $this->abstractOptions['messageTemplates'] = array_merge($this->abstractOptions['messageTemplates'], $validator->getMessageTemplates());
            $this->error($validator::ERROR_RECORD_FOUND);
            return false;
        }

        return true;
    }
}
