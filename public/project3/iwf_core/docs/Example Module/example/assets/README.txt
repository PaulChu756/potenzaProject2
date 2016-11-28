##############################
# Information ArchiTECH LLC  #
# ***** MODULE ASSETS ****** #
##############################

Javascript and css files may be autoloaded using the following conventions

js
--global.js  <-- will be loaded on every page in the application
--module.js  <-- will be loaded on every page within the module
--{controller}  <-- actual controller name
----controller.js  <-- will be loaded on every page within the controller
----{action}.js    <-- will be loaded specifically on this action

css
--global.css  <-- will be loaded on every page in the application
--module.css  <-- will be loaded on every page within the module
--{controller}  <-- actual controller name
----controller.css  <-- will be loaded on every page within the controller
----{action}.css    <-- will be loaded specifically on this action

Images may be loaded using the assetUrl view helper.  E.g.

<img src="<?=$this->assetUrl('member',null,'test.jpg');?>" />

Paramaters are:
module - required
controller - optional
filename - required