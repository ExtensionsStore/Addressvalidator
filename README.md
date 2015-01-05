Address Validator
=================

Description
-----------
Validate billing and shipping addresses in the One Page Checkout. 
Customer submits billing and shipping addresses and is shown a popup of valid addresses to choose from. 
Currently has support for Address Doctor, Melissa Data; coming soon USPS and UPS.


How to use
-------------------------
Upload the files to the root of your Magento install. Let the install script run. This will create the table
aydus_addressvalidator_responses to hold requests and responses. In the System -> Configuration,
go to Sales -> Address Validator and configure the extension with your verification provider 
(USPS, Melissa Data, Address Doctor, etc.) account. Clear cache. 