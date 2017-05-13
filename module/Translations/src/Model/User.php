<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

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

class User implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $displayName;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $passwordVerify;

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
            'name'       => 'username',
            'required'   => true,
            'filters'    => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
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
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
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
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
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
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
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
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
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
        $this->userId         = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->username       = !empty($data['username']) ? $data['username'] : null;
        $this->email          = !empty($data['email']) ? $data['email'] : null;
        $this->displayName    = !empty($data['display_name']) ? $data['display_name'] : null;
        $this->password       = !empty($data['password']) ? $data['password'] : null;
        $this->passwordVerify = !empty($data['password_verify']) ? $data['password_verify'] : null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'user_id'         => $this->userId,
            'username'        => $this->username,
            'email'           => $this->email,
            'display_name'    => $this->displayName,
            'password'        => $this->password,
            'password_verify' => $this->passwordVerify,
        ];
    }
}
