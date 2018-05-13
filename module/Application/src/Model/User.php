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

namespace Application\Model;

use Common\Model\AbstractDbTableEntry;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Validator\StringLength;

class User extends AbstractDbTableEntry implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $displayName;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $passwordVerify;

    /**
     * @var InputFilter
     */
    private $inputFilter;

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
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param null|string $password
     */
    public function setPassword($password)
    {
        if (! is_null($password)) {
            $password = (string) $password;
        }
        $this->password = $password;
    }

    /**
     * @return null|string
     */
    public function getPasswordVerify()
    {
        return $this->passwordVerify;
    }

    /**
     * @param null|string $passwordVerify
     */
    public function setPasswordVerify($passwordVerify)
    {
        if (! is_null($passwordVerify)) {
            $passwordVerify = (string) $passwordVerify;
        }
        $this->passwordVerify = $passwordVerify;
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
            'name' => 'username',
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
                        'min' => 3,
                        'max' => 255,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'email',
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
                [
                    'name' => 'EmailAddress'
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'display_name',
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
                        'min' => 3,
                        'max' => 50,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'password',
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
                        'min' => 6,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'passwordVerify',
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
                        'min' => 6,
                    ],
                ],
                [
                    'name' => 'Identical',
                    'options' => [
                        'token' => 'password',
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
        $this->setUserId(isset($array['user_id']) ? $array['user_id'] : null);
        $this->setUsername(isset($array['username']) ? $array['username'] : null);
        $this->setEmail(isset($array['email']) ? $array['email'] : null);
        $this->setDisplayName(isset($array['display_name']) ? $array['display_name'] : null);
        $this->setPassword(isset($array['password']) ? $array['password'] : null);
        $this->setPasswordVerify(isset($array['password_verify']) ? $array['password_verify'] : null);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'user_id' => $this->getUserId(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'display_name' => $this->getDisplayName(),
            'password' => $this->getPassword(),
            'password_verify' => $this->getPasswordVerify(),
        ];
    }
}
