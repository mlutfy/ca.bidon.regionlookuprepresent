Open North Represent integration for CiviCRM Region Lookup
==========================================================

Lookup and complete form fields from a given key (ex: postcode) using the Open North Represent API.

Written and maintained by (C) Mathieu Lutfy, 2015-2017  
https://www.symbiotic.coop/en

To get the latest version of this extension:  
https://github.com/mlutfy/ca.bidon.regionlookuprepresent

Distributed under the terms of the GNU Affero General public license (AGPL).
See LICENSE.txt for details.

This extension was sponsored by:  
Projet Montréal <http://projetmontreal.org>  
Coop SymbioTIC <https://www.symbiotic.coop/en>

Features
========

Given a postcode, this extension will return the city district, bourough and federal riding.

For more advanced requirements, see: https://www.drupal.org/project/civinorth

Experimental features
=====================

The following APIs have hardcoded values and a lot of "FIXME" tags in the
code. They are highly experimental and should not be relied upon. They are
available so that other developers can improve them. If you are a developer,
please file an issue and let's discuss how we can make the code more flexible.
You can also contract Coop SymbioTIC to adapt this code for your organisation's
needs.

`Regionlookuprepresent.Updatefederalridings contact_sub_type=Federal_riding`

This API will import/update a list of federal ridings into CiviCRM. The
`contact_sub_type` must be specified. The are a lot of odd things about this
import: it will import the Party name as a string (instead of an indexed list)
and it will import the MP name in custom fields (instead of creating a separate
individual contact).

`Regionlookuprepresent.Ridinglookup`

Assuming the contacts have a "federal riding" custom field (of type "contact
reference") and a known postal address, this API call will lookup their
(federal) riding in Represent (using either their geolocation).

Requirements
============

- CiviCRM >= 4.7 (latest CiviCRM version recommended)
- latest version of ca.bidon.regionlookup (https://github.com/mlutfy/ca.bidon.regionlookup)

Installation
============

1- Download this extension and unpack it in your 'extensions' directory.
   You may need to create it if it does not already exist, and configure
   the correct path in CiviCRM -> Administer -> System -> Directories.

2- Enable the extension from CiviCRM -> Administer -> System -> Extensions.

3- In the Region Lookup settings (CiviCRM -> Administer -> System -> Region Lookup), select the 'Represent' lookup method.

Support
=======

Please post bug reports, patches and support requests in the issue tracker of this project on github:  
https://github.com/mlutfy/ca.bidon.regionlookuprepresent/issues

Please keep in mind that I have very limited time for free support.

This is a community contributed extension written thanks to the financial
support of organisations using it, as well as the very helpful and collaborative
CiviCRM community.

If you appreciate this module, please consider supporting the CiviCRM project:

* https://civicrm.org/participate/support-civicrm
* https://civicrm.org/membership
* https://civicrm.org/become-a-partner

If you are a member or a partner, please do mention it when posting in the issue queue.

Commercial support is available from Coop SymbioTIC:  
https://www.symbiotic.coop/en  
info@symbiotic.coop

License
=======

(C) 2015-2017 Mathieu Lutfy <mathieu@symbiotic.coop>  
(C) 2015-2017 Coop SymbioTIC <info@symbiotic.coop>

Distributed under the terms of the GNU Affero General public license (AGPL).
See LICENSE.txt for details.
