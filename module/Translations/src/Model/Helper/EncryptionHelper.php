<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model\Helper;

use Zend\Config\Config;
use Zend\Crypt\BlockCipher;
use Zend\Crypt\Key\Derivation\Pbkdf2;
use Zend\Math\Rand;

class EncryptionHelper
{
    /**
     * @var string
     */
    private $masterKey;

    /**
     * Initialising block cipher with default values.
     *
     * @return \Zend\Crypt\BlockCipher
     */
    private function getBlockCipher()
    {
        return BlockCipher::factory('openssl', ['algo' => 'aes']);
    }

	/**
	 * Constructor
	 *
	 * @param Config $config
	 */
	public function __construct(Config $config)
    {
        $security = $config->security;

        $this->masterKey = ($security->master_key) ? $security->master_key : '';
	}

	public function decrypt($text)
	{
	    $text = (string) $text;

	    $blockCipher = $this->getBlockCipher();

	    $blockCipher->setKey($this->masterKey);
	    $decryptedArray = explode(':', $blockCipher->decrypt($text), 2);

	    if (count($decryptedArray) !== 2) {
	        return false;
	    }

	    $key = hex2bin($decryptedArray[0]);
	    $blockCipher->setKey($key);
	    return $blockCipher->decrypt($decryptedArray[1]);
	}

	/**
	 * Encrypts text
	 *
	 * @param string $text
	 * @return string
	 */
	public function encrypt($text)
	{
	    $text = (string) $text;

	    $blockCipher = $this->getBlockCipher();
	    $salt = Rand::getBytes(32, true);
	    $key = Pbkdf2::calc('sha256', $this->masterKey, $salt, 10000, 32);

	    $blockCipher->setKey($key);
	    $encrypted = bin2hex($key) . ':' . $blockCipher->encrypt($text);

	    $blockCipher->setKey($this->masterKey);
	    return $blockCipher->encrypt($encrypted);
	}
}
