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

namespace Common\Model;

use RuntimeException;

class SettingTable extends AbstractDbTable
{

    /**
     * Get entry
     *
     * @param int $id
     * @throws RuntimeException
     * @return \Common\Model\Setting
     */
    public function getSetting(int $id)
    {
        $resultSet = $this->fetchAll([
            'id' => $id,
        ]);
        $row = $resultSet->current();
        if (! $row) {
            throw new RuntimeException(sprintf('Could not find row with identifier %d', $id));
        }

        return $row;
    }

    /**
     * Get entry by path
     *
     * @param string $path
     * @throws RuntimeException
     * @return \Common\Model\Setting
     */
    public function getSettingByPath(string $path)
    {
        $resultSet = $this->fetchAll([
            'path' => $path,
        ]);
        $row = $resultSet->current();
        if (! $row) {
            throw new RuntimeException(sprintf('Could not find row with path %s', $path));
        }

        return $row;
    }

    /**
     * Setting save function
     *
     * @param Setting $setting
     * @throws RuntimeException
     */
    public function saveSetting(Setting $setting)
    {
        $data = [
            'path' => $setting->getPath(),
            'value' => is_null($setting->getValue()) ? null : (string) $setting->getValue(),
        ];

        $id = (int) $setting->getId();

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $setting->setId($this->tableGateway->getLastInsertValue());
            return;
        }

        try {
            if ($this->getSetting($id)) {
                $this->tableGateway->update($data, ['id' => $id]);
            }
        } catch (RuntimeException $e) {
            throw new RuntimeException(sprintf(
                'Cannot update row with identifier %d; does not exist',
                $id
            ));
        }
    }

    /**
     * Setting delete function
     *
     * @param int $id
     */
    public function deleteSetting(int $id)
    {
        $this->tableGateway->delete([
            'id' => $id,
        ]);
    }
}
