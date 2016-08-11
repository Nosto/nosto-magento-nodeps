# Personalization for Magento (No dependencies version)

Please see https://github.com/Nosto/nosto-magento for release notes. 

# How to update this repo
* Pull latest changes from upstream/master to local master
* Make sure the `composer.json` does not get overridden! The sdk must be inside require-dev block for this fork
* Resolve possible conflicts
* Run `composer update`
* Push changes to origin/master
* Tag with appropriate version number. For example if the last release in upstream is 2.7.3 tag origin as 2.7.3.1
