<?php
/*
 * Author   : Greg Croasdill
 *            Human Element, Inc http://www.human-element.com
 *
 * License  : MPL http://en.wikipedia.org/wiki/Mozilla_Public_License
 *
 * For more information on Duo security's Rest v2 API, please see the following URL
 * https://www.duosecurity.com/docs/authap
 */

class HE_TwoFactorAuth_Helper_Data extends Mage_Core_Helper_Abstract {

    public function __construct(){
        $this->_provider= Mage::getStoreConfig('he2faconfig/control/provider');
    }

    public function isDisabled(){

        $return=true;

        if  (!$this->_provider || $this->_provider == 'disabled') {
            return $return;
        }

        //TODO - Use provider based checks instead of hardcoding for Duo
        if (!Mage::getModel('he_twofactorauth/validate_duo_request')->ping()) {
            $msg = Mage::helper('he_twofactorauth')->__(
                'Can not connect to specified Duo API server - TFA settings not validated'
            );
            Mage::getSingleton('adminhtml/session')->addError($msg);

            Mage::log("isDisabled - ping Duo API server failed - settings not valid.", 0, "two_factor_auth.log");
            $newMode="NOT VALID";

        } elseif (!Mage::getModel('he_twofactorauth/validate_duo_request')->check()) {
            $msg = Mage::helper('he_twofactorauth')->__(
                'Credentials for Duo API server not accepted, please check - TFA settings not validated'
            );
            Mage::getSingleton('adminhtml/session')->addError($msg);

            Mage::log("isDisabled - check Duo API server failed - settings not valid.", 0, "two_factor_auth.log");
            $newMode="NOT VALID";
        } else {
            $newMode="VALID";
            $return=false;
        }

        if ($newMode <> Mage::getStoreConfig('he2faconfig/duo/validated')) {
            Mage::getModel('core/config')->
                saveConfig('he2faconfig/duo/validated', $newMode);
            Mage::app()->getStore()->resetConfig();
        }

        return $return;
    }

    public function getProvider(){
        return $this->_provider;
    }

    public function disable2FA(){
        Mage::getModel('core/config')->
            saveConfig('he2faconfig/control/provider', 'disabled');
        Mage::app()->getStore()->resetConfig();
    }
}