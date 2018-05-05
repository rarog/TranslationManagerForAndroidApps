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

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class DataTablesInitHelper extends AbstractHelper
{
    /**
     * Tracks if headers have already been set.
     *
     * @var bool
     */
    private $headersSet = false;

    /**
     * Prepares array for DataTables initialisation.
     *
     * @param array $array
     * @return array
     */
    private function processArray(array $array = null)
    {
        $resultArray = [];

        if (! is_null($array)) {
            if (array_key_exists('table', $array)) {
                array_push($resultArray, $array);
            } else {
                foreach ($array as $arr) {
                    if (is_array($arr) && array_key_exists('table', $arr)) {
                        array_push($resultArray, $arr);
                    }
                }
            }
        }

        return $resultArray;
    }

    /**
     * Injects JS and CSS for DataTables library.
     *
     * @param array $tablesToInit
     */
    public function __invoke(array $tablesToInit = null)
    {
        $tablesToInit = $this->processArray($tablesToInit);

        if (! empty($tablesToInit)) {
            $initConfDefault = [
                'language' => [
                    'url' => $this->view->basePath(
                        '/js/dataTables.' . $this->view->plugin('translate')
                            ->getTranslator()
                            ->getFallbackLocale() . '.json'
                    ),
                ],
                'stateSave' => true,
            ];

            $initTable = '';
            foreach ($tablesToInit as $table) {
                if (! array_key_exists('table', $table) ||
                    ! is_string($table['table']) ||
                    strlen($tableName = trim($table['table'])) === 0
                ) {
                    continue;
                }

                if (array_key_exists('initOptions', $table) && is_array($table['initOptions'])) {
                    $initConf = array_merge($initConfDefault, $table['initOptions']);
                } else {
                    $initConf = $initConfDefault;
                }

                if (array_key_exists('functionName', $table) &&
                    is_string($table['functionName']) &&
                    (strlen($functionName = trim($table['functionName'])) > 0)) {
                    $prefix = 'function ' . $functionName . '() {';
                    $suffix = '}';
                } else {
                    $prefix = '';
                    $suffix = '';
                }

                $initTable .= $prefix . '$("' . $tableName . '").dataTable(' . json_encode($initConf).');' . $suffix;
            }

            if ($initTable === '') {
                return;
            }

            if (! $this->headersSet) {
                $this->view->headScript()->appendFile($this->view->basePath('/js/jquery.dataTables.min.js'));
                $this->view->headScript()->appendFile($this->view->basePath('/js/dataTables.bootstrap.min.js'));
                $this->view->headLink()->prependStylesheet($this->view->basePath('/css/dataTables.bootstrap.min.css'));
                $this->headersSet = true;
            }
            $this->view->inlineScript()->appendScript($initTable);
        }
    }
}
