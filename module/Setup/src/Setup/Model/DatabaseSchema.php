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

class DatabaseSchema implements InputFilterAwareInterface
{
    protected $inputFilter;
    protected $output;

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
            throw new \Exception('Invalid Step1 property');
        }
        $this->$method($value);
    }
    
    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new \Exception('Invalid Step1 property');
        }
        return $this->$method();
    }
    
    public function setOutput($output)
    {
        $this->output = (is_null($output)) ? null : (string) $output;
        return $this;
    }
    
    public function getOutput()
    {
        return $this->output;
    }
    
    public function exchangeArray($data)
    {
        $this->setOutput((!empty($data['output'])) ? $data['output'] : null);
    }
    
    public function getArrayCopy()
    {
        return array(
            'output' => $this->Output,
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
    
            $this->inputFilter = $inputFilter;
        }
    
        return $this->inputFilter;
    }
}
