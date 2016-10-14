<?php
class SabaiFramework_Token
{
    /**
     * @var string
     */
    private $_salt;
    /**
     * @var int
     */
    private $_expires;
    /**
     * @var string
     */
    private $_value;
    /**
     * @var bool
     */
    private static $_seeded = false;

    /**
     * Constructor
     *
     * @param string $salt
     * @param int $expires
     * @return SabaiFramework_Token
     */
    private function __construct($salt, $expires = null)
    {
        $this->_salt = $salt;
        $this->_expires = $expires;
    }

    /**
     * Creates a new SabaiFramework_Token object
     *
     * @return SabaiFramework_Token
     * @param string $tokenId
     * @param int $lifetime
     * @param bool $reobtainable
     */
    public static function create($tokenId, $lifetime = 1800, $reobtainable = false)
    {
        if (!self::$_seeded) {
            mt_srand();
            self::$_seeded = true;
        }

        if (!isset($_SESSION[__CLASS__])) {
            $_SESSION[__CLASS__] = array();
        }

        if (!$reobtainable
            || !isset($_SESSION[__CLASS__][$tokenId])
            || $_SESSION[__CLASS__][$tokenId][1] < time() // already expired
        ) {
            // Clear previous token if any
            unset($_SESSION[__CLASS__][$tokenId]);
            // No more than 10 tokens may be stored in the session
            if (count($_SESSION[__CLASS__]) >= 10) {
                $_SESSION[__CLASS__] = array_slice($_SESSION[__CLASS__], -9, 9, true);
            }
            // Generate token and save into session
            $salt = function_exists('hash') ? hash('md5', uniqid(mt_rand(), true)) : md5(uniqid(mt_rand(), true));
            $_SESSION[__CLASS__][$tokenId] = array($salt, time() + $lifetime);
        }

        return new self($_SESSION[__CLASS__][$tokenId][0], $_SESSION[__CLASS__][$tokenId][1]);
    }

    /**
     * Validates a token
     *
     * @param string $value;
     * @param string $tokenId
     * @param bool $reuseable
     * @return bool
     */
    public static function validate($value, $tokenId, $reuseable = false)
    {
        if (empty($_SESSION[__CLASS__][$tokenId])
            || (!$token_arr = $_SESSION[__CLASS__][$tokenId])
            || $token_arr[1] < time() // expired
        ) {
            unset($_SESSION[__CLASS__][$tokenId]);

            return false;
        }

        $token = new self($token_arr[0], $token_arr[1]);

        if ($token->getValue() != $value) {
            unset($_SESSION[__CLASS__][$tokenId]);

            return false;
        }

        if (!$reuseable) unset($_SESSION[__CLASS__][$tokenId]);

        return true;
    }

    /**
     * Returns the value of this token
     *
     * @return string
     */
    public function getValue()
    {
        if (!isset($this->_value)) {
            if (function_exists('hash')) {
                $this->_value = hash('sha1', $this->_salt . $this->_expires);
            } else {
                $this->_value = sha1($this->_salt . $this->_expires);
            }
        }

        return $this->_value;
    }

    /**
     * Returns token salt value
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->_salt;
    }

    /**
     * Returns the tiemstamp at which token expires
     *
     * @return int
     */
    public function getExpires()
    {
        return $this->_expires;
    }
}