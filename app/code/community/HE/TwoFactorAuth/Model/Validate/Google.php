<?php

require_once (Mage::getBaseDir('lib') . DS . 'GoogleAuthenticator' . DS . 'PHPGangsta' . DS . 'GoogleAuthenticator.php');

class HE_TwoFactorAuth_Model_Validate_Google extends HE_TwoFactorAuth_Model_Validate
{

    public function __construct()
    {
        $this->_shouldLog = Mage::helper('he_twofactorauth')->shouldLog();
    }

    /*
     * HOTP - counter based
     * TOTP - time based
     */
    public function getToken($username, $tokentype = "TOTP")
    {
        $token = $this->setUser($username, $tokentype);
        if ($this->_shouldLog) {
            Mage::log("token = " . var_export($token, true));
        }

        $user = Mage::getModel('admin/user')->loadByUsername($username);
        $user->setTwofactorauthToken($token);
        //$user->save(); //password gets messed up after saving?!
    }

    /*
     * abstract function in GoogleAuthenticator, needs to be defined here
     */
    function getData($username)
    {
        $user = Mage::getModel('admin/user')->loadByUsername($username);
        return $user->getTwofactorauthToken() == null ? false : $user->getTwofactorauthToken();
    }

    /*
     * abstract function in GoogleAuthenticator, needs to be defined here
     */
    function putData($username, $data)
    {
        $user = Mage::getModel('admin/user')->loadByUsername($username);
        $user->setTwofactorauthToken("test");
        $user->save();
    }

    /*
     * abstract function in GoogleAuthenticator, needs to be defined here
     */
    function getUsers()
    {
    }
}
