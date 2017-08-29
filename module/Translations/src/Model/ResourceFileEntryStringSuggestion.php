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

namespace Translations\Model;

use DomainException;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Validator\StringLength;

class ResourceFileEntryStringSuggestion implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var null|int
     */
    private $id;

    /**
     * @var null|int
     */
    private $resourceFileEntryStringId;

    /**
     * @var null|int
     */
    private $userId;

    /**
     * @var null|string
     */
    private $value;

    /**
     * @var null|int
     */
    private $created;

    /**
     * @var InputFilter
     */
    private $inputFilter;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = null)
    {
        if ($data) {
            $this->exchangeArray($data);
        }
    }

    /**
     * @param mixed $name
     * @throws \Exception
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new \Exception('Invalid property');
        }
        return $this->$method();
    }

    /**
     * @param mixed $name
     * @param mixed $value
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (!method_exists($this, $method)) {
            throw new \Exception('Invalid property');
        }
        $this->$method($value);
    }

    /**
     * @return null|int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param null|int $id
     */
    public function setId($id) {
        if (!is_null($id)) {
            $id = (int) $id;
        }
        $this->id = $id;
    }

    /**
     * @return null|int
     */
    public function getResourceFileEntryStringId() {
        return $this->resourceFileEntryStringId;
    }

    /**
     * @param null|int $resourceFileEntryStringId
     */
    public function setResourceFileEntryStringId($resourceFileEntryStringId) {
        if (!is_null($resourceFileEntryStringId)) {
            $resourceFileEntryStringId = (int) $resourceFileEntryStringId;
        }
        $this->resourceFileEntryStringId = $resourceFileEntryStringId;
    }

    /**
     * @return null|int
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * @param null|int $userId
     */
    public function setUserId($userId) {
        if (!is_null($userId)) {
            $userId = (int) $userId;
        }
        $this->userId = $userId;
    }

    /**
     * @return null|string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param null|string $value
     */
    public function setValue($value) {
        if (!is_null($value)) {
            $value = (string) $value;
        }
        $this->value = $value;
    }

    /**
     * @return null|int
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @param null|int $created
     */
    public function setCreated($created) {
        if (!is_null($created)) {
            $created = (int) $created;
        }
        $this->created = $created;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\InputFilter\InputFilterAwareInterface::setInputFilter()
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf(
            '%s does not allow injection of an alternate input filter',
            __CLASS__
        ));
    }

    /**
     * {@inheritDoc}
     * @see \Zend\InputFilter\InputFilterAwareInterface::getInputFilter()
     */
    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name'     => 'id',
            'required' => true,
            'filters'  => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'resource_file_entry_string_id',
            'required' => true,
            'filters'  => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'user_id',
            'required' => true,
            'filters'  => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'value',
            'required' => true,
            'filters'  => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name'    => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 0,
                        'max' => 20480,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'created',
            'required'   => true,
            'filters'  => [
                ['name' => ToInt::class],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $this->Id = !empty($array['id']) ? $array['id'] : null;
        $this->ResourceFileEntryStringId = !empty($array['resource_file_entry_string_id']) ? $array['resource_file_entry_string_id'] : null;
        $this->UserId = !empty($array['user_id']) ? $array['user_id'] : null;
        $this->Value = !empty($array['value']) ? $array['value'] : null;
        $this->Created = !empty($array['created']) ? $array['created'] : null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'id' => $this->Id,
            'resource_file_entry_string_id' => $this->ResourceFileEntryStringId,
            'user_id' => $this->UserId,
            'value' => $this->Value,
            'created' => $this->Created,
        ];
    }
}
