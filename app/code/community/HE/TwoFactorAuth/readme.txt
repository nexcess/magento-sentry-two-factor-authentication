Authors   : Greg Croasdill
            Human Element, Inc http://www.human-element.com

            Aric Watson
            Nexcess.net     https://www.nexcess.net/

License  : GPL  -- https://www.gnu.org/copyleft/gpl.html

For more information on Duo security's API, please see -
   https://www.duosecurity.com

For more information on Google Authenticator, please see -
   https://github.com/google/google-authenticator/wiki
   https://support.google.com/accounts/answer/180744?hl=en&ref_topic=1099588

Some code based on previous work by Jonathan Day jonathan@aligent.com.au
   https://github.com/magento-hackathon/Magento-Two-factor-Authentication

Some code based on previous work by Michael Kliewe/PHPGangsta
  https://github.com/PHPGangsta/GoogleAuthenticator
  http://www.phpgangsta.de/

-----------

Notes -
1) Installing this module will update the AdminUser table in the Magento database to add a twofactor_google_secret
field for storing the local GA key. It is safe to remove this field once the module is removed.


2) If you get locked out of admin because of a settings issue, loss of your Duo account or other software
related issue, you can temporarily disable the second factor authentication.

To temporarily disable two factor authentication, place a file named tfaoff.flag in the root directory
of your Magento installation. This will allow you to login without the second factor.  Please update
your configuration and remove the tfaoff.flag file to re-enable two factor authentication.
