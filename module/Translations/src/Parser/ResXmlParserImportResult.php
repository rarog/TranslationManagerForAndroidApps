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

namespace Translations\Parser;

class ResXmlParserImportResult
{
    /**
     * @var integer
     */
    public $entriesProcessed = 0;

    /**
     * @var integer
     */
    public $entriesUpdated = 0;

    /**
     * @var integer
     */
    public $entriesSkippedExistOnlyInDb = 0;

    /**
     * @var integer
     */
    public $entriesSkippedNotInDefault = 0;
}
