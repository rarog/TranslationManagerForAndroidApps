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

namespace ApplicationTest\Model;

use Application\Model\User;
use PHPUnit\Framework\TestCase;
use Zend\InputFilter\InputFilterInterface;
use ReflectionClass;

class UserTest extends TestCase
{
    private $exampleArrayData = [
        'user_id' => 12,
        'username' => 'auser',
        'email' => 'someboday@localhost.localdomain',
        'display_name' => 'A user',
        'password' => 123456789,
        'password_verify' => 123456789,
    ];

    public function testConstructor()
    {
        $user = new User();

        $this->assertNull($user->getUserId());
        $this->assertNull($user->getUsername());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getDisplayName());
        $this->assertNull($user->getPassword());
        $this->assertNull($user->getPasswordVerify());

        $user = new User($this->exampleArrayData);

        $this->assertEquals(
            $this->exampleArrayData['user_id'],
            $user->getUserId()
        );
        $this->assertEquals(
            $this->exampleArrayData['username'],
            $user->getUsername()
        );
        $this->assertEquals(
            $this->exampleArrayData['email'],
            $user->getEmail()
        );
        $this->assertEquals(
            $this->exampleArrayData['display_name'],
            $user->getDisplayName()
        );
        $this->assertEquals(
            $this->exampleArrayData['password'],
            $user->getPassword()
        );
        $this->assertEquals(
            $this->exampleArrayData['password_verify'],
            $user->getPasswordVerify()
        );
    }

    public function testSetterAndGetters()
    {
        $user = new User();
        $user->setUserId($this->exampleArrayData['user_id']);
        $user->setUsername($this->exampleArrayData['username']);
        $user->setEmail($this->exampleArrayData['email']);
        $user->setDisplayName($this->exampleArrayData['display_name']);
        $user->setPassword($this->exampleArrayData['password']);
        $user->setPasswordVerify($this->exampleArrayData['password_verify']);

        $this->assertEquals(
            $this->exampleArrayData['user_id'],
            $user->getUserId()
        );
        $this->assertEquals(
            $this->exampleArrayData['username'],
            $user->getUsername()
        );
        $this->assertEquals(
            $this->exampleArrayData['email'],
            $user->getEmail()
        );
        $this->assertEquals(
            $this->exampleArrayData['display_name'],
            $user->getDisplayName()
        );
        $this->assertEquals(
            $this->exampleArrayData['password'],
            $user->getPassword()
            );
        $this->assertEquals(
            $this->exampleArrayData['password_verify'],
            $user->getPasswordVerify()
        );
    }

    public function testGetInputFilter()
    {
        $user = new User();

        $reflection = new ReflectionClass(User::class);
        $inputFilterProperty = $reflection->getProperty('inputFilter');
        $inputFilterProperty->setAccessible(true);

        $this->assertNull($inputFilterProperty->getValue($user));

        $inputFilter = $user->getInputFilter();
        $this->assertInstanceOf(InputFilterInterface::class, $inputFilter);
        $this->assertSame($inputFilterProperty->getValue($user), $inputFilter);
        $this->assertSame($inputFilter, $user->getInputFilter());
    }

    public function testArraySerializableImplementations()
    {
        $user = new User();
        $user->exchangeArray($this->exampleArrayData);

        $this->assertEquals($this->exampleArrayData, $user->getArrayCopy());
    }
}
