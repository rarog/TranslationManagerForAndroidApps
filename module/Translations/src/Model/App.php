<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use DomainException;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Validator\StringLength;

class App implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var null|int
     */
    private $id;

    /**
     * @var null|int
     */
    private $teamId;

    /**
     * @var null|string
     */
    private $name;

    /**
     * @var null|string
     */
    private $gitRepository;

    /**
     * @var null|string
     */
    private $pathToResFolder;

    /**
     * @var null|int
     * Joined field
     */
    private $resourceCount;

    /**
     * @var null|int
     * Joined field
     */
    private $resourceFileCount;

    /**
     * @var InputFilter
     */
    private $inputFilter;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = null)
    {
        if ($data) {
            $this->exchangeArray($data);
        }
    }

    /**
     * @param unknown $name
     * @throws \Exception
     * @return unknown
     */
    public function __get($name)
    {
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new \Exception('Invalid property');
        }
        return $this->$method();
    }

    /**
     * @param unknown $name
     * @param unknown $value
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (!method_exists($this, $method)) {
            throw new \Exception('Invalid property');
        }
        $this->$method($value);
    }

    /**
     * @return null|int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param null|int $id
     */
    public function setId($id) {
        if (!is_null($id)) {
            $id = (int) $id;
        }
        $this->id = $id;
    }

    /**
     * @return null|int
     */
    public function getTeamId() {
        return $this->teamId;
    }

    /**
     * @param null|int $teamId
     */
    public function setTeamId($teamId) {
        if (!is_null($teamId)) {
            $teamId = (int) $teamId;
        }
        $this->teamId = $teamId;
    }

    /**
     * @return null|string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName($name) {
        if (!is_null($name)) {
            $name = (string) $name;
        }
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getGitRepository() {
        return $this->gitRepository;
    }

    /**
     * @param null|string $gitRepository
     */
    public function setGitRepository($gitRepository) {
        if (!is_null($gitRepository)) {
            $gitRepository = (string) $gitRepository;
        }
        $this->gitRepository = $gitRepository;
    }

    /**
     * @return null|string
     */
    public function getPathToResFolder() {
        return $this->pathToResFolder;
    }

    /**
     * @return null|int
     */
    public function getResourceCount() {
        return $this->resourceCount;
    }

    /**
     * @param null|int $resourceCount
     */
    public function setResourceCount($resourceCount) {
        if (!is_null($resourceCount)) {
            $resourceCount = (int) $resourceCount;
        }
        $this->resourceCount = $resourceCount;
    }

    /**
     * @return null|int
     */
    public function getResourceFileCount() {
        return $this->resourceFileCount;
    }

    /**
     * @param null|int $resourceFileCount
     */
    public function setResourceFileCount($resourceFileCount) {
        if (!is_null($resourceFileCount)) {
            $resourceFileCount = (int) $resourceFileCount;
        }
        $this->resourceFileCount = $resourceFileCount;
    }

    /**
     * @param null|string $pathToResFolder
     */
    public function setPathToResFolder($pathToResFolder) {
        if (!is_null($pathToResFolder)) {
            $pathToResFolder = (string) $pathToResFolder;
        }
        $this->pathToResFolder = $pathToResFolder;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\InputFilter\InputFilterAwareInterface::setInputFilter()
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf(
            '%s does not allow injection of an alternate input filter',
            __CLASS__
        ));
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
            'name'     => 'team_id',
            'required' => true,
            'filters'  => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name'     => 'name',
            'required' => true,
            'filters'  => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name'    => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 255,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'git_repository',
            'required'   => false,
            'filters'    => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name'    => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min'      => 0,
                        'max'      => 4096,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'path_to_res_folder',
            'required'   => false,
            'filters'    => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name'    => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min'      => 0,
                        'max'      => 4096,
                    ],
                ],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::exchangeArray()
     */
    public function exchangeArray(array $data)
    {
        $this->Id                = !empty($data['id']) ? $data['id'] : null;
        $this->TeamId            = !empty($data['team_id']) ? $data['team_id'] : null;
        $this->Name              = !empty($data['name']) ? $data['name'] : null;
        $this->GitRepository     = !empty($data['git_repository']) ? $data['git_repository'] : null;
        $this->PathToResFolder   = !empty($data['path_to_res_folder']) ? $data['path_to_res_folder'] : null;
        $this->ResourceCount     = !empty($data['resource_count']) ? $data['resource_count'] : null;
        $this->ResourceFileCount = !empty($data['resource_file_count']) ? $data['resource_file_count'] : null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'id'                  => $this->Id,
            'team_id'             => $this->TeamId,
            'name'                => $this->Name,
            'git_repository'      => $this->GitRepository,
            'path_to_res_folder'  => $this->PathToResFolder,
            'resource_count'      => $this->ResourceCount,
            'resource_file_count' => $this->ResourceFileCount,
        ];
    }
}
