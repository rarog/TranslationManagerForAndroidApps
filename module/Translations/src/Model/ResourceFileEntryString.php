<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
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

class ResourceFileEntryString implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var null|int
     */
    private $id;

    /**
     * @var null|int
     */
    private $appResourceId;

    /**
     * @var null|int
     */
    private $resourceFileEntryId;

    /**
     * @var null|string
     */
    private $value;

    /**
     * @var null|int
     */
    private $lastChange;

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
    public function getAppResourceId() {
        return $this->appResourceId;
    }

    /**
     * @param null|int $appResourceId
     */
    public function setAppResourceId($appResourceId) {
        if (!is_null($appResourceId)) {
            $appResourceId = (int) $appResourceId;
        }
        $this->appResourceId = $appResourceId;
    }

    /**
     * @return null|int
     */
    public function getResourceFileEntryId() {
        return $this->resourceFileEntryId;
    }

    /**
     * @param null|int $resourceFileEntryId
     */
    public function setResourceFileEntryId($resourceFileEntryId) {
        if (!is_null($resourceFileEntryId)) {
            $resourceFileEntryId = (int) $resourceFileEntryId;
        }
        $this->resourceFileEntryId = $resourceFileEntryId;
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
    public function getLastChange() {
        return $this->lastChange;
    }

    /**
     * @param null|int $lastChange
     */
    public function setLastChange($lastChange) {
        if (!is_null($lastChange)) {
            $lastChange = (int) $lastChange;
        }
        $this->lastChange = $lastChange;
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
            'name'     => 'app_resource_id',
            'required' => true,
            'filters'  => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'resource_file_entry_id',
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
            'name'     => 'last_change',
            'required' => true,
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
    public function exchangeArray(array $data)
    {
        $this->Id                  = !empty($data['id']) ? $data['id'] : null;
        $this->AppResourceId       = !empty($data['app_resource_id']) ? $data['app_resource_id'] : null;
        $this->ResourceFileEntryId = !empty($data['resource_file_entry_id']) ? $data['resource_file_entry_id'] : null;
        $this->Value               = !empty($data['value']) ? $data['value'] : null;
        $this->LastChange          = !empty($data['last_change']) ? $data['last_change'] : null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'id'                     => $this->Id,
            'app_resource_id'        => $this->AppResourceId,
            'resource_file_entry_id' => $this->ResourceFileEntryId,
            'value'                  => $this->Value,
            'last_change'            => $this->LastChange,
        ];
    }
}
