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

namespace Translations\Model;

use Common\Model\AbstractDbTableEntry;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\Stdlib\ArraySerializableInterface;

class EntryCommon extends AbstractDbTableEntry implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var null|int
     */
    private $id;

    /**
     * @var null|int
     */
    private $appResourceId;

    /**
     * @var null|int
     */
    private $resourceFileEntryId;

    /**
     * @var null|int
     */
    private $lastChange;

    /**
     * @var null|int
     */
    private $notificationStatus;

    /**
     * @var InputFilter
     */
    private $inputFilter;

    /**
     * @return null|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null|int $id
     */
    public function setId($id)
    {
        if (! is_null($id)) {
            $id = (int) $id;
        }
        $this->id = $id;
    }

    /**
     * @return null|int
     */
    public function getAppResourceId()
    {
        return $this->appResourceId;
    }

    /**
     * @param null|int $appResourceId
     */
    public function setAppResourceId($appResourceId)
    {
        if (! is_null($appResourceId)) {
            $appResourceId = (int) $appResourceId;
        }
        $this->appResourceId = $appResourceId;
    }

    /**
     * @return null|int
     */
    public function getResourceFileEntryId()
    {
        return $this->resourceFileEntryId;
    }

    /**
     * @param null|int $resourceFileEntryId
     */
    public function setResourceFileEntryId($resourceFileEntryId)
    {
        if (! is_null($resourceFileEntryId)) {
            $resourceFileEntryId = (int) $resourceFileEntryId;
        }
        $this->resourceFileEntryId = $resourceFileEntryId;
    }

    /**
     * @return null|int
     */
    public function getLastChange()
    {
        return $this->lastChange;
    }

    /**
     * @param null|int $lastChange
     */
    public function setLastChange($lastChange)
    {
        if (! is_null($lastChange)) {
            $lastChange = (int) $lastChange;
        }
        $this->lastChange = $lastChange;
    }

    /**
     * @return null|int
     */
    public function getNotificationStatus()
    {
        return $this->notificationStatus;
    }

    /**
     * @param null|int $notificationStatus
     */
    public function setNotificationStatus($notificationStatus)
    {
        if (! is_null($notificationStatus)) {
            $notificationStatus = (int) $notificationStatus;
        }
        $this->notificationStatus = $notificationStatus;
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
            'name'     => 'id',
            'required' => true,
            'filters'  => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'app_resource_id',
            'required' => true,
            'filters'  => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'resource_file_entry_id',
            'required' => true,
            'filters'  => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'last_change',
            'required' => true,
            'filters'  => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'notification_status',
            'required' => true,
            'filters'  => [
                ['name' => ToInt::class],
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
        $this->setId(isset($array['id']) ? $array['id'] : null);
        $this->setAppResourceId(isset($array['app_resource_id']) ? $array['app_resource_id'] : null);
        $this->setResourceFileEntryId(
            isset($array['resource_file_entry_id']) ? $array['resource_file_entry_id'] : null
        );
        $this->setLastChange(isset($array['last_change']) ? $array['last_change'] : null);
        $this->setNotificationStatus(isset($array['notification_status']) ? $array['notification_status'] : null);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'id' => $this->getId(),
            'app_resource_id' => $this->getAppResourceId(),
            'resource_file_entry_id' => $this->getResourceFileEntryId(),
            'last_change' => $this->getLastChange(),
            'notification_status' => $this->getNotificationStatus()
        ];
    }
}
