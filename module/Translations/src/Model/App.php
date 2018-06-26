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
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Validator\EmailAddress;
use Zend\Validator\StringLength;
use Zend\Validator\Uri;

class App extends AbstractDbTableEntry implements
    ArraySerializableInterface,
    InputFilterAwareInterface
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
    private $pathToResFolder;

    /**
     * @var null|string
     */
    private $gitRepository;

    /**
     * @var null|string
     */
    private $gitUsername;

    /**
     * @var null|string
     */
    private $gitPassword;

    /**
     * @var null|string
     */
    private $gitUser;

    /**
     * @var null|string
     */
    private $gitEmail;

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
    public function getTeamId()
    {
        return $this->teamId;
    }

    /**
     * @param null|int $teamId
     */
    public function setTeamId($teamId)
    {
        if (! is_null($teamId)) {
            $teamId = (int) $teamId;
        }
        $this->teamId = $teamId;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName($name)
    {
        if (! is_null($name)) {
            $name = (string) $name;
        }
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getPathToResFolder()
    {
        return $this->pathToResFolder;
    }

    /**
     * @param null|string $pathToResFolder
     */
    public function setPathToResFolder($pathToResFolder)
    {
        if (! is_null($pathToResFolder)) {
            $pathToResFolder = (string) $pathToResFolder;
        }
        $this->pathToResFolder = $pathToResFolder;
    }

    /**
     * @return null|string
     */
    public function getGitRepository()
    {
        return $this->gitRepository;
    }

    /**
     * @param null|string $gitRepository
     */
    public function setGitRepository($gitRepository)
    {
        if (! is_null($gitRepository)) {
            $gitRepository = (string) $gitRepository;
        }
        $this->gitRepository = $gitRepository;
    }

    /**
     * @return null|string
     */
    public function getGitUsername()
    {
        return $this->gitUsername;
    }

    /**
     * @param null|string $gitUsername
     */
    public function setGitUsername($gitUsername)
    {
        if (! is_null($gitUsername)) {
            $gitUsername = (string) $gitUsername;
        }
        $this->gitUsername = $gitUsername;
    }

    /**
     * @return null|string
     */
    public function getGitPassword()
    {
        return $this->gitPassword;
    }

    /**
     * @param null|string $gitPassword
     */
    public function setGitPassword($gitPassword)
    {
        if (! is_null($gitPassword)) {
            $gitPassword = (string) $gitPassword;
        }
        $this->gitPassword = $gitPassword;
    }

    /**
     * @return null|string
     */
    public function getGitUser()
    {
        return $this->gitUser;
    }

    /**
     * @param null|string $gitUser
     */
    public function setGitUser($gitUser)
    {
        if (! is_null($gitUser)) {
            $gitUser = (string) $gitUser;
        }
        $this->gitUser = $gitUser;
    }

    /**
     * @return null|string
     */
    public function getGitEmail()
    {
        return $this->gitEmail;
    }

    /**
     * @param null|string $gitEmail
     */
    public function setGitEmail($gitEmail)
    {
        if (! is_null($gitEmail)) {
            $gitEmail = (string) $gitEmail;
        }
        $this->gitEmail = $gitEmail;
    }

    /**
     * @return null|int
     */
    public function getResourceCount()
    {
        return $this->resourceCount;
    }

    /**
     * @param null|int $resourceCount
     */
    public function setResourceCount($resourceCount)
    {
        if (! is_null($resourceCount)) {
            $resourceCount = (int) $resourceCount;
        }
        $this->resourceCount = $resourceCount;
    }

    /**
     * @return null|int
     */
    public function getResourceFileCount()
    {
        return $this->resourceFileCount;
    }

    /**
     * @param null|int $resourceFileCount
     */
    public function setResourceFileCount($resourceFileCount)
    {
        if (! is_null($resourceFileCount)) {
            $resourceFileCount = (int) $resourceFileCount;
        }
        $this->resourceFileCount = $resourceFileCount;
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
                [
                    'name'    => Uri::class,
                    'options' => [
                        'allowAbsolute' => true,
                        'allowRelative' => false,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'git_username',
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
                        'max'      => 255,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'git_password',
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
                        'max'      => 255,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'git_user',
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
                        'max'      => 255,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name'       => 'git_email',
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
                        'max'      => 255,
                    ],
                ],
                [
                    'name' => EmailAddress::class
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
    public function exchangeArray(array $array)
    {
        $this->setId(isset($array['id']) ? $array['id'] : null);
        $this->setTeamId(isset($array['team_id']) ? $array['team_id'] : null);
        $this->setName(isset($array['name']) ? $array['name'] : null);
        $this->setPathToResFolder(isset($array['path_to_res_folder']) ? $array['path_to_res_folder'] : null);
        $this->setGitRepository(isset($array['git_repository']) ? $array['git_repository'] : null);
        $this->setGitUsername(isset($array['git_username']) ? $array['git_username'] : null);
        $this->setGitPassword(isset($array['git_password']) ? $array['git_password'] : null);
        $this->setGitUser(isset($array['git_user']) ? $array['git_user'] : null);
        $this->setGitEmail(isset($array['git_email']) ? $array['git_email'] : null);
        $this->setResourceCount(isset($array['resource_count']) ? $array['resource_count'] : null);
        $this->setResourceFileCount(isset($array['resource_file_count']) ? $array['resource_file_count'] : null);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        return [
            'id' => $this->getId(),
            'team_id' => $this->getTeamId(),
            'name' => $this->getName(),
            'path_to_res_folder' => $this->getPathToResFolder(),
            'git_repository' => $this->getGitRepository(),
            'git_username' => $this->getGitUsername(),
            'git_password' => $this->getGitPassword(),
            'git_user' => $this->getGitUser(),
            'git_email' => $this->getGitEmail(),
            'resource_count' => $this->getResourceCount(),
            'resource_file_count' => $this->getResourceFileCount(),
        ];
    }
}
