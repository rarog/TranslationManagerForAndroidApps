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

use Common\Model\AbstractDbTableEntry;
use Zend\Filter\Boolean;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\Filter\ToNull;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Validator\StringLength;

class ResourceFileEntry extends AbstractDbTableEntry implements
    ArraySerializableInterface,
    InputFilterAwareInterface
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
     * @var boolean
     */
    private $deleted;

    /**
     * @var null|boolean
     */
    private $translatable;

    /**
     * @return null|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null|int $id
     */
    public function setId($id)
    {
        if (! is_null($id)) {
            $id = (int) $id;
        }
        $this->id = $id;
    }

    /**
     * @return null|int
     */
    public function getAppResourceFileId()
    {
        return $this->appResourceFileId;
    }

    /**
     * @param null|int $appResourceFileId
     */
    public function setAppResourceFileId($appResourceFileId)
    {
        if (! is_null($appResourceFileId)) {
            $appResourceFileId = (int) $appResourceFileId;
        }
        $this->appResourceFileId = $appResourceFileId;
    }

    /**
     * @return null|int
     */
    public function getResourceTypeId()
    {
        return $this->resourceTypeId;
    }

    /**
     * @param null|int $resourceTypeId
     */
    public function setResourceTypeId($resourceTypeId)
    {
        if (! is_null($resourceTypeId)) {
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
    public function setName($name)
    {
        if (! is_null($name)) {
            $name = (string) $name;
        }
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param null|string $product
     */
    public function setProduct($product)
    {
        if (! is_null($product)) {
            $product = (string) $product;
        }
        $this->product = $product;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription($description)
    {
        if (! is_null($description)) {
            $description = (string) $description;
        }
        $this->description = $description;
    }

    /**
     * @return boolean
     */
    public function getDeleted()
    {
        if (is_null($this->deleted)) {
            $this->deleted = false;
        }
        return $this->deleted;
    }

    /**
     * @param null|boolean $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = (boolean) $deleted;
    }

    /**
     * @return null|boolean
     */
    public function getTranslatable()
    {
        if (is_null($this->translatable)) {
            $this->translatable = false;
        }
        return $this->translatable;
    }

    /**
     * @param null|boolean $translatable
     */
    public function setTranslatable($translatable)
    {
        $translatable = (boolean) $translatable;

        $this->translatable = $translatable;
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
        $this->setId(isset($array['id']) ? $array['id'] : null);
        $this->setAppResourceFileId(isset($array['app_resource_file_id']) ? $array['app_resource_file_id'] : null);
        $this->setResourceTypeId(isset($array['resource_type_id']) ? $array['resource_type_id'] : null);
        $this->setName(isset($array['name']) ? $array['name'] : null);
        $this->setProduct(isset($array['product']) ? $array['product'] : null);
        $this->setDescription(isset($array['description']) ? $array['description'] : null);
        $this->setDeleted(isset($array['deleted']) ? $array['deleted'] : null);
        $this->setTranslatable(isset($array['translatable']) ? $array['translatable'] : null);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'id'  => $this->getId(),
            'app_resource_file_id' => $this->getAppResourceFileId(),
            'resource_type_id' => $this->getResourceTypeId(),
            'name' => $this->getName(),
            'product' => $this->getProduct(),
            'description' => $this->getDescription(),
            'deleted' => $this->getDeleted(),
            'translatable' => $this->getTranslatable(),
        ];
    }
}
