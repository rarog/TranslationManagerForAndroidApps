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

namespace Common\Settings;

use Common\Model\SettingTable;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\ArraySerializableInterface;
use DomainException;
use RuntimeException;

abstract class AbstractSettingsSet implements
    ArraySerializableInterface,
    InputFilterAwareInterface,
    SettingsSetInterface
{
    /**
     * @var SettingTable
     */
    protected $settingTable;

    /**
     * @var InputFilterInterface
     */
    protected $inputFilter = null;

    /**
     * @var bool
     */
    protected $loaded = false;

    /**
     * Constructor
     *
     * @param SettingTable $settingTable
     */
    public function __construct(SettingTable $settingTable)
    {
        $this->settingTable = $settingTable;
    }

    /**
     * @param string $name
     * @throws RuntimeException
     * @return mixed
     */
    public function __get(string $name)
    {
        $method = 'get' . $name;
        if (! method_exists($this, $method)) {
            throw new RuntimeException('Invalid property');
        }
        return $this->$method();
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws RuntimeException
     */
    public function __set(string $name, $value)
    {
        $method = 'set' . $name;
        if (! method_exists($this, $method)) {
            throw new RuntimeException('Invalid property');
        }
        $this->$method($value);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\InputFilter\InputFilterAwareInterface::setInputFilter()
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf(
            '%s does not allow injection of an alternate input filter.',
            __CLASS__
        ));
    }

    /**
     * {@inheritDoc}
     * @see \Zend\InputFilter\InputFilterAwareInterface::getInputFilter()
     */
    abstract public function getInputFilter();

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::exchangeArray()
     */
    abstract public function exchangeArray(array $array);

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    abstract public function getArrayCopy();

    /**
     * {@inheritDoc}
     * @see \Common\Settings\SettingsSetInterface::load()
     */
    abstract public function load();

    /**
     * {@inheritDoc}
     * @see \Common\Settings\SettingsSetInterface::save()
     */
    abstract public function save();
}
