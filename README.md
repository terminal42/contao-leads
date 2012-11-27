leads
========

This is a Contao 2.11 extension that allows you to store and manage form data within Leads. Each 
Lead consists of a master form and optional slave forms. The master form defines which fields are 
available. This approach helps you e.g. to implement multilingual forms and store all data in the 
same Lead.

All configuration can be done in the form generator from Contao. Additionally you can set a label 
for the backend module of your Lead and define the listing of the form data using simple tags.

The leads extension offers additionally a export function (CSV and Excel) for each Lead in the 
backend.

Upgrading from older versions
---
There is a upgrade script for older versions included. Follow these steps to upgrade existing leads
extensions:
- Make a full backup!
- Remove files from the old version
- Copy the files from the current version
- Execute the Contao installer which will take care of the upgrade

Simple Tags
---
The listing of the form data in the Contao backend can be configured using simple tags, e.g.:
    ##created## - ##name## ##firstname##

Note: there is an additional tag available for the creation date: ##created##