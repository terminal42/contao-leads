leads
========

This is a Contao extension that allows you to store and manage form data within Leads. Each
Lead consists of a master form and optional slave forms. The master form defines which fields are 
available. This approach helps you e.g. to implement multilingual forms and store all data in the 
same Lead.

All configuration can be done in the form generator from Contao. Additionally you can set a label 
for the backend module of your Lead and define the listing of the form data using simple tags.

The leads extension additionally offers an export function for each Lead in the backend. You can 
configure it as you wish! Export options for CSV and Excel are available. _However_ you need to 
install the `phpoffice/phpexcel` package for Excel support, otherwise the Excel export will not be 
available. 

Simple Tokens
---
The listing of the form data in the Contao backend can be configured using simple tokens, e.g.:
    ##created## - ##name## ##firstname##

Note: there is an additional tag available for the creation date: ##created##
