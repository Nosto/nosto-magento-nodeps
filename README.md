# Personalization for Magento (No dependencies version)

Please see https://github.com/Nosto/nosto-magento for release notes. If you plan to contribute please do so in https://github.com/Nosto/nosto-magento.   

# How to use this repo
* Always use versions with the extra trailing version number, e.g. 2.7.0.1. The versions following the standard semantic versions will NOT have the dependencies bundled. 
* This repo is only meant to be used together with magento-composer-installer
* No pull requests to this repo please. Use https://github.com/Nosto/nosto-magento  

# How to update this repo
* Pull latest changes from upstream/master to local master
* Make sure the `composer.json` does not get overridden! Bump SDK version if needed. Also note that the sdk must be inside require-dev block for this fork.
* Resolve possible conflicts
* Run `composer update` (if SDK version changed)
* Push changes to origin/master
* Tag with appropriate version number. For example if the last release in upstream is 2.7.3 tag origin as 2.7.3.1

