# Sentry
## Magento Two Factor Authentication Module

### Authors
- Greg Croasdill, Human Element, Inc http://www.human-element.com
- Gregg Milligan, Human Element, Inc http://www.human-element.com
- Aric Watson, Nexcess.net     https://www.nexcess.net/

### License  
- GPL  -- https://www.gnu.org/copyleft/gpl.html

### Purpose
Sentry Two-Factor Authentication will protect your Magento store and customer data by adding an extra check to authenticate your Admin users before allowing them access. Developed as a partnership between the Human Element Magento Development team and Nexcess Hosting, Sentry Two-Factor Authentication for Magento is easy to setup and admin users can quickly login.

### Supported Providers (more to come)
The following __Two Factor Authentication__ providers are supported at this time.
#### Duo Security
For more information on Duo security's API, please see -
- https://www.duosecurity.com

#### Google Authenticator
For more information on Google Authenticator, please see -
- https://github.com/google/google-authenticator/wiki
- https://support.google.com/accounts/answer/180744?hl=en&ref_topic=1099588


## Support

If you have an issue, please read the [FAQ](https://github.com/nexcess/magento-sentry-two-factor-authentication/wiki/FAQ)
then if you still need help, open a bug report in GitHub's
[issue tracker](https://github.com/nexcess/magento-sentry-two-factor-authentication/issues).

Please do not use Magento Connect's Reviews or (especially) the Q&A for support.
There isn't a way for me to reply to reviews and the Q&A moderation is very slow.

## Contributing

If you have a fix or feature for this module, submit a pull request through GitHub
to the **devel** branch. The *master* branch is only for stable releases. Please
make sure the new code follows the same style and conventions as already written
code.

### Referenced work

Some code based on previous work by Jonathan Day jonathan@aligent.com.au
- https://github.com/magento-hackathon/Magento-Two-factor-Authentication

Some code based on previous work by Michael Kliewe/PHPGangsta
- https://github.com/PHPGangsta/GoogleAuthenticator
- http://www.phpgangsta.de/

----
### Notes -
1. Installing this module will update the admin_user table in the Magento database to add a twofactor_google_secret
field for storing the local GA key. It is safe to remove this field once the module is removed.
1. If you get locked out of admin because of a settings issue, loss of your provider account or other software related issue, you can *temporarily disable* the second factor authentication - 
 - Place a file named __tfaoff.flag__ in the root directory of your Magento installation.
 - Login to Magento's Admin area without the second factor.  
 - Update settings or disable Sentry
 - Remove the tfaoff.flag file to re-enable two factor authentication.
