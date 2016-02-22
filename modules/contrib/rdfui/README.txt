--------------------------------------------------------------------------------
                        Drupal 8 RDF UI module
--------------------------------------------------------------------------------

CONTENTS
=====================

 * Introduction
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------
The Drupal 8 RDF UI module provides User Interfaces for site builders to
integrate schema.org seamlessly during the site building process.

Content types could be mapped at the point of creation
(admin/structure/types/add) or later (by using the edit form for content type)
by specifying the type to be mapped under the "Schema.org Mappings" menu link.
Fields can similarly be mapped to Schema.org properties at
(admin/structure/types/manage/{entity_type_id}/fields/rdf).

Refer project documentation for more information.


INSTALLATION
------------
The module only depends on modules in Drupal 8 core.

To install RDF UI Module:
  * Place this module directory in your modules folder (this will usually be
    "modules/").
  * Enable the module within your Drupal site at Administation >> Extend
    (admin/modules)


CONFIGURATION
-------------
 * Configure user permissions in Administration » People » Permissions:
   - Content type mapping can only be specified by users with permissions to
     "administer content types".
   - Field mappings can be specified by users who are authorized to "administer
     node_type fields"

MAINTAINERS
-----------
Current maintainers:
 * Sachini Herath - https://www.drupal.org/user/2831117
