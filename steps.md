STEPS
=====
The following provisioning steps should be followed to create a new device on kazoo-provisioner.  In the example code provided we've used Yealink support for v7.2 code base.

#Overview

#General Request Flow
The following general request flow occurs
- a request comes in for a provisioning file
- this calls process.php (via .htaccess), the $uri and $ua are extracted
- next the adapter.php is accessed (kazoo specific - as specified in config.json)
- attempt to access mac address from $uri accessed
- identify account from lcoal bigcouch and find device document
- Extract the following from the document:
 - brand
 - family
 - model
- Construct .. descriptor brand_family_model eg yealink_txxp72_t28p
- Import settings from database
 - factory defaults (brand) 
 - factory defaults (family)
 - factory defaults (model)
- Import provider settings
- Import account settings
- Import device settings
- Final configuration settings are now produced / merged
- Now find the $master_template
 - ../adapter/2600hz/[brand|family|model] (note strange preference/hierarchy in code)
 - ../master.json
- Build the line settings with template and merge results
- Identify the target file
- Find the file list (using helper utils) - in within $brand . "/brand_data.json there is [model][config_files]
- Find the regex list (using helper utils) - in within $brand . "/brand_data.json there is [model][regexs]
Thus the file is produced and returned to the phone.
## Notes
- For documents the later settings will override any previous values (eg device specific most important)
- The 2600hz adapter does not support the y0000000xx.cfg as you would expect. What does happen is that the MAC address is extracted from the $ua and used to find correct document.
- The TIMEZONE setting is taken from the device document.  However there is no means in the GUI to set this correctly.  Thus it is often missing and instead the account default is taken which is almost always “America/Los Angles”. If the device is associated with a user it will pick up the user setting.
- Codecs are taken from the device codec list
- I have modified base.php in yealink to accommodate timezone defaults where account / phone thinks it’s in “LA”.
- To add a new parameter I was able to set a value in factory_defaults, then use it in $mac.cfg, but could not get the line.parameter working
- When you create the factory defaults on the provisioner, this dynamically creates the drop-down menu options for the CVS GUI




#General Configuration Process for a new device
kazoo-provisioner interacts with both the client-ui and the back end kazoo platform.
The client-ui will directly request the models available from the bigcouch on kazoo-provisioner, through an api call.


Step | Description
-----|----
1    | Create a new sub-directory within the selected brand directory .. mkdir /var/www/endpoint/yealink/txxp_72
2    | Update the brand_data.json file with the new device types required 
3    | Create the files required based on the templates .. often two/three files are requested by phones .. for yealink this is a model specific file and a device specific file
4    | create a phone.php within the subdirectory
5    | create any factory default settings in bigcouch

#Yealink Specific Modifications
The model specific files used by yealink are as follows:-

Model | File
------|-------
T28   |	y000000000000.cfg
T26   | y000000000004.cfg
T22   | y000000000005.cfg
T20   | y000000000007.cfg
T20P  | y000000000007.cfg
T19P  |	y000000000031.cfg

You can add specific configuration "stuff" in here applicable to each model (number of lines supported etc)

Additionally we required the ability to upgrade the software remotely.  This is controlled by adding the following to the bigcouch document (we added to the specific factory_defaults file for the device, but can be more specific).
```
  "firmware": {
           "server_type": "http",
           "url": "http://xxx.xxx.xxx.xxx/firmware/yealink/t28p/",
           "name": "2.72.0.30.rom"
       },
```
Don't forget to also place the software in the directory specified.

#Warning
The current implementation has a large number of security flaws.  Specifically:-
- clear text files 
- ability to interogate file structure
- ability to download phone configurations

