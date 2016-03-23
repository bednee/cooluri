.. include:: ../Includes.txt



.. _cHash-issue:

cHash issue
-----------

It's an ugly parameter, that needs to be removed from URLs. To remove
cHash, you have to put

::

   <part>
         <parameter>cHash</parameter>
   </part>


into predefinedparts. But it can cause trouble, when it's not used
right. The easiest way is to make an extension COA\_INT and you don't
have to care about the rest. But if you care about performance, this
is not the best way to go.

The crucial thing is that ALL parameters need to be translated into
"cool" form. I'll explain the reason. Let's say that there are 2
URLs:

`id=10&par=foo&cHash=abc`

`id=10&par=bar&cHash=xyz`

now, the "par" is not translated, "id" is (as it's page id) and
"cHash" is removed. Then the cache is at first filled with this
double:

`id=10&par=foo&cHash=abc || /page/path/`

as you can see, the "par" is not cached, because it hasn't been
translated. Now, the second URL is generated and since the "cool part"
is again only "/page/path", no new value is created in the cache,
because the "/page/path" value is already in the database.

Now, if a request for "/page/page/?par=bar" comes, first double is
found and cHash that belongs to par=abc is fetched. And that's
obviously wrong.

So, to make cHash work as expected, all parameters need to be
translated into the cool form. That means, no ? in any URL.


