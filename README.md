# WebPageLoadStats
uses PHP curl mod to test a website loading and its SSL certificate

Here is a sample of what it returns (using https://support.uptimesoftware.com as an example)

* content_type: text/html; charset=UTF-8
* http_code: 200-OK
* header_size: 740
* request_size: 65
* namelookup_time: 0
* connect_time: 47
* pretransfer_time: 219
* starttransfer_time: 344
* total_time: 406
* size_download: 12575
* speed_download: 30972
* download_content_length: -1
* ssl_verify_result: 20
* Country: US
* Organization: Symantec Corporation
* Organizational unit: Symantec Trust Network
* Common Name: Symantec Class 3 Secure Server SHA256 SSL CA
* Country: US
* State: Texas
* Locale / City: houston
* Organization: Idera, Inc.
* Organizational unit: Idera, Inc.
* Common Name: support.uptimesoftware.com
* Certificate Expiration Date: 2018-03-06 23:59:59 GMT
* SSL cert expiry days: 336

All times are in milliseconds. It does loading statistics as well as certificate analysis. It is available here:

You can install it manually on your monitoring station by following these steps:

1. unzip the file into the main uptime folder, it will place files in proper locations.
2. move the webpageloadstats.xml that will now reside in your main uptime folder into the xml subfolder
3. run command prompt as administrator
4. navigate to uptime\scripts folder (type cd %MIBDIRS%\..\scripts and hit enter)
5. run: erdcloader -x path.to.your.uptime\XML\webpageloadstats.xml

it should say:

2017-04-04 08:53:18,017 - Initialized ERDCLoader
2017-04-04 08:53:18,144 - Database connection was not explicitly initialized; performing check...

if all goes well, you should then be able to add a service monitor which resides in the "Applications - Web Services" category called "Webpage Load and Certificate Stats".

Configuring it is easy, just put in the url of the site you wish to test, select whether you want it to follow any and all redirects that may come up during the web page loading, and whether or not you want a verbose output of the certificate. Then, you can alert on and save any of the metrics that are returned (listed above). There are dozens of use cases for this monitor I believe and I'm sure you can see so by the metrics as well. 
