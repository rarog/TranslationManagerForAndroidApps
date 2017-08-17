<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Validator;

use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Sql\Select;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Db\NoRecordExists;

class ResFileName extends AbstractValidator implements AdapterAwareInterface
{
    use AdapterAwareTrait;

    const FILENAME = 'filename';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::FILENAME => 'Resource file name must end with ".xml"',
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

        $isValid = true;

        if (!preg_match('/.*[a-zA-Z0-9-_\.].xml$/', $value)) {
            $this->error(self::FILENAME);
            return false;
        }

        $select = new Select('app_resource_file');
        $select->where->equalTo('name', $value)
            ->where->equalTo('app_id', $context['app_id'])
            ->where->notEqualTo('id', $context['id']);

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
