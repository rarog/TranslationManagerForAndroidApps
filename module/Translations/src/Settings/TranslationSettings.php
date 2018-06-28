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

namespace Translations\Settings;

use Common\Model\Setting;
use Common\Settings\AbstractSettingsSet;
use Common\Settings\SettingsSetInterface;
use Zend\Filter\Boolean;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\Stdlib\ArraySerializableInterface;
use RuntimeException;

class TranslationSettings extends AbstractSettingsSet implements
    ArraySerializableInterface,
    InputFilterAwareInterface,
    SettingsSetInterface
{
    /**
     * Settings path prefix
     */
    const PREFIX = 'translations/';

    /**
     * Settings path for VATIN
     */
    const PATH_MARKAPPROVEDTRANSLATIONSGREEN = self::PREFIX . 'markapprovedtranslationsgreen';

    /**
     * @var null|Setting
     */
    private $markApprovedTranslationsGreen;

    /**
     * @return boolean
     */
    public function getMarkApprovedTranslationsGreen()
    {
        if (is_null($this->markApprovedTranslationsGreen)) {
            return false;
        }

        return (bool) $this->markApprovedTranslationsGreen->getValue();
    }

    /**
     * @param bool $markApprovedTranslationsGreen
     * @return \Translations\Settings\TranslationSettings
     */
    public function setMarkApprovedTranslationsGreen(bool $markApprovedTranslationsGreen)
    {
        if (is_null($this->markApprovedTranslationsGreen)) {
            $this->markApprovedTranslationsGreen = new Setting([
                'path' => self::PATH_MARKAPPROVEDTRANSLATIONSGREEN,
            ]);
        }

        $this->markApprovedTranslationsGreen->setValue((string)(int) $markApprovedTranslationsGreen);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Common\Settings\AbstractSettingsSet::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $this->setMarkApprovedTranslationsGreen(
            isset($array['mark_approved_translations_green']) ? $array['mark_approved_translations_green'] : null
        );
    }

    /**
     * {@inheritDoc}
     * @see \Common\Settings\AbstractSettingsSet::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'mark_approved_translations_green' => $this->getMarkApprovedTranslationsGreen(),
        ];
    }

    /**
     * {@inheritDoc}
     * @see \Common\Settings\AbstractSettingsSet::getInputFilter()
     */
    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'markapprovedtranslationsgreen',
            'required' => false,
            'filters' => [
                ['name' => Boolean::class],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    /**
     * {@inheritDoc}
     * @see \Common\Settings\AbstractSettingsSet::load()
     */
    public function load()
    {
        if ($this->loaded) {
            return;
        }

        try {
            $this->markApprovedTranslationsGreen = $this->settingTable->getSettingByPath(
                self::PATH_MARKAPPROVEDTRANSLATIONSGREEN
            );
        } catch (RuntimeException $e) {
            $this->markApprovedTranslationsGreen = null;
        }

        $this->loaded = true;
    }

    /**
     * {@inheritDoc}
     * @see \Common\Settings\AbstractSettingsSet::save()
     */
    public function save()
    {
        if (! is_null($this->markApprovedTranslationsGreen)) {
            $this->settingTable->saveSetting($this->markApprovedTranslationsGreen);
        }
    }
}
