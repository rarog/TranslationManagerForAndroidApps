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
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\Stdlib\ArraySerializableInterface;

class Suggestion extends AbstractDbTableEntry implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var null|int
     */
    private $id;

    /**
     * @var null|int
     */
    private $entryCommonId;

    /**
     * @var null|int
     */
    private $userId;

    /**
     * @var null|int
     */
    private $lastChange;

    /**
     * @var InputFilter
     */
    private $inputFilter;

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
        if (! is_null($id)) {
            $id = (int) $id;
        }
        $this->id = $id;
    }

    /**
     * @return null|int
     */
    public function getEntryCommonId() {
        return $this->entryCommonId;
    }

    /**
     * @param null|int $entryCommonId
     */
    public function setEntryCommonId($entryCommonId) {
        if (! is_null($entryCommonId)) {
            $entryCommonId = (int) $entryCommonId;
        }
        $this->entryCommonId = $entryCommonId;
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
        if (! is_null($userId)) {
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
     * @return null|int
     */
    public function getLastChange() {
        return $this->lastChange;
    }

    /**
     * @param null|int $lastChange
     */
    public function setLastChange($lastChange) {
        if (! is_null($lastChange)) {
            $lastChange = (int) $lastChange;
        }
        $this->lastChange = $lastChange;
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
            'name'       => 'last_change',
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
        $this->EntryCommonId = !empty($array['entry_common_id']) ? $array['entry_common_id'] : null;
        $this->UserId = !empty($array['user_id']) ? $array['user_id'] : null;
        $this->LastChange = !empty($array['last_change']) ? $array['last_change'] : null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'id' => $this->Id,
            'entry_common_id' => $this->EntryCommonId,
            'user_id' => $this->UserId,
            'last_change' => $this->LastChange,
        ];
    }
}
