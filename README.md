# terminal42/contao-leads

`terminal42/contao-leads` is an extension for the [Contao CMS][Contao].

It allows you to store submissions from the Contao form generator into the database without the need to create tables
for each form. Leads can then be viewed in the back end as well as exported into various formats (e.g. CSV or Excel).

If multiple identical forms exist (e.g. in different languages), lead data can be grouped by the main form
(e.g. your fallback language). This allows to view and export data as if it was just one form.

## Installation

Choose the installation method that matches your workflow!

### Installation via Contao Manager

Search for `terminal42/contao-leads` in the Contao Manager and add it to your installation. Finally, update the
packages.

### Manual installation

Add a composer dependency for this bundle. Therefore, change in the project root and run the following:

```bash
composer require terminal42/contao-leads
```

Depending on your environment, the command can differ, i.e. starting with `php composer.phar …` if you do not have
composer installed globally.


## Configuration

Initial configuration can be done in the form generator of Contao. Do not forget to activate the fields you want to
save! In the form configuration, you can set a label for the back end menu link of your leads and define the listing
of the form data using simple tokens.

To configure exports, first make sure you have at least one form submission. A global operation to configure exports
is then available when viewing the lead data.


### Permissions

For users that are not admins of the system, permissions need to be set up to access leads. Enable access to the leads
back end module and select the forms of which lead data should be available. If the user does not need to configure
the form itself, you don't need to give access to the form generator!

You can additionally configure if a user is allowed to edit or delete existing leads.


## Simple Tokens

[Contao Simple Tokens][SimpleTokens] are used to generate the back end list and as well as for customized exports.
All saved form fields are available by their field name. For example, if you created a text field with name `firstname`, 
you can output its value in the back end list or the export using the `##firstname##` simple token.

Additionally, the following simple tokens are providing data of the lead itself, rather than the data submitted
through the form generator.

<dl>
    <dt>##_id##</dt>
    <dd>Database ID of the lead record.</dd>
    <dt>##_created##</dt>
    <dd>Date and time when the lead was saved (when the form was submitted).</dd>
    <dt>##_form</dt>
    <dd>Database ID (and title) of the form that was submitted.</dd>
    <dt>##_member##</dt>
    <dd>
        ID of the front end member that was logged in  while the form was submitted, or <code>0</code> if no member
        was logged in.
    </dd>
</dl>


## License

This bundle is released under the [LGPL 3.0+ license](LICENSE)


[Contao]: https://contao.org
[SimpleTokens]: https://docs.contao.org/manual/en/article-management/simple-tokens/
