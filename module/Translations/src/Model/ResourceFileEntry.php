<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use DomainException;
use Zend\Filter\Boolean;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\Filter\ToNull;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Validator\StringLength;

class ResourceFileEntry implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var null|int
     */
    private $id;

    /**
     * @var null|int
     */
    private $appResourceFileId;

    /**
     * @var null|int
     */
    private $resourceTypeId;

    /**
     * @var null|string
     */
    private $name;

    /**
     * @var null|boolean
     */
    private $deleted;

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
     * @param unknown $name
     * @throws \Exception
     * @return unknown
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
     * @param unknown $name
     * @param unknown $value
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
    public function getAppResourceFileId() {
        return $this->appResourceFileId;
    }

    /**
     * @param null|int $appResourceFileId
     */
    public function setAppResourceFileId($appResourceFileId) {
        if (!is_null($appResourceFileId)) {
            $appResourceFileId = (int) $appResourceFileId;
        }
        $this->appResourceFileId= $appResourceFileId;
    }

    /**
     * @return null|int
     */
    public function getResourceTypeId() {
        return $this->resourceTypeId;
    }

    /**
     * @param null|int $resourceTypeId
     */
    public function setResourceTypeId($resourceTypeId) {
        if (!is_null($resourceTypeId)) {
            $resourceTypeId = (int) $resourceTypeId;
        }
        $this->resourceTypeId= $resourceTypeId;
    }

    /**
     * @return null|string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName($name) {
        if (!is_null($name)) {
            $name = (string) $name;
        }
        $this->name = $name;
    }

    /**
     * @return null|boolean
     */
    public function getDeleted() {
        return $this->deleted;
    }

    /**
     * @param null|boolean $deleted
     */
    public function setDeleted($deleted) {
        if (!is_null($deleted)) {
            $deleted = (boolean) $deleted;
        }
        $this->deleted = $deleted;
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
            'name'     => 'app_resource_file_id',
            'required' => true,
            'filters'  => [
                [
                    'name'    => ToNull::class,
                    'options' => ['type' => ToNull::TYPE_INTEGER],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'resource_type_id',
            'required' => true,
            'filters'  => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'name',
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
                        'min' => 1,
                        'max' => 255,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'deleted',
            'required'   => false,
            'filters'    => [
                ['name' => Boolean::class],
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
        $this->Id                = !empty($data['id']) ? $data['id'] : null;
        $this->AppResourceFileId = !empty($data['app_resource_file_id']) ? $data['app_resource_file_id'] : null;
        $this->ResourceTypeId    = !empty($data['resource_type_id']) ? $data['resource_type_id'] : null;
        $this->Name              = !empty($data['name']) ? $data['name'] : null;
        $this->Deleted           = !empty($data['deleted']) ? $data['deleted'] : null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'id'                   => $this->Id,
            'app_resource_file_id' => $this->AppResourceFileId,
            'resource_type_id'     => $this->ResourceTypeId,
            'name'                 => $this->Name,
            'deleted'              => $this->Deleted,
        ];
    }
}
