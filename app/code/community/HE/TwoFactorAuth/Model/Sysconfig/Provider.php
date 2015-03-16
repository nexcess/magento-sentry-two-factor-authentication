<?php

/*
 * Author   : Greg Croasdill
 *            Human Element, Inc http://www.human-element.com
 *
 * License  : MPL http://en.wikipedia.org/wiki/Mozilla_Public_License
 *
 * Used in creating options for provider config value selection
 *
 * For more information on Duo security's Rest v2 API, please see the following URL
 * https://www.duosecurity.com/docs/authap
 */

class HE_TwoFactorAuth_Model_Sysconfig_Provider
{
    /**
     * Options getter - creates a list of options from a list of providers in config.xml
     */
    public function toOptionArray()
    {
        // get the list of providers from the validator class

        $providersXML = Mage::getStoreConfig('he2faconfig/providers');  //set in config.xml

        $providers=array();

        foreach($providersXML as $provider => $node) {
            $providers[]=(array('value' => $provider , 'label' => $node['title']));
        }

        return $providers;
    }
}
