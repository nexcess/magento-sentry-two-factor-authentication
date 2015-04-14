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


class HE_TwoFactorAuth_Model_Sysconfig_Appkey extends Mage_Adminhtml_Model_System_Config_Backend_Encrypted
{
    public function save()
    {
        // TODO - check to see if Duo is enabled
        $appkey = $this->getValue(); //get the value from our config

        if(strlen($appkey) < 40)   //exit if we're less than 50 characters
        {
            Mage::throwException("The Duo application key needs to be at least 40 characters long.");
        }
        return parent::save();  //call original save method so whatever happened
    }

    protected function _afterLoad()
    {
        $value = (string)$this->getValue();
        if (empty($value)) {
            $key = $this->generateKey(40);
            $this->setValue($key);
        }
    }

    function generateKey($length=40)
    {
        $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $count = strlen($charset);
        $str = '';
        while ($length--) {
            $str .= $charset[mt_rand(0, $count-1)];
        }
        return $str;
    }
}