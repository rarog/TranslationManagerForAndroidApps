<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class DataTablesInitHelper extends AbstractHelper
{
    private function processArray($array)
    {
        $resultArray = array();

        if (is_array($array)) {
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

    public function __invoke($tablesToInit)
    {
        $tablesToInit = $this->processArray($tablesToInit);

        if (!empty($tablesToInit)){
            $this->view->headScript()->appendFile($this->view->basePath('/js/jquery.dataTables.min.js'));
            $this->view->headScript()->appendFile($this->view->basePath('/js/dataTables.bootstrap.min.js'));
            $this->view->headLink()->prependStylesheet($this->view->basePath('/css/dataTables.bootstrap.min.css'));

            $initConf = '
"language": {
    "url": "' . $this->view->basePath('/js/dataTables.' . $this->view->plugin('translate')->getTranslator()->getFallbackLocale() . '.json') . '",
},
"lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "' . $this->view->translate('All') . '"]],';

            $initTable = '';
            foreach ($tablesToInit as $table) {
                $initTable .= '$(document).ready(function() {
$("' . $table['table'] . '").dataTable({' . $initConf;
                if (array_key_exists('columnDefs', $table)) {
                    $initTable .= '
"columnDefs": ' . $table['columnDefs'] . ',';
                }
                $initTable .= ' });
} );';
            }

            $this->view->headScript()->appendScript($initTable);
        }
    }
}
