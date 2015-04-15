<?php

/*
 * Author   : Greg Croasdill
 *            Human Element, Inc http://www.human-element.com
 *
 * License  : GPL  -- https://www.gnu.org/copyleft/gpl.html
 *
 * This class implements the Duo Security's V2 REST API.  Currently only 3 functions are supported
 *  - ping
 *  - check
 *  - logo
 * Ping and Check are basic functions used to validate the integration settings.  Logo can be used to
 * customize the login screen.
 *
 * If ping or check fail, then the integration is disabled.  This allows users to access the Magento admin
 * even if the Duo integration settings are incorrect.
 *
 * For more information on Duo security's API, please see -
 *   https://www.duosecurity.com
 */

class HE_TwoFactorAuth_Model_Validate_Duo_Request extends Mage_Core_Model_Abstract
{
    var $_path;   //rest request path
    var $_params; //call parameters
    var $_method; //call method (GET|POST)
    var $_date;   //current time, formatted as RFC 2822.

    // these values are supplied on the DUO integration control panel
    var $_host;   //DUO API host
    var $_ikey;
    var $_skey;

    // a unique key for your application, set in the Magento Admin
    var $_akey;

    // a file to keep the logo associated with the DUO integration
    var $_logoFile;

    /***
     * Initialize the request environment
     */
    public function _construct()
    {
        $this->_params = array();
        $this->_path = "";
        $this->_method = "GET";
        $this->_date = date("r");

        $this->_host = Mage::getStoreConfig('he2faconfig/duo/host');
        $this->_ikey = Mage::helper('core')->decrypt(Mage::getStoreConfig('he2faconfig/duo/ikey'));
        $this->_skey = Mage::helper('core')->decrypt(Mage::getStoreConfig('he2faconfig/duo/skey'));
        $this->_akey = Mage::helper('core')->decrypt(Mage::getStoreConfig('he2faconfig/duo/akey'));

        $this->_logoFile = Mage::getBaseDir('media') . "/duo_logo.png";

    }

    /***
     * create the request cannon string to be used in the password hash.  Data must
     * match the request parameters
     *
     * @return string
     */
    protected function _makeCannon()
    {
        $cannon = array(
            "date"   => $this->_date,
            "method" => $this->_method,
            "host"   => $this->_host,
            "path"   => $this->_path,
        );

        if (count($this->_params)) {
            $cannon["params"] = ksort($this->_params);
        } else {
            $cannon["params"] = "";
        }

        return implode("\n", $cannon);
    }

    /***
     * TBD
     */
    protected function _getPostfields()
    {

    }

    /***
     * Create a hash string to be used as the password for the request
     * Must be sha1 encrypted
     *
     * @return string
     */
    protected function _makeHash()
    {
        return hash_hmac('sha1', $this->_makeCannon(), $this->_skey);
    }

    /***
     * Add the date to the request headers, required for validation of authentication request
     *
     * @return array
     */
    protected function _getHeaders()
    {
        return array("Date: $this->_date");
    }

    /***
     * Format parameters for GET calls and return full URL with query string
     *
     * @param string $url
     *
     * @return string
     */
    protected function _addUrlParams($url)
    {
        if (count($this->_params) > 0) {
            return $url . "?" . http_build_query(ksort($this->_params));
        } else {
            return $url;
        }
    }

    /***
     * Call the DUO rest service and return the results
     *
     * @param bool $raw
     * @param bool $debug
     *
     * @return array|bool|mixed
     */

    protected function _doRequest($raw = false, $debug = false)
    {
        if ($this->_path == "") {
            return false;
        }

        $url = "https://" . $this->_host . $this->_path;

        $headers = $this->_getHeaders();

        $curl = curl_init();
        if ($this->_method == "POST") {
            curl_setopt($curl, CURLOPT_POST, 1); //defaults to get
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->_getPostfields());
            curl_setopt($curl, CURLOPT_URL, $url);
        } else {
            $url = $this->_addUrlParams($url);
            curl_setopt($curl, CURLOPT_URL, $url);
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_USERPWD, $this->_ikey . ":" . $this->_makeHash());
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_VERBOSE, $debug);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, $raw);

        if (!$result = curl_exec($curl)) {
            $error = curl_error($curl);
        } else {
            $error = false;
        }
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($error || $responseCode <> '200') {
            //todo - put in error logging
            return false;
        } else {
            if ($raw) {
                return $result;
            } else {
                return json_decode($result, true);
            }
        }
    }

    /***
     * Check to see if the DUO service is reachable
     *
     * @return bool
     */

    public function ping()
    {
        $this->_path = "/auth/v2/ping";
        $result = $this->_doRequest();

        if (!$result || $result['stat'] <> "OK") {
            return false;
        } else {
            return true;
        }
    }

    /***
     * Check to see if the integration settings are valid
     *
     * @return bool
     */

    public function check()
    {
        $this->_path = "/auth/v2/check";
        $result = $this->_doRequest();

        if (!$result || $result['stat'] <> "OK") {
            return false;
        } else {
            return true;
        }
    }

    /***
     * Get the logo, if one is registered with DUO, for the integration
     *
     * @return bool
     */
    public function logo()
    {
        $this->_path = "/auth/v2/logo";
        $result = $this->_doRequest(true);

        if (!$result) {
            return false;
        } else {
            if (file_put_contents($this->_logoFile, $result)) {
                return $this->_logoFile;
            } else {
                return false;
            }
        }
    }

    // TODO -   the remainder of the DUO protocol will be filled out later if needed

    public function enroll()
    {
    }

    public function enroll_status()
    {
    }

    public function preauth()
    {
    }

    public function auth()
    {
    }

    public function auth_status()
    {
    }
}