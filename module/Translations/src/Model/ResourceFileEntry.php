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
     * @var null|string
     */
    private $product;

    /**
     * @var null|string
     */
    private $description;

    /**
     * @var null|boolean
     */
    private $deleted;

    /**
     * @var null|boolean
     */
    private $translatable;

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

        if (!isset($this->deleted)) {
            $this->setDeleted(false);
        }

        if (!isset($this->translatable)) {
            $this->setTranslatable(true);
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
        $this->appResourceFileId = $appResourceFileId;
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
        $this->resourceTypeId = $resourceTypeId;
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
     * @return null|string
     */
    public function getProduct() {
        return $this->product;
    }

    /**
     * @param null|string $product
     */
    public function setProduct($product) {
        if (!is_null($product)) {
            $product = (string) $product;
        }
        $this->product = $product;
    }

    /**
     * @return null|string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription($description) {
        if (!is_null($description)) {
            $description = (string) $description;
        }
        $this->description = $description;
    }

    /**
     * @return null|boolean
     */
    public function getDeleted() {
        if (is_null($this->deleted)) {
            $this->deleted = false;
        }
        return $this->deleted;
    }

    /**
     * @param null|boolean $deleted
     */
    public function setDeleted($deleted) {
        $deleted = (boolean) $deleted;

        $this->deleted = $deleted;
    }

    /**
     * @return null|boolean
     */
    public function getTranslatable() {
        if (is_null($this->translatable)) {
            $this->translatable = false;
        }
        return $this->translatable;
    }

    /**
     * @param null|boolean $translatable
     */
    public function setTranslatable($translatable) {
        $translatable = (boolean) $translatable;

        $this->translatable = $translatable;
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
            'name' => 'id',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'app_resource_file_id',
            'required' => true,
            'filters' => [
                [
                    'name' => ToNull::class,
                    'options' => ['type' => ToNull::TYPE_INTEGER],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'resource_type_id',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'name',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 255,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'product',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 255,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'description',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 0,
                        'max' => 4096,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'deleted',
            'required' => true,
            'filters' => [
                ['name' => Boolean::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'translatable',
            'required' => true,
            'filters' => [
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
    public function exchangeArray(array $array)
    {
        $this->Id = !empty($array['id']) ? $array['id'] : null;
        $this->AppResourceFileId = !empty($array['app_resource_file_id']) ? $array['app_resource_file_id'] : null;
        $this->ResourceTypeId = !empty($array['resource_type_id']) ? $array['resource_type_id'] : null;
        $this->Name = !empty($array['name']) ? $array['name'] : null;
        $this->Product = !empty($array['product']) ? $array['product'] : null;
        $this->Description = !empty($array['description']) ? $array['description'] : null;
        $this->Deleted = !empty($array['deleted']) ? $array['deleted'] : 0;
        $this->Translatable = !empty($array['translatable']) ? $array['translatable'] : 0;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'id'  => $this->Id,
            'app_resource_file_id' => $this->AppResourceFileId,
            'resource_type_id' => $this->ResourceTypeId,
            'name' => $this->Name,
            'product' => $this->Product,
            'description' => $this->Description,
            'deleted' => $this->Deleted,
            'translatable' => $this->Translatable,
        ];
    }
}
