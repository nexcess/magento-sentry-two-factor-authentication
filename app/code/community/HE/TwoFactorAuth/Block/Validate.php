<?php

class HE_TwoFactorAuth_Block_Validate extends Mage_Core_Block_Template
{
    public function getSaveUrl()
    {
        return $this->getUrl('twofactorauth/interstitial/verify');
    }
}
