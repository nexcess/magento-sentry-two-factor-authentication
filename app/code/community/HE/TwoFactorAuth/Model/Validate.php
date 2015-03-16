<?php
/*
 * Author   : Greg Croasdill
 *            Human Element, Inc http://www.human-element.com
 *
 * License  : MPL http://en.wikipedia.org/wiki/Mozilla_Public_License
 *
 * Base class for authentication validation testing
 *
 * For more information on Duo security's Rest v2 API, please see the following URL
 * https://www.duosecurity.com/docs/authap
 */


class HE_TwoFactorAuth_Model_Validate extends Mage_Core_Model_Abstract
{
    const TFA_STATE_NONE        = 0;
    const TFA_STATE_PROCESSING  = 1;
    const TFA_STATE_ACTIVE      = 2;

    public function getProviderArray() {
        return array(
            array('value' => 'duo',     'label'=>Mage::helper('adminhtml')->__('Duo Security')),
            array('value' => 'google',  'label'=>Mage::helper('adminhtml')->__('Google')),
        );
    }

    public function signRequest($user) {
    }

    public function verifyResponse($response) {
    }



}