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
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Validator\StringLength;

class User implements ArraySerializableInterface, InputFilterAwareInterface
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
     * @param mixed $name
     * @throws \Exception
     * @return mixed
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
     * @param mixed $name
     * @param mixed $value
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
        $this->userId = $userId;
    }

    /**
     * @return null|string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @param null|string $username
     */
    public function setUsername($username) {
        if (!is_null($username)) {
            $username = (string) $username;
        }
        $this->username = $username;
    }

    /**
     * @return null|string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param null|string $email
     */
    public function setEmail($email) {
        if (!is_null($email)) {
            $email = (string) $email;
        }
        $this->email = $email;
    }

    /**
     * @return null|string
     */
    public function getDisplayName() {
        return $this->displayName;
    }

    /**
     * @param null|string $displayName
     */
    public function setDisplayName($displayName) {
        if (!is_null($displayName)) {
            $displayName = (string) $displayName;
        }
        $this->displayName = $displayName;
    }

    /**
     * @return null|string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param null|string $password
     */
    public function setPassword($password) {
        if (!is_null($password)) {
            $password = (string) $password;
        }
        $this->password = $password;
    }

    /**
     * @return null|string
     */
    public function getPasswordVerify() {
        return $this->passwordVerify;
    }

    /**
     * @param null|string $passwordVerify
     */
    public function setPasswordVerify($passwordVerify) {
        if (!is_null($passwordVerify)) {
            $passwordVerify = (string) $passwordVerify;
        }
        $this->passwordVerify = $passwordVerify;
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
            'name'       => 'username',
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
                        'min'      => 3,
                        'max'      => 255,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'email',
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
                        'max'      => 255,
                    ],
                ],
                [
                    'name' => 'EmailAddress'
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'display_name',
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
                        'min'      => 3,
                        'max'      => 50,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'password',
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
                        'min'      => 6,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'passwordVerify',
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
                        'min'      => 6,
                    ],
                ],
                [
                    'name'    => 'Identical',
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
    public function exchangeArray(array $data)
    {
        $this->UserId         = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->Username       = !empty($data['username']) ? $data['username'] : null;
        $this->Email          = !empty($data['email']) ? $data['email'] : null;
        $this->DisplayName    = !empty($data['display_name']) ? $data['display_name'] : null;
        $this->Password       = !empty($data['password']) ? $data['password'] : null;
        $this->PasswordVerify = !empty($data['password_verify']) ? $data['password_verify'] : null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'user_id'         => $this->UserId,
            'username'        => $this->Username,
            'email'           => $this->Email,
            'display_name'    => $this->DisplayName,
            'password'        => $this->Password,
            'password_verify' => $this->PasswordVerify,
        ];
    }
}
