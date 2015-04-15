<?php
/*
 * Author   : Greg Croasdill
 *            Human Element, Inc http://www.human-element.com
 *
 * License  : GPL  -- https://www.gnu.org/copyleft/gpl.html
 *
 * For more information on Duo security's API, please see -
 *   https://www.duosecurity.com
 */

include_once Mage::getBaseDir('lib') . DS . 'Duo' . DS . 'duo_web.php';

class HE_TwoFactorAuth_Model_Validate_Duo extends HE_TwoFactorAuth_Model_Validate
{
    public function __construct()
    {
        $this->_helper = Mage::helper('he_twofactorauth');

        $this->_host = Mage::getStoreConfig('he2faconfig/duo/host');
        $this->_ikey = Mage::helper('core')->decrypt(Mage::getStoreConfig('he2faconfig/duo/ikey'));
        $this->_skey = Mage::helper('core')->decrypt(Mage::getStoreConfig('he2faconfig/duo/skey'));
        $this->_akey = Mage::helper('core')->decrypt(Mage::getStoreConfig('he2faconfig/duo/akey'));

        if (!($this->_host && $this->_ikey && $this->_skey && $this->_akey)) {
            $this->_helper->disable2FA();
            $msg = $this->_helper->__(
                'Duo Twofactor Authentication is missing one or more settings. Please configure HE Two Factor Authentication.'
            );
            Mage::getSingleton('adminhtml/session')->addError($msg);
        }

        $this->_shouldLog = Mage::helper('he_twofactorauth')->shouldLog();
    }

    public function signRequest($user)
    {
        if ($this->_shouldLog) {
            Mage::log("in signRequest with $user", 0, "two_factor_auth.log");
        }
        $sig_request = Duo::signRequest($this->_ikey, $this->_skey, $this->_akey, $user);

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
        return Mage::getModel('he_twofactorauth/validate_duo_request')->check();
    }

    public function isValid()
    {

        $status = HE_TwoFactorAuth_Model_Validate::TFA_CHECK_FAIL;

        //TODO - Use provider based checks instead of hardcoding for Duo
        if (!Mage::getModel('he_twofactorauth/validate_duo_request')->ping()) {
            $msg = $this->_helper->__('Can not connect to specified Duo API server - TFA settings not validated');
        } elseif (!$this->check()) {
            $msg = $this->_helper->__(
                'Credentials for Duo API server not accepted, please check - TFA settings not validated'
            );
        } else {
            $status = HE_TwoFactorAuth_Model_Validate::TFA_CHECK_SUCCESS;
            $msg = $this->_helper->__('Credentials for Duo API server accepted - TFA settings validated');
        }

        //let the user know the status
        if ($status == HE_TwoFactorAuth_Model_Validate::TFA_CHECK_SUCCESS) {
            //Mage::getSingleton('adminhtml/session')->addSuccess($msg);
            if ($this->_shouldLog) {
                Mage::log("isValid - $msg.", Zend_Log::ERR, "two_factor_auth.log");
            }
            $newMode = $this->_helper->__('VALID');
        } else {
            Mage::getSingleton('adminhtml/session')->addError($msg);
            if ($this->_shouldLog) {
                Mage::log("isValid - $msg.", Zend_Log::INFO, "two_factor_auth.log");
            }
            $newMode = $this->_helper->__('NOT VALID');
        }

        //if mode changed, update config
        if ($newMode <> Mage::getStoreConfig('he2faconfig/duo/validated')) {
            Mage::getModel('core/config')->saveConfig('he2faconfig/duo/validated', $newMode);
            Mage::app()->getStore()->resetConfig();
        }

        return $status;
    }
}