CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Included Modules and Features
 * Recommended Modules
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

This module enables site builders creating dynamic QR code fields which
can contain content based on a content entity they are attached to.


REQUIREMENTS
------------

Token - Provides additional tokens not supported by core (most notably fields),
as well as a UI for browsing tokens.

  - https://www.drupal.org/project/token


INCLUDED MODULES AND FEATURES
-----------------------------

This module does not include any additional modules / features.


RECOMMENDED MODULES
-------------------

Multiple field remove button - Drupal core multi value field have
no option to remove items, this module adds button to remove them.

  - https://www.drupal.org/project/multiple_fields_remove_button

INSTALLATION
------------

 - Install the Devel module as you would normally install a contributed Drupal
   module. Visit https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

Field type (qrfield): Add QRField configuration field to you content type
and configure field setting such as storage, form display, display
(e.g.: /admin/structure/types/manage/page/fields/node.page.field_qrfield/storage).


Block: Place QRField block to your site region in the block admin page
/admin/structure/block.


MAINTAINERS
-----------

  - Borut Piletic (borutpiletic) - https://www.drupal.org/u/borutpiletic
