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

class Database implements ArraySerializableInterface, InputFilterAwareInterface
{
	protected $inputFilter;
	protected $driver;
	protected $database;
	protected $username;
	protected $password;
	protected $hostname;
	protected $port;
	protected $charset;

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
            throw new \Exception('Invalid Database property');
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new \Exception('Invalid Database property');
        }
        return $this->$method();
    }

    public function setDriver($driver)
    {
    	$this->driver = (is_null($driver)) ? null : (string) $driver;
    	return $this;
    }

    public function getDriver()
    {
    	return $this->driver;
    }

    public function setDatabase($database)
    {
        $this->database = (is_null($database)) ? null : (string) $database;
        return $this;
    }

    public function getDatabase()
    {
        return $this->database;
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

    public function setHostname($hostname)
    {
        $this->hostname = (is_null($hostname)) ? null : (string) $hostname;
        return $this;
    }

    public function getHostname()
    {
        return $this->hostname;
    }

    public function setPort($port)
    {
        $this->port = (is_null($port)) ? null : (int) $port;
        return $this;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setCharset($charset)
    {
        $this->charset = (is_null($charset)) ? null : (string) $charset;
        return $this;
    }

    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
    	$this->setDriver((!empty($array['driver'])) ? $array['driver'] : null);
    	$this->setDatabase((!empty($array['database'])) ? $array['database'] : null);
    	$this->setUsername((!empty($array['username'])) ? $array['username'] : null);
    	$this->setPassword((!empty($array['password'])) ? $array['password'] : null);
    	$this->setHostname((!empty($array['hostname'])) ? $array['hostname'] : null);
    	$this->setPort((!empty($array['port'])) ? $array['port'] : null);
    	$this->setCharset((!empty($array['charset'])) ? $array['charset'] : null);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
    	return [
            'driver' => $this->Driver,
    	    'database' => $this->Database,
    	    'username' => $this->Username,
    	    'password' => $this->Password,
    	    'hostname' => $this->Hostname,
    	    'port' => $this->Port,
    	    'charset' => $this->Charset,
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

            $inputFilter->add([
                'name'     => 'database',
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
                            'min'      => 1,
                        ],
                    ],
                ],
            ]);

            $inputFilter->add([
                'name'     => 'username',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
            ]);

            $inputFilter->add([
                'name'     => 'password',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
            ]);

            $inputFilter->add([
                'name'     => 'hostname',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
            ]);

            $inputFilter->add([
                'name'     => 'port',
                'required' => false,
            ]);

            $inputFilter->add([
                'name'     => 'charset',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
            ]);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
