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

class SuggestionVote extends AbstractDbTableEntry implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var null|int
     */
    private $suggestionId;

    /**
     * @var null|int
     */
    private $userId;

    /**
     * @var InputFilter
     */
    private $inputFilter;

    /**
     * @return null|int
     */
    public function getSuggestionId() {
        return $this->suggestionId;
    }

    /**
     * @param null|int $suggestionId
     */
    public function setSuggestionId($suggestionId) {
        if (! is_null($suggestionId)) {
            $suggestionId = (int) $suggestionId;
        }
        $this->suggestionId = $suggestionId;
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
            'name'     => 'suggestion_id',
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

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $this->SuggestionId = !empty($array['suggestion_id']) ? $array['suggestion_id'] : null;
        $this->UserId = !empty($array['user_id']) ? $array['user_id'] : null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'suggestion_id' => $this->SuggestionId,
            'user_id' => $this->UserId,
        ];
    }
}
