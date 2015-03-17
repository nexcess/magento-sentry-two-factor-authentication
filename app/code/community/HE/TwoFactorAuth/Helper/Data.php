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

        $tfaFlag = MAGENTO_ROOT . '/tfaoff.flag';

        if (file_exists($tfaFlag)) {
            Mage::log("isDisabled - Found tfaoff.flag, TFA disabled.", 0, "two_factor_auth.log");
            return true;
        }

        if (!$this->_provider || $this->_provider == 'disabled') {
            return true;
        }

        $method = Mage::getSingleton('he_twofactorauth/validate_'.$this->_provider) ;

        if (!$method) {
            return true;
        }

        return !$method->isValid();
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