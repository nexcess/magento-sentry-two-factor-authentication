<?php

/*
 *
 * Author   : Greg Croasdill
 *            Human Element, Inc http://www.human-element.com
 *
 * License  : MPL http://en.wikipedia.org/wiki/Mozilla_Public_License
 *
 * Implements Duo Two Factor authentication handler for Magento
 *
 */

include_once Mage::getBaseDir('lib') . DS . 'Duo' . DS . 'duo_web.php';

class HE_TwoFactorAuth_Model_Validate_Duo extends HE_TwoFactorAuth_Model_Validate
{
    public function __construct()
    {
        $this->_host = Mage::getStoreConfig('he2faconfig/duo/host');
        $this->_ikey = Mage::helper('core')->decrypt(Mage::getStoreConfig('he2faconfig/duo/ikey'));
        $this->_skey = Mage::helper('core')->decrypt(Mage::getStoreConfig('he2faconfig/duo/skey'));
        $this->_akey = Mage::helper('core')->decrypt(Mage::getStoreConfig('he2faconfig/duo/akey'));

        if (!($this->_host && $this->_ikey && $this->_skey && $this->_akey)) {

            Mage::helper('he_twofactorauth')->disable2FA();

            $msg = Mage::helper('he_twofactorauth')->__('Duo Twofactor Authentication is missing one or more settings. Please configure HE Two Factor Authentication.');
            Mage::getSingleton('adminhtml/session')->addError($msg);
        }
    }

    public function signRequest($user)
    {
        Mage::log("in signRequest with $user", 0, "two_factor_auth.log");
        $sig_request = Duo::signRequest($this->_ikey, $this->_skey, $this->_akey, $user);
        Mage::log(print_r($sig_request, true), 0, "two_factor_auth.log");

        return $sig_request;
    }

    public function verifyResponse($response)
    {
        $verified = Duo::verifyResponse($this->_ikey, $this->_skey, $this->_akey, $response);
        return ($verified != null);
    }

    public function getHost()
    {
        return $this->_host;
    }

    public function check()
    {
        //todo - finish calling DUO check
        //call the Duo Check rest API to test the integration settings

        $params=array();

        $date = date("r");

        $path ="/auth/v2/check";
        $url = "https://".$this->getHost().$path;

        $headers = array("Date: $date");

        $cannon = array (
            "date" => $date,
            "method"=>"GET",
            "host"=>$this->_host,
            "path"=>$path,
        );

        if (count($params)) {
            $cannon["params"]=ksort($params);
            $url .= "?".http_build_query($params);
        } else {
            $cannon["params"]="";
        }

        $hashData = implode("\n",$cannon);
        $hash = hash_hmac('sha1', $hashData , $this->_skey);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        //curl_setopt($curl, CURLOPT_POST, 1); //defaults to get
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_USERPWD, $this->_ikey . ":" . $hash);
        curl_setopt($curl, CURLOPT_HEADER, 1);
//        curl_setopt($curl, CURLINFO_HEADER_OUT, 1);

//        curl_setopt($curl, CURLOPT_VERBOSE, true);

//        echo print_r(curl_getinfo($curl),true) ."\n";

        $result = curl_exec($curl);
//        echo print_r(curl_getinfo($curl),CURLINFO_HEADER_OUT) ."\n";
//        echo curl_error ( $curl ) ."\n";

        echo print_r($result,true) ."\n";
        curl_close($curl);
        return true;

    }
}