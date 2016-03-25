.. include:: ../../Includes.txt



.. _exclude-condition-for-suffix:

exclude condition for suffix
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Element urlsuffix now has optional attribute  **exclude** and
**include** . Element removetrailingslash has just **include** .
Values of these attributes are passed to preg\_match function. Anyway,
one example is better than hundred words and three examples are even
better:


.. _Example-1:

Example 1
"""""""""

Let's say you want to

1) remove / from links that end with ".xml"

2) append ".html" to links that end with "foo" (without slash)

3) other links with slash

Then the config would be:

::

   <urlsuffix include="foo$" exclude=".xml$">.html</urlsuffix> 
   <removetrailingslash include="(.xml|foo)$">1</removetrailingslash>


.. _Example-2:

Example 2
"""""""""

Simple example - don't append slash to links ending with .xml and
append .html to other:

::

   <urlsuffix exclude=".xml$">.html</urlsuffix> 
   <removetrailingslash>1</removetrailingslash> 


.. _Example-3:

Example 3
"""""""""

Another - leave slash except when link ends with .xml:

::

   <removetrailingslash include=".xml$">1</removetrailingslash> 

