Author   : Greg Croasdill
           Human Element, Inc http://www.human-element.com

License  : MPL http://en.wikipedia.org/wiki/Mozilla_Public_License

This work is loosely based on the MagentoHackday TFA example module. Which is covered by the
The MIT License (MIT).

For more information on that work, please see -
https://github.com/magento-hackathon/Magento-Two-factor-Authentication


For more information on Duo security's Rest v2 API, please see the following URL
https://www.duosecurity.com/docs/authap


For mor information on Google's Authenticator, please see th following URL
https://support.google.com/accounts/answer/180744?hl=en&ref_topic=1099588


If there is a problem with the configuration of the settings, you may find yourself
locked out of the admin area of Magento. To temporarily disable two factor authentication,
place a file named tfaoff.flag in the root directory of your Magento installation.
This will allow you to login without the second factor.  Please update your configuration
and remove the tfaoff.flag file to re-enable two factor authentication.
