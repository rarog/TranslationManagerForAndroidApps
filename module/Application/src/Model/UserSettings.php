<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application\Model;

use DomainException;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\Filter\ToNull;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Validator\StringLength;

class UserSettings implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var null|int
     */
    private $userId;

    /**
     * @var null|string
     */
    private $locale;

    /**
     * @var null|int
     */
    private $teamId;

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
        $this->userId= $userId;
    }

    /**
     * @return null|string
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * @param null|string $locale
     */
    public function setLocale($locale) {
        if (!is_null($locale)) {
            $locale = (string) $locale;
        }
        $this->locale = $locale;
    }

    /**
     * @return null|int
     */
    public function getTeamId() {
        return $this->teamId;
    }

    /**
     * @param null|int $teamId
     */
    public function setTeamId($teamId) {
        if (!is_null($teamId)) {
            $teamId = (int) $teamId;
        }
        $this->teamId = $teamId;
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
            'name'     => 'user_id',
            'required' => true,
            'filters'  => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'locale',
            'required' => false,
            'filters'  => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name'    => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 5,
                        'max' => 5,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'team_id',
            'required' => false,
            'filters'  => [
                [
                    'name'    => ToNull::class,
                    'options' => ['type' => ToNull::TYPE_INTEGER],
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
    public function exchangeArray(array $data)
    {
        $this->userId = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->locale = !empty($data['locale']) ? $data['locale'] : null;
        $this->teamId = !empty($data['team_id']) ? $data['team_id'] : null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'user_id' => $this->userId,
            'locale'  => $this->locale,
            'team_id' => $this->teamId,
        ];
    }
}