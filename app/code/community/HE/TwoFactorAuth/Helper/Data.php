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

class HE_TwoFactorAuth_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function __construct()
    {
        $this->_provider = Mage::getStoreConfig('he2faconfig/control/provider');
        $this->_logging = Mage::getStoreConfig('he2faconfig/control/logging');
        $this->_logAccess = Mage::getStoreConfig('he2faconfig/control/logaccess');
    }

    public function isDisabled()
    {
        $tfaFlag = Mage::getBaseDir('base') . '/tfaoff.flag';

        if (file_exists($tfaFlag)) {
            if ($this->shouldLog()) {
                Mage::log("isDisabled - Found tfaoff.flag, TFA disabled.", 0, "two_factor_auth.log");
            }

            return true;
        }

        if (!$this->_provider || $this->_provider == 'disabled') {
            return true;
        }

        $method = Mage::getSingleton('he_twofactorauth/validate_' . $this->_provider);

        if (!$method) {
            return true;
        }

        return !$method->isValid();
    }

    public function getProvider()
    {
        return $this->_provider;
    }


    public function shouldLog()
    {
        return $this->_logging;
    }

    public function shouldLogAccess()
    {
        return $this->_logAccess;
    }

    public function disable2FA()
    {
        Mage::getModel('core/config')->saveConfig('he2faconfig/control/provider', 'disabled');
        Mage::app()->getStore()->resetConfig();
    }
}