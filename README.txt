This README is a quick how-to, for more information read the extenstion manual and optionaly the CoolUri-core manual, that can be downloaded at http://uri.bednarik.org.

Configuration:

- Copy EXT:cooluri/Resources/CoolUriConf.xml_default file to typo3conf/CoolUriConf.xml

- use the same .htaccess as with the RealURL
- install extension
- add to your template setup:

config.baseURL = http://www.example.com/
config.tx_cooluri_enable = 1
config.redirectOldLinksToNew = 1 # if you want to redirect index.php?id=X to a new URI

All configuration is placed in the CoolUriConf.xml. I hope it can be understood even without the manual.

Requirements:

- PHP 5+ with SimpleXML enabled!
- MySQL 4.1+


