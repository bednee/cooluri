.. include:: ../Includes.txt



.. _Users-manual:

Users manual
------------

#. Default configuration file is placed in
   EXT:cooluri/coouri/CoolUriConf.xml\_default. You have to copy it
   somewhere, remove “\_default” from the extension and specify path to
   it in the extension's setup.

#. Use the same .htaccess as you'd with the RealURL (that means,
   redirect everything to index.php):

   ::

      RewriteEngine On
      RewriteRule ^/(typo3|typo3temp|typo3conf|t3lib|tslib|fileadmin|uploads|showpic\.php)$ - [L]
      RewriteRule ^/(typo3|typo3temp|typo3conf|t3lib|tslib|fileadmin|uploads|showpic\.php)/.*$ - [L]
      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteCond %{REQUEST_FILENAME} !-d
      RewriteCond %{REQUEST_FILENAME} !-l
      RewriteRule .* index.php

#. Add this to your template:

   ::

      config.baseURL = http://www.example.com/
      config.tx_cooluri_enable = 1
      config.redirectOldLinksToNew = 1 # if you want to redirect index.php?id=X to a new URI


.. toctree::

   MultidomainSupport/Index
   MigratingFromRealurl/Index
   InstallingCooluriWithDbalExtension/Index

