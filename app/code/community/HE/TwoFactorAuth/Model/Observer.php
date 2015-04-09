<?php

/*
 * Author   : Greg Croasdill
 *            Human Element, Inc http://www.human-element.com
 *
 * License  : MPL http://en.wikipedia.org/wiki/Mozilla_Public_License
 *
 * Observer watches login attempts (currently admin only) and will enforce multi-factor
 * authentication if not disabled.
 *
 * For more information on Duo security's Rest v2 API, please see the following URL
 * https://www.duosecurity.com/docs/authap
 */

class HE_TwoFactorAuth_Model_Observer
{
    protected $_allowedActions = array('login','forgotpassword');

    public function __construct()
    {
        $this->_shouldLog = Mage::helper('he_twofactorauth')->shouldLog();
    }

    public function admin_user_authenticate_after($observer)
    {
        if (Mage::helper('he_twofactorauth')->isDisabled()) return;

        if (Mage::getSingleton('admin/session')->get2faState() != HE_TwoFactorAuth_Model_Validate::TFA_STATE_ACTIVE) {

            if ($this->_shouldLog) {
                Mage::log("authenticate_after - get2faState is not active", 0, "two_factor_auth.log");
            }

            // set we are processing 2f login
            Mage::getSingleton('admin/session')->set2faState(HE_TwoFactorAuth_Model_Validate::TFA_STATE_PROCESSING);

            $provider=Mage::helper('he_twofactorauth/data')->getProvider();

            //redirect to the 2f login page
            $twoFactAuthPage = Mage::helper("adminhtml")->getUrl("adminhtml/twofactor/$provider");

            if ($this->_shouldLog) {
                Mage::log("authenticate_after - redirect to $twoFactAuthPage", 0, "two_factor_auth.log");
            }

            Mage::app()->getResponse()
                ->setRedirect($twoFactAuthPage)
                ->sendResponse();
            exit();
        } else {
            if ($this->_shouldLog) {
                Mage::log("authenticate_after - getValid2Fa is true", 0, "two_factor_auth.log");
            }
        }
    }

    /***
     * controller to check for valid 2fa
     * admin states
     *
     * @param $observer
     */

    public function check_twofactor_active($observer){

        if (Mage::helper('he_twofactorauth')->isDisabled()) return;

        $request = $observer->getControllerAction()->getRequest();
        $tfaState = Mage::getSingleton('admin/session')->get2faState();
        $action = Mage::app()->getRequest()->getActionName();

        switch ($tfaState) {
            case HE_TwoFactorAuth_Model_Validate::TFA_STATE_NONE:
                if ($this->_shouldLog) {
                    Mage::log("check_twofactor_active - tfa state none", 0, "two_factor_auth.log");
                }
                break;
            case HE_TwoFactorAuth_Model_Validate::TFA_STATE_PROCESSING:
                if ($this->_shouldLog) {
                    Mage::log("check_twofactor_active - tfa state processing", 0, "two_factor_auth.log");
                }
                break;
            case HE_TwoFactorAuth_Model_Validate::TFA_STATE_ACTIVE:
                if ($this->_shouldLog) {
                    Mage::log("check_twofactor_active - tfa state active", 0, "two_factor_auth.log");
                }
                break;
            default:
                if ($this->_shouldLog) {
                    Mage::log("check_twofactor_active - tfa state unknown - ".$tfaState, 0, "two_factor_auth.log");
                }
        }
        if( $action == 'logout' ) {
            if ($this->_shouldLog) {
                Mage::log("check_twofactor_active - logout", 0, "two_factor_auth.log");
            }
            Mage::getSingleton('admin/session')->set2faState(HE_TwoFactorAuth_Model_Validate::TFA_STATE_NONE);
            return $this;
        }

        if(in_array( $action, $this->_allowedActions )) {
            return $this;
        }

        if( $request->getControllerName() == 'twofactor' ||
            $tfaState == HE_TwoFactorAuth_Model_Validate::TFA_STATE_ACTIVE) {
            if ($this->_shouldLog) {
                Mage::log("check_twofactor_active - return controller twofactor or is active", 0, "two_factor_auth.log");
            }
            return $this;
        }

        if (Mage::getSingleton('admin/session')->get2faState() != HE_TwoFactorAuth_Model_Validate::TFA_STATE_ACTIVE){

            if ($this->_shouldLog) {
                Mage::log("check_twofactor_active - not active, try again", 0, "two_factor_auth.log");
            }

            $msg = Mage::helper('he_twofactorauth')->__('You must complete Two Factor Authentication before accessing Magento administration');
            Mage::getSingleton('adminhtml/session')->addError($msg);

            // set we are processing 2f login
            Mage::getSingleton('admin/session')->set2faState(HE_TwoFactorAuth_Model_Validate::TFA_STATE_PROCESSING);

            //TODO - change to config setting
            $twoFactAuthPage = Mage::helper("adminhtml")->getUrl("adminhtml/twofactor/duo");

            //disable the dispatch for now
            $request = Mage::app()->getRequest();
            $action = $request->getActionName();
            Mage::app()->getFrontController()
                ->getAction()
                ->setFlag($action, Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);

            $response = Mage::app()->getResponse();

            if ($this->_shouldLog) {
                Mage::log("check_twofactor_active - redirect to $twoFactAuthPage", 0, "two_factor_auth.log");
            }

            $response->setRedirect($twoFactAuthPage)->sendResponse();
            exit();
        }
    }
}