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
