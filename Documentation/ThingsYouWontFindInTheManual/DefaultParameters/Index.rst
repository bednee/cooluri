.. include:: ../../Includes.txt



.. _Default-parameters:

Default parameters
^^^^^^^^^^^^^^^^^^

There's a fallback for parameter value when it's not set. It's called
“defaults” and basically it works as expected. When a parameter is not
present in query string, it uses this defaults value, otherwise,
standard translation is perfomed. Defaults are treated like regular
parameters and are by default appended to the end of the URLs (that
means they don't occupy position of the parameters they represent).
Anyway, this can be changed either by “partorder”, where there is a
new keyword “defaults” or “paramorder”.

Real life example – to have a default languge in URLs, add this to
your configuration:

::

   <defaults>
       <value key="L">de</value>
   </defaults>

And that's it. Now, when there is no L paramter in query string of a
URL, “de” will be always present in the place where it should be
(because of paramorder, which puts L as the first parameter).

