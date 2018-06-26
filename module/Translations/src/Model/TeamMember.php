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

class TeamMember extends AbstractDbTableEntry implements
    ArraySerializableInterface,
    InputFilterAwareInterface
{
    /**
     * @var null|int
     */
    private $userId;

    /**
     * @var null|int
     */
    private $teamId;

    /**
     * @var null|string
     * Joined field
     */
    private $username;

    /**
     * @var null|string
     * Joined field
     */
    private $email;

    /**
     * @var null|string
     * Joined field
     */
    private $displayName;

    /**
     * @var null|string
     * Joined field
     */
    private $teamName;

    /**
     * @return null|int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param null|int $userId
     */
    public function setUserId($userId)
    {
        if (! is_null($userId)) {
            $userId = (int) $userId;
        }
        $this->userId = $userId;
    }

    /**
     * @return null|int
     */
    public function getTeamId()
    {
        return $this->teamId;
    }

    /**
     * @param null|int $teamId
     */
    public function setTeamId($teamId)
    {
        if (! is_null($teamId)) {
            $teamId = (int) $teamId;
        }
        $this->teamId = $teamId;
    }

    /**
     * @return null|string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param null|string $username
     */
    public function setUsername($username)
    {
        if (! is_null($username)) {
            $username = (string) $username;
        }
        $this->username = $username;
    }

    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     */
    public function setEmail($email)
    {
        if (! is_null($email)) {
            $email = (string) $email;
        }
        $this->email = $email;
    }

    /**
     * @return null|string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param null|string $displayName
     */
    public function setDisplayName($displayName)
    {
        if (! is_null($displayName)) {
            $displayName = (string) $displayName;
        }
        $this->displayName = $displayName;
    }

    /**
     * @return null|string
     */
    public function getTeamName()
    {
        return $this->teamName;
    }

    /**
     * @param null|string $teamName
     */
    public function setTeamName($teamName)
    {
        if (! is_null($teamName)) {
            $teamName = (string) $teamName;
        }
        $this->teamName = $teamName;
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
    public function exchangeArray(array $array)
    {
        $this->setUserId(isset($array['user_id']) ? $array['user_id'] : null);
        $this->setTeamId(isset($array['team_id']) ? $array['team_id'] : null);
        $this->setUsername(isset($array['username']) ? $array['username'] : null);
        $this->setEmail(isset($array['email']) ? $array['email'] : null);
        $this->setDisplayName(isset($array['display_name']) ? $array['display_name'] : null);
        $this->setTeamName(isset($array['team_name']) ? $array['team_name'] : null);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'user_id' => $this->getUserId(),
            'team_id' => $this->getTeamId(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'display_name' => $this->getDisplayName(),
            'team_name' => $this->getTeamName(),
        ];
    }
}
