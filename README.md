# kPoint Moodle Plugin
**Description**

kPoint's Video Package for Moodle 3.5 and above makes it easy to use videos from the kPoint's powerful Enterprise Video Platform to any Moodle powered learning portal. The package is developed specifically for Moodle and seamlessly integrates with Moodle’s other features and modules, such as resources and activities, so that users can search and embed media easily.

The package adds the following features for all users:
  * Ability to easily search, choose and insert videos from your kPoint video library into your course in the Moodle learning portal
  * Track video view status by a learner in near real-time
  * Maintain logs for user's interactions with videos.

Before installing kPoint activity plugin make sure you install the kPoint repository plugin because of the plugin dependencies.


**Version information:**

Please take note of the version of Moodle you have and the version of the plug-in you are installing.
  * Make sure you have moodle 3.5 and above version.


**Installing the kPoint's video package for Moodle 3.5 and above**
**Prerequisites**
* A kPoint siteadmin user’s account on your an active domain
* Client id
* Secret key
* Domain name e.g., ktpl.kPoint.com
* Minimum moodle version 3.5

To get your client id and secret key, please contact your customer support representative or write to helpdesk@kpoint.com


**Installation Process**
1. Download the latest kPoint Repository plugin and kPoint Activity plugin.
1. Install kPoint Repository plugin by going to the "Site Administration" area. Then go to the "Plugins" tab. Next, select “Install plugin”. Next select kPoint repository plugin zip file and click on “Install from zip file”. Next click on “upgrade moodle database now”.
1. Install kPoint Activity plug-in by going to the "Site Administration" area. Then go to the "Plugins" tab. Next, select “Install plugin”.Next select kPoint activity plugin zip file and click on “Install from zip file”.Next click on “upgrade moodle database now”.
1. Enter the kPoint credentials by going to the "Site Administration" area. Then go to the "Plugins" tab. Next, scroll down to the "repositories" section. Click "Manage repositories".Then scroll down to the "kPoint" section and select Enable and visible from Dropdown. Enter the following information:

      Repository plugin name | Name of the Repository
      Client ID              | This is your kPoint accounts client id.
      Secret Key             | This is your kPoint accounts secret key.
      Email                  | kPoint Site Administrator id
      Display Name           | site Administrator display name

1. Next,Click on “Test” for Credential Authentication. If the test is successful then “Save” button will be enabled. Click on “Save” to save configuration.

