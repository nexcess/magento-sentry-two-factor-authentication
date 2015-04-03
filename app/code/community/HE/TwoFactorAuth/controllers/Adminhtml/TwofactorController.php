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

class HE_TwoFactorAuth_Adminhtml_TwofactorController extends Mage_Adminhtml_Controller_Action
{

    //need an action per provider so that we can load the correct 2fa form
    public function duoAction()
    {
        //TODO - fix logging settings

        Mage::log("duoAction start", 0, "two_factor_auth.log");
        $msg = Mage::helper('he_twofactorauth')->__('Please complete the DUO two factor authentication');
        Mage::getSingleton('adminhtml/session')->addNotice($msg);

        $this->loadLayout();
        $this->renderLayout();
    }


    public function googleAction()
    {
        Mage::log("googleAction start", 0, "two_factor_auth.log");
        $this->loadLayout();
        $this->renderLayout();
    }


    /***
     * verify is a generic action, looks at the current config to get provider, then dispatches correct verify method
     * @return $this
     */
    public function verifyAction()
    {
        Mage::log("verifyAction start", 0, "two_factor_auth.log");
        $provider = Mage::helper('he_twofactorauth')->getProvider();

        $verifyProcess = '_verify' . ucfirst($provider);

        if (method_exists($this, $verifyProcess)) {
            $this->$verifyProcess();
        } else {
            Mage::helper('he_twofactorauth')->disable2FA();
            Mage::log("verifyAction - Unsupported provider $provider. Two factor Authentication is disabled", 0, "two_factor_auth.log");
        }
        return $this;
    }

    private function _verifyDuo()
    {
        $duoSigResp = Mage::app()->getRequest()->getPost('sig_response', null);

        $validate = Mage::getModel('he_twofactorauth/validate_duo');

        if ($validate->verifyResponse($duoSigResp) === false) {
            Mage::log("verifyAction fail", 0, "two_factor_auth.log");

            //TODO - make status message area on template
            $msg = Mage::helper('he_twofactorauth')->__(
                'Two Factor Authentication has failed. Please try again or contact an administrator'
            );
            Mage::getSingleton('adminhtml/session')->addError($msg);

            $this->_redirect('adminhtml/twofactor/duo');
            return $this;
        }

        Mage::log("verifyAction - Duo Validated", 0, "two_factor_auth.log");

        Mage::getSingleton('admin/session')->set2faState(HE_TwoFactorAuth_Model_Validate::TFA_STATE_ACTIVE);
        $this->_redirect('*');
        return $this;
    }


    private function _verifyGoogle()
    {
        Mage::log("verifyAction - start Google validate", 0, "two_factor_auth.log");
        $params =  $this->getRequest()->getParams();

        // save the user's shared secret 
        if ((!empty($params['google_secret'])) && (strlen($params['google_secret']) == 16)) { 
            $user            = Mage::getSingleton('admin/session')->getUser();
            $admin_user      = Mage::getModel('admin/user')->load($user->getId());
            $admin_user->twofactor_google_secret = Mage::helper('core')->encrypt($params['google_secret']);
            $admin_user->save(); 
             Mage::log("google secret saved", 0, "two_factor_auth.log");

            // redirect back to login, now they'll need to enter the code.
            $msg = Mage::helper('he_twofactorauth')->__("Please enter your input code.");
            Mage::getSingleton('adminhtml/session')->addError($msg);
            $this->_redirect('adminhtml/twofactor/google');
            return $this;
        }
        else { 
            // check the key

            // Test to make sure the parameter exists and remove any spaces
            if (array_key_exists('input_code', $params)) {
                $gcode = str_replace(' ', '', $params['input_code']);
            } else {
                $gcode='';
            }

            // TODO add better error checking and flow!
            if ((strlen($gcode) == 6) && (is_numeric($gcode))) { 
                Mage::log("Checking input code '" . $gcode ."'", 0, "two_factor_auth.log");
                $g2fa = Mage::getModel("he_twofactorauth/validate_google");
                $goodCode = $g2fa->validateCode($gcode);
                if ($goodCode) { 
                    $msg = Mage::helper('he_twofactorauth')->__("Valid code entered");
                    Mage::getSingleton('adminhtml/session')->addSuccess($msg);
                    Mage::getSingleton('admin/session')->set2faState(HE_TwoFactorAuth_Model_Validate::TFA_STATE_ACTIVE);
                    $this->_redirect('*');
                    return $this;
                } else { 
                    $msg = Mage::helper('he_twofactorauth')->__("Invalid code entered");
                    Mage::getSingleton('adminhtml/session')->addError($msg);
                    $this->_redirect('adminhtml/twofactor/google');
                    return $this;
                }
            } else {
                $msg = Mage::helper('he_twofactorauth')->__("Invalid code entered");
                Mage::getSingleton('adminhtml/session')->addError($msg);
                $this->_redirect('adminhtml/twofactor/google');
                return $this;
            }
        }
    }



    /***
     * verify is a generic action, looks at the current config to get provider, then dispatches correct verify method
     * @return $this
     */
    public function validateAction()
    {
        Mage::log("validateAction start", 0, "two_factor_auth.log");
        $provider = Mage::helper('he_twofactorauth')->getProvider();

        $validateProcess = '_validate' . ucfirst($provider);

        if (method_exists($this, $validateProcess)) {
            $this->$validateProcess();
        } else {
            Mage::helper('he_twofactorauth')->disable2FA();
            Mage::log("validateAction - Unsupported provider $provider. Two factor Authentication is disabled", 0, "two_factor_auth.log");
        }
        return $this;
    }

    private function _validateDuo()
    {
        Mage::log("validateAction starting", 0, "two_factor_auth.log");

        $validate = Mage::getModel('he_twofactorauth/validate_duo_request');

        if ($validate->ping() == false) {
            Mage::log("validateAction ping fail - can not communicate with Duo auth server", 0, "two_factor_auth.log");

            $msg = Mage::helper('he_twofactorauth')->__(
                'Can not connect to authentication server. Two Factor Authentication has been disabled.'
            );
            Mage::getSingleton('adminhtml/session')->addError($msg);

        } elseif ($validate->check() == false) {
            Mage::log("validateAction check fail - can not communicate with Duo auth server", 0, "two_factor_auth.log");

            $msg = Mage::helper('he_twofactorauth')->__(
                'Can not connect to authentication server. Two Factor Authentication has been disabled.'
            );
            Mage::getSingleton('adminhtml/session')->addError($msg);

        }

        $this->_redirect('*');
        return $this;
    }

}