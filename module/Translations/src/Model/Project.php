<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

class Project
{
    public $id;
    public $name;
    public $gitRepository;
    public $pathToResFolder;

    public function exchangeArray(array $data)
    {
        $this->id              = !empty($data['id']) ? $data['id'] : null;
        $this->name            = !empty($data['name']) ? $data['name'] : null;
        $this->gitRepository   = !empty($data['git_repository']) ? $data['git_repository'] : null;
        $this->pathToResFolder = !empty($data['path_to_res_folder']) ? $data['path_to_res_folder'] : null;
    }
}
