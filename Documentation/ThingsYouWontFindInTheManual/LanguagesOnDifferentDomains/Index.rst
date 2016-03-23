.. include:: ../../Includes.txt



.. _Languages-on-different-domains:

Languages on different domains
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Let's say you have multilanguage site with English as the default
language and Czech (or whatever) as the second language. With the
default setup, you'd have example.com and example.com/cz. But what if
you want the second language on another domain, in this case
example.cz. Here's how to do it. At first, let's assume you have the
default setup. These are the steps you have to take to migrate from
/cz to example.cz.

#. In your TypoScript change `[globalVar = GP:L=1]` to `[globalString = IENV:HTTP_HOST = *example.cz]`

#. Addconfig.baseURL = `http://www.example.cz/` into the condition section

#. Remove language valuemap from XML configuration

#. Since L parameter is still internally used, make sure it will be
   removed from URL by putting it into **predefinedparts**

   ::

      <part>
          <parameter>L</parameter>
      </part>

#. Add the following into the configuration

   ::

      <domainlanguages>
          <domain lang="0">www.example.com</domain>
          <domain lang="1">www.example.cz</domain>
      </domainlanguages>

#. Now just delete cached links and cache and you're done.

