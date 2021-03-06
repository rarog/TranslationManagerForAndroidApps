<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Setup\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\ArraySerializableInterface;
use ZfcUser\Options\ModuleOptions as ZUModuleOptions;

class UserCreation implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var ZUModuleOptions
     */
    protected $options;

	protected $inputFilter;
	protected $username;
	protected $email;
	protected $displayName;
	protected $password;
	protected $passwordVerify;

	public function __construct(ZUModuleOptions $options, array $data = null)
    {
	    $this->options = $options;
        if (is_array($data)) {
            $this->exchangeArray($data);
        }
    }

    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new \Exception('Invalid UserCreation property');
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new \Exception('Invalid UserCreation property');
        }
        return $this->$method();
    }

    public function setUsername($username)
    {
        $this->username = (is_null($username)) ? null : (string) $username;
        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setEmail($email)
    {
        $this->email= (is_null($email)) ? null : (string) $email;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setDisplayName($displayName)
    {
        $this->displayName= (is_null($displayName)) ? null : (string) $displayName;
        return $this;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function setPassword($password)
    {
        $this->password = (is_null($password)) ? null : (string) $password;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPasswordVerify($passwordVerify)
    {
        $this->passwordVerify = (is_null($passwordVerify)) ? null : (string) $passwordVerify;
        return $this;
    }

    public function getPasswordVerify()
    {
        return $this->passwordVerify;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $this->setUsername((!empty($array['username'])) ? $array['username'] : null);
        $this->setEmail((!empty($array['email'])) ? $array['email'] : null);
        $this->setDisplayName((!empty($array['display_name'])) ? $array['display_name'] : null);
    	$this->setPassword((!empty($array['password'])) ? $array['password'] : null);
    	$this->setPasswordVerify((!empty($array['passwordVerify'])) ? $array['passwordVerify'] : null);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
    	return [
    	    'username' => $this->Username,
    	    'email' => $this->Email,
    	    'display_name' => $this->DisplayName,
    	    'password' => $this->Password,
    	    'passwordVerify' => $this->PasswordVerify,
        ];
    }

    /**
     * {@inheritDoc}
     * @see \Zend\InputFilter\InputFilterAwareInterface::setInputFilter()
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception('Not used');
    }

    /**
     * {@inheritDoc}
     * @see \Zend\InputFilter\InputFilterAwareInterface::getInputFilter()
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            if ($this->options->getEnableUsername()) {
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
            }

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

            if ($this->options->getEnableDisplayName()) {
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
            }

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
        }

        return $this->inputFilter;
    }
}
