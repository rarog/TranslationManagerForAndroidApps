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
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Validator\StringLength;

class EntryString extends AbstractDbTableEntry implements
    ArraySerializableInterface,
    InputFilterAwareInterface
{
    /**
     * @var null|int
     */
    private $entryCommonId;

    /**
     * @var null|string
     */
    private $value;

    /**
     * @return null|int
     */
    public function getEntryCommonId()
    {
        return $this->entryCommonId;
    }

    /**
     * @param null|int $id
     */
    public function setEntryCommonId($entryCommonId)
    {
        if (! is_null($entryCommonId)) {
            $entryCommonId = (int) $entryCommonId;
        }
        $this->entryCommonId = $entryCommonId;
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
     */
    public function setValue($value)
    {
        if (! is_null($value)) {
            $value = (string) $value;
        }
        $this->value = $value;
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
            'name'     => 'entry_common_id',
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

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $this->setEntryCommonId(isset($array['entry_common_id']) ? $array['entry_common_id'] : null);
        $this->setValue(isset($array['value']) ? $array['value'] : null);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'entry_common_id' => $this->getEntryCommonId(),
            'value' => $this->getValue(),
        ];
    }
}
