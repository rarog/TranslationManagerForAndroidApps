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

class Step1 implements InputFilterAwareInterface
{
    protected $inputFilter;
    protected $setupLanguage;

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

    public function setSetupLanguage($setupLanguage)
    {
        $this->setupLanguage = (is_null($setupLanguage)) ? null : (string) $setupLanguage;
        return $this;
    }

    public function getSetupLanguage()
    {
        return $this->setupLanguage;
    }

    public function exchangeArray($data)
    {
        $this->setSetupLanguage((!empty($data['setup_language'])) ? $data['setup_language'] : null);
    }

    public function getArrayCopy()
    {
        return array(
            'setup_language' => $this->SetupLanguage,
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
