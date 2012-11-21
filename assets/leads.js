var Leads =
{
	initializeExportMenu: function()
	{
		var tools = document.getElements('#tl_buttons .leads-export');

		if (tools.length < 1)
			return;

		// Remove the separators between each button
		tools.each(function(node) {
			node.previousSibling.nodeValue = '';
		});

		// Add trigger to tools buttons
		document.getElement('a.header_leads_export').addEvent('click', function(e)
		{
			document.id('leadsexportmenu').setStyle('display', 'block');
			return false;
		})
		.setStyle('display', 'inline');

		var div = new Element('div',
		{
			'id': 'leadsexportmenu',
			'styles': {
				'top': ($$('a.header_leads_export')[0].getPosition().y + 22)
			}
		})
		.adopt(tools)
		.inject(document.id(document.body))
		.setStyle('left', $$('a.header_leads_export')[0].getPosition().x - 7);

		// Hide context menu
		document.id(document.body).addEvent('click', function()
		{
			document.id('leadsexportmenu').setStyle('display', 'none');
		});
	}
};

window.addEvent('domready', function()
{
	Leads.initializeExportMenu();
});

