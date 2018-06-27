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

namespace Common\Model;

use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Validator\StringLength;

class Setting extends AbstractDbTableEntry implements
    ArraySerializableInterface,
    InputFilterAwareInterface
{
    /**
     * @var null|int
     */
    private $id;

    /**
     * @var null|string
     */
    private $path;

    /**
     * @var null|string
     */
    private $value;

    /**
     * @return null|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null|int $id
     * @return \Common\Model\Setting
     */
    public function setId($id)
    {
        if (! is_null($id)) {
            $id = (int) $id;
        }
        $this->id = $id;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param null|string $path
     * @return \Common\Model\Setting
     */
    public function setPath($path)
    {
        if (! is_null($path)) {
            $path = (string) $path;
        }
        $this->path = $path;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param null|string $value
     * @return \Common\Model\Setting
     */
    public function setValue($value)
    {
        if (! is_null($value)) {
            $value = (string) $value;
        }
        $this->value = $value;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Common\Model\AbstractDbTableEntry::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $this->setId(isset($array['id']) ? $array['id'] : null);
        $this->setPath(isset($array['path']) ? $array['path'] : null);
        $this->setValue(isset($array['value']) ? $array['value'] : null);
    }

    /**
     * {@inheritDoc}
     * @see \Common\Model\AbstractDbTableEntry::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'id' => $this->getId(),
            'path' => $this->getPath(),
            'value' => $this->getValue(),
        ];
    }

    /**
     * {@inheritDoc}
     * @see \Common\Model\AbstractDbTableEntry::getInputFilter()
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
            'name' => 'path',
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
            'name' => 'value',
            'required' => false,
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }
}
