CHECKOUT BY AMAZON INSTANT ORDER PROCESSING NOTIFICATION MODULE FOR PHP
Copyright 2012 Amazon.com, Inc. or its affiliates. All Rights Reserved.
*-*-**-***-*****-********-*************

*-*-**-***-*****-********-*************
CONTENT SECTIONS (in order of appearance)
*-*-**-***-*****-********-*************
          INTRODUCTION          
          PREREQUISITES          
          INCLUDED FILES
          Third Party Libraries
          RELEASE NOTES
          SUPPORT & PROJECT HOME
          LINKS
*-*-**-***-*****-********-*************
INTRODUCTION
*-*-**-***-*****-********-*************
          
Please understand that by installing Checkout by Amazon Instant Order
Payment Notification sample code, you are agreeing to understand and abide by the
terms of the license, as written in LICENSE.txt.  Important links are
grouped together in a separate section for your convenience.  The most
current documentation on Checkout by Amazon can be found on its
website.          

*-*-**-***-*****-********-*************
PREREQUISITES
*-*-**-***-*****-********-*************
   PHP 3.0 and above


*-*-**-***-*****-********-*************
INCLUDED FILES
*-*-**-***-*****-********-*************

The zip file contains files in the following structure:

 Root
   |-AmazonCBAIOPN      
      |-lib
      |-source
      |-log
      |-schema      
      |-prop      
      |-LICENSE.txt
      |-INSTALLATION_GUIDE.txt
      |-NOTICE.txt
      |-README.txt 

Source:      This directory contains the source files. 

lib:         This directory contains the classes, third party libraries and licensing information which 
             you can use for processing the request and generating the appropriate response.

log:         This directory contains the log of requests.
 
schema:      This directory contains the list of XML schema files namely iopn.xsd, callback.xsd
             and order.xsd. These are used to validate the request.

prop:        Contains the merchant.properties file where you specify your (AWSAccessKeyId,AWSSecretKey) pairs.

INSTALLATION_GUIDE.txt: Installation and Usage guide.

NOTICE.txt: Notice file.

README.txt: This file.

LICENSE.txt: Apache License. 

*-*-**-***-*****-********-*************
Third Party Libraries
*-*-**-***-*****-********-*************

The following third party libraries are 
used in this sample code. Licenses for each 
package can be found in lib/.

* Crypt_HMAC-1.0.1
* PEAR-1.7.2

*-*-**-***-*****-********-*************
RELEASE NOTES
*-*-**-***-*****-********-*************

(1) Carefully follow all instructions in INSTALLATION_GUIDE.txt
(2) You must have set up an Checkout by Amazon account & have your merchantID available. Your merchant ID is located in
    Seller Central > Settings > Checkout Pipeline Settings.
(3) It is required to set up an AWS account in Seller Central > Integration Tab > Access Keys. 
(4) Please login to Seller Central(https://sellercentral.amazon.com), then go to  Orders > Manage Orders to review the orders processed through Checkout by Amazon.

*-*-**-***-*****-********-*************
SUPPORT & PROJECT HOME
*-*-**-***-*****-********-*************
        The latest documentation on Checkout by Amazon Instant Order Processing Notification can be found at in the LINKS section below.

*-*-**-***-*****-********-*************
LINKS
*-*-**-***-*****-********-*************
		Checkout by Amazon Documentation & Seller Central
                https://sellercentral.amazon.com/gp/help/
        Amazon Web Services
                http://www.amazon.com/webservices
        Amazon Seller Community Website
                http://www.amazonsellercommunity.com/forums/
                                                                                                                                                            
