<?php

/*
 * Author   : Greg Croasdill
 *            Human Element, Inc http://www.human-element.com
 *
 * License  : GPL  -- https://www.gnu.org/copyleft/gpl.html
 *
 * For more information on Duo security's API, please see -
 *   https://www.duosecurity.com
 *
 * For more information on Google Authenticator, please see -
 *   https://github.com/google/google-authenticator/wiki
 *
 * Some code based on previous work by Jonathan Day jonathan@aligent.com.au
 *   https://github.com/magento-hackathon/Magento-Two-factor-Authentication
 *
 */

class HE_TwoFactorAuth_Adminhtml_TwofactorController extends Mage_Adminhtml_Controller_Action
{

    public function _construct()
    {
        $this->_shouldLog = Mage::helper('he_twofactorauth')->shouldLog();
        $this->_shouldLogAccess = Mage::helper('he_twofactorauth')->shouldLogAccess();
        parent::_construct();
    }

    /**
     * Allow all admin users to access the 2fa forms
     */
    protected function _isAllowed()
    {
        return true;
    }

    //need an action per provider so that we can load the correct 2fa form

    public function duoAction()
    {
        if ($this->_shouldLog) {
            Mage::log("duoAction start", 0, "two_factor_auth.log");
        }
        $msg = Mage::helper('he_twofactorauth')->__('Please complete the DUO two factor authentication');
        Mage::getSingleton('adminhtml/session')->addNotice($msg);

        $this->loadLayout();
        $this->renderLayout();
    }


    public function googleAction()
    {
        if ($this->_shouldLog) {
            Mage::log("googleAction start", 0, "two_factor_auth.log");
        }
        $this->loadLayout();
        $this->renderLayout();
    }


    /***
     * verify is a generic action, looks at the current config to get provider, then dispatches correct verify method
     *
     * @return $this
     */
    public function verifyAction()
    {
        if ($this->_shouldLog) {
            Mage::log("verifyAction start", 0, "two_factor_auth.log");
        }

        if ($this->_shouldLogAccess) {
            $ipAddress = Mage::helper('core/http')->getRemoteAddr();
            $adminName = Mage::getSingleton('admin/session')->getUser()->getUsername();

            Mage::log("TFA Verify attempt for admin account $adminName from IP $ipAddress", 0, "two_factor_auth.log");
        }

        $provider = Mage::helper('he_twofactorauth')->getProvider();

        $verifyProcess = '_verify' . ucfirst($provider);

        if (method_exists($this, $verifyProcess)) {
            $this->$verifyProcess();
        } else {
            Mage::helper('he_twofactorauth')->disable2FA();
            if ($this->_shouldLog) {
                Mage::log(
                    "verifyAction - Unsupported provider $provider. Two factor Authentication is disabled", 0,
                    "two_factor_auth.log"
                );
            }
        }

        return $this;
    }

    private function _verifyDuo()
    {
        $duoSigResp = Mage::app()->getRequest()->getPost('sig_response', null);

        $validate = Mage::getModel('he_twofactorauth/validate_duo');

        if ($validate->verifyResponse($duoSigResp) === false) {
            if ($this->_shouldLog) {
                Mage::log("verifyDuo - failed verify", 0, "two_factor_auth.log");
            }

            if ($this->_shouldLogAccess) {
                $ipAddress = Mage::helper('core/http')->getRemoteAddr();
                $adminName = Mage::getSingleton('admin/session')->getUser()->getUsername();

                Mage::log(
                    "verifyDuo - TFA Verify attempt FAILED for admin account $adminName from IP $ipAddress", 0,
                    "two_factor_auth.log"
                );
            }

            //TODO - make status message area on template
            $msg = Mage::helper('he_twofactorauth')->__(
                'verifyDuo - Two Factor Authentication has failed. Please try again or contact an administrator.'
            );
            Mage::getSingleton('adminhtml/session')->addError($msg);

            $this->_redirect('adminhtml/twofactor/duo');

            return $this;
        }

        if ($this->_shouldLog) {
            Mage::log("verifyDuo - Duo Validated", 0, "two_factor_auth.log");
        }
        if ($this->_shouldLogAccess) {
            $ipAddress = Mage::helper('core/http')->getRemoteAddr();
            $adminName = Mage::getSingleton('admin/session')->getUser()->getUsername();

            Mage::log(
                "verifyDuo - TFA Verify attempt SUCCEEDED for admin account $adminName from IP $ipAddress", 0,
                "two_factor_auth.log"
            );
        }


        Mage::getSingleton('admin/session')
            ->set2faState(HE_TwoFactorAuth_Model_Validate::TFA_STATE_ACTIVE);
        $this->_redirect('*');

        return $this;
    }

    private function _verifyGoogle()
    {
        if ($this->_shouldLog) {
            Mage::log("verifyAction - start Google validate", 0, "two_factor_auth.log");
        }
        $params = $this->getRequest()->getParams();

        $ipAddress = Mage::helper('core/http')->getRemoteAddr();
        $adminName = Mage::getSingleton('admin/session')->getUser()->getUsername();

        // save the user's shared secret 
        if ((!empty($params['google_secret'])) && (strlen($params['google_secret']) == 16)) {
            $user = Mage::getSingleton('admin/session')->getUser();
            $admin_user = Mage::getModel('admin/user')->load($user->getId());
            $admin_user->setTwofactorGoogleSecret(Mage::helper('core')->encrypt($params['google_secret']));
            $admin_user->save();
            if (($this->_shouldLog) || ($this->_shouldLogAccess)) {
                Mage::log(
                    "verifyGoogle - new google secret saved for admin account $adminName from IP $ipAddress", 0,
                    "two_factor_auth.log"
                );
            }

            // redirect back to login, now they'll need to enter the code.
            $msg = Mage::helper('he_twofactorauth')->__("Please enter your input code.");
            Mage::getSingleton('adminhtml/session')->addError($msg);
            $this->_redirect('adminhtml/twofactor/google');

            return $this;
        } else {
            // check the key
            // Test to make sure the parameter exists and remove any spaces
            if (array_key_exists('input_code', $params)) {
                $gcode = str_replace(' ', '', $params['input_code']);
            } else {
                $gcode = '';
            }

            // TODO add better error checking and flow!
            if ((strlen($gcode) == 6) && (is_numeric($gcode))) {
                if ($this->_shouldLog) {
                    Mage::log("verifyGoogle - checking input code '" . $gcode . "'", 0, "two_factor_auth.log");
                }
                $g2fa = Mage::getModel("he_twofactorauth/validate_google");
                $goodCode = $g2fa->validateCode($gcode);
                if ($goodCode) {
                    if ($this->_shouldLogAccess) {

                        Mage::log(
                            "verifyGoogle - TFA Verify attempt SUCCESSFUL for admin account $adminName from IP $ipAddress",
                            0, "two_factor_auth.log"
                        );
                    }

                    $msg = Mage::helper('he_twofactorauth')->__("Valid code entered");
                    Mage::getSingleton('adminhtml/session')->addSuccess($msg);
                    Mage::getSingleton('admin/session')->set2faState(HE_TwoFactorAuth_Model_Validate::TFA_STATE_ACTIVE);
                    $this->_redirect('*');

                    return $this;
                } else {
                    if ($this->_shouldLogAccess) {
                        Mage::log(
                            "verifyGoogle - TFA Verify attempt FAILED for admin account $adminName from IP $ipAddress",
                            0, "two_factor_auth.log"
                        );
                    }
                    $msg = Mage::helper('he_twofactorauth')->__("Invalid code entered");
                    Mage::getSingleton('adminhtml/session')->addError($msg);
                    $this->_redirect('adminhtml/twofactor/google');

                    return $this;
                }
            } else {
                if ($this->_shouldLogAccess) {
                    Mage::log(
                        "verifyGoogle - TFA Verify attempt FAILED for admin account $adminName from IP $ipAddress", 0,
                        "two_factor_auth.log"
                    );
                }
                $msg = Mage::helper('he_twofactorauth')->__("Invalid code entered");
                Mage::getSingleton('adminhtml/session')->addError($msg);
                $this->_redirect('adminhtml/twofactor/google');

                return $this;
            }
        }
    }

    /***
     * verify is a generic action, looks at the current config to get provider, then dispatches correct verify method
     *
     * @return $this
     */
    public function validateAction()
    {
        if ($this->_shouldLog) {
            Mage::log("validateAction start", 0, "two_factor_auth.log");
        }
        $provider = Mage::helper('he_twofactorauth')->getProvider();

        $validateProcess = '_validate' . ucfirst($provider);

        if (method_exists($this, $validateProcess)) {
            $this->$validateProcess();
        } else {
            Mage::helper('he_twofactorauth')->disable2FA();
            if ($this->_shouldLog) {
                Mage::log(
                    "validateAction - Unsupported provider $provider. Two factor Authentication is disabled", 0,
                    "two_factor_auth.log"
                );
            }
        }

        return $this;
    }

    private function _validateDuo()
    {
        if ($this->_shouldLog) {
            Mage::log("validateAction starting", 0, "two_factor_auth.log");
        }

        $validate = Mage::getModel('he_twofactorauth/validate_duo_request');

        if ($validate->ping() == false) {
            if ($this->_shouldLog) {
                Mage::log(
                    "validateDuo - ValidateAction ping fail - can not communicate with Duo auth server", 0,
                    "two_factor_auth.log"
                );
            }

            $msg = Mage::helper('he_twofactorauth')->__(
                'Can not connect to authentication server. Two Factor Authentication has been disabled.'
            );
            Mage::getSingleton('adminhtml/session')->addError($msg);

        } elseif ($validate->check() == false) {
            if ($this->_shouldLog) {
                Mage::log(
                    "validateDuo - ValidateAction check fail - can not communicate with Duo auth server", 0,
                    "two_factor_auth.log"
                );
            }

            $msg = Mage::helper('he_twofactorauth')->__(
                'Can not connect to authentication server. Two Factor Authentication has been disabled.'
            );
            Mage::getSingleton('adminhtml/session')->addError($msg);
        }

        $this->_redirect('*');

        return $this;
    }
}