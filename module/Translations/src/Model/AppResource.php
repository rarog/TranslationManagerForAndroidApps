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
use Translations\Validator\ResValuesName;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Filter\BaseName;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Validator\StringLength;

class AppResource extends AbstractDbTableEntry implements
    AdapterAwareInterface,
    ArraySerializableInterface,
    InputFilterAwareInterface
{
    use AdapterAwareTrait;

    /**
     * @var null|int
     */
    private $id;

    /**
     * @var null|int
     */
    private $appId;

    /**
     * @var null|string
     */
    private $name;

    /**
     * @var null|string
     */
    private $locale;

    /**
     * @var null|string
     */
    private $description;

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
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param null|int $appId
     */
    public function setAppId($appId)
    {
        if (! is_null($appId)) {
            $appId = (int) $appId;
        }
        $this->appId = $appId;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
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
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param null|string $locale
     */
    public function setLocale($locale)
    {
        if (! is_null($locale)) {
            $locale = (string) $locale;
        }
        $this->locale = $locale;
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
            'name'     => 'app_id',
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
                ['name' => BaseName::class],
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
                [
                    'name'    => ResValuesName::class,
                    'options' => [
                        'dbAdapter' => $this->adapter,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'locale',
            'required'   => true,
            'filters'    => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name'    => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min'      => 1,
                        'max'      => 20,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'description',
            'required'   => false,
            'filters'    => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name'    => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min'      => 0,
                        'max'      => 255,
                    ],
                ],
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
        $this->setAppId(isset($array['app_id']) ? $array['app_id'] : null);
        $this->setName(isset($array['name']) ? $array['name'] : null);
        $this->setLocale(isset($array['locale']) ? $array['locale'] : null);
        $this->setDescription(isset($array['description']) ? $array['description'] : null);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'id' => $this->getId(),
            'app_id' => $this->getAppId(),
            'name' => $this->getName(),
            'locale' => $this->getLocale(),
            'description' => $this->getDescription(),
        ];
    }
}
