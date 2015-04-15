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


class HE_TwoFactorAuth_Model_Validate extends Mage_Core_Model_Abstract
{
    const TFA_STATE_NONE = 0;
    const TFA_STATE_PROCESSING = 1;
    const TFA_STATE_ACTIVE = 2;

    const TFA_CHECK_FAIL = 0;
    const TFA_CHECK_SUCCESS = 1;

    public function signRequest($user)
    {
    }

    public function verifyResponse($response)
    {
    }

    public function isValid()
    {
        return $this::TFA_CHECK_FAIL;
    }
}