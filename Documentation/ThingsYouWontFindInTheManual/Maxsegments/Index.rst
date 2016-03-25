.. include:: ../../Includes.txt



.. _maxsegments:

maxsegments
^^^^^^^^^^^

You can limit number of segment in pagepath using element maxsegments
– subelement of pagepath element.

::

   <pagepath>
       <title>tx_realurl_pathsegment, alias,subtitle,title</title>
       <saveto>id</saveto>
       <default>0</default>    
       <userfunc>tx_cooluri->getPageTitle</userfunc>
       <maxsegments>1</maxsegments>
   </pagepath>

