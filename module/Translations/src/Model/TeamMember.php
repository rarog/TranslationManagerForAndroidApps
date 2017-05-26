<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use DomainException;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\ArraySerializableInterface;

class TeamMember implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var int
     */
    public $userId;

    /**
     * @var int
     */
    public $teamId;

    /**
     * @var string
     * Joined field
     */
    public $username;

    /**
     * @var string
     * Joined field
     */
    public $email;

    /**
     * @var string
     * Joined field
     */
    public $displayName;

    /**
     * @var string
     * Joined field
     */
    public $teamName;

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
            'name'     => 'team_id',
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
        $this->userId      = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->teamId      = !empty($data['team_id']) ? $data['team_id'] : null;
        $this->username    = !empty($data['username']) ? $data['username'] : null;
        $this->email       = !empty($data['email']) ? $data['email'] : null;
        $this->displayName = !empty($data['display_name']) ? $data['display_name'] : null;
        $this->teamName    = !empty($data['team_name']) ? $data['team_name'] : null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'user_id'      => $this->userId,
            'team_id'      => $this->teamId,
            'username'     => $this->username,
            'email'        => $this->email,
            'display_name' => $this->displayName,
            'team_name'    => $this->teamName,
        ];
    }
}
