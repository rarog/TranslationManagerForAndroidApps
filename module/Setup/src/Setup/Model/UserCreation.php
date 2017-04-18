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

class UserCreation implements InputFilterAwareInterface
{
	protected $inputFilter;
	protected $username;
	protected $password;
	protected $passwordVerify;

    public function __construct(array $data = null)
    {
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

    public function exchangeArray($data)
    {
    	$this->setUsername((!empty($data['username'])) ? $data['username'] : null);
    	$this->setPassword((!empty($data['password'])) ? $data['password'] : null);
    	$this->setPasswordVerify((!empty($data['passwordVerify'])) ? $data['passwordVerify'] : null);
    }

    public function getArrayCopy()
    {
    	return array(
    	    'username'       => $this->Username,
    	    'password'       => $this->Password,
    	    'passwordVerify' => $this->PasswordVerify,
        );
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception('Not used');
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add([
                'name'     => 'username',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max' => 255,
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
                'name'       => 'password2',
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
