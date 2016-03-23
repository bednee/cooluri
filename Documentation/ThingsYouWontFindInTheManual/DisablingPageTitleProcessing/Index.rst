.. include:: ../../Includes.txt



.. _disabling-page-title-processing:

disabling page title processing
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you need a page title to stay exact the same as you have set it,
you need to suppress the default page title processing from non-url-
like form to url-like (I call this urlizing). To do this, you need to
set subelement of the pagepath element urlize to 0.

::

   <pagepath>
   ....
   <urlize>0</urlize>
   </pagepath>

