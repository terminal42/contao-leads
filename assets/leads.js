var Leads = {

    initializeExportMenu: function() {
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

        var div = new Element('div', {'id': 'leadsexportmenu'})
        .adopt(tools)
        .inject(document.id(document.body))
        .setStyles({
            'top': ($$('a.header_leads_export')[0].getPosition().y + 22),
            'left': ($$('a.header_leads_export')[0].getPosition().x - 7)
        });

        // Hide context menu
        document.id(document.body).addEvent('click', function()
        {
            document.id('leadsexportmenu').setStyle('display', 'none');
        });
    },

    initializeColumnDisplayHelper: function() {
        var mcws = document.getElements('table.multicolumnwizard');
        var self = this;

        // Cannot use regular click events because MCW is shit.
        var MutationObserver = (function () {
            var prefixes = ['WebKit', 'Moz', 'O', 'Ms', ''];
            for(var i=0; i < prefixes.length; i++) {
                if(prefixes[i] + 'MutationObserver' in window) {
                    return window[prefixes[i] + 'MutationObserver'];
                }
            }
            return false;
        }());

        mcws.forEach(function(mcw) {
            var elements = self.fetchColumnDisplayElements(mcw);

            if (MutationObserver) {
                self.updateColumnDisplays(elements);

                // Register observer
                var observerConfig = {childList: true, subtree: true};
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {

                        if (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0) {

                            observer.disconnect();

                            elements = self.fetchColumnDisplayElements(mcw);
                            self.updateColumnDisplays(elements);

                            observer.observe(mcw, observerConfig);
                        }
                    });
                });

                observer.observe(mcw, observerConfig);

            } else {
                elements.forEach(function(el) {
                    el.set('html', '');
                });
            }
        });
    },

    fetchColumnDisplayElements: function(mcw) {
        return mcw.getElements('td.column_display');
    },

    updateColumnDisplays: function(elements) {
        var self = this;

        elements.forEach(function(el, index) {
            var humanReadableIndex = index + 1;

            el.set('html', '<div class="index">' +
                humanReadableIndex +
                '</div>' +
                '<div class="excel">' +
                self.convertIndexToExcelColumn(humanReadableIndex) +
                '</div>');
        });
    },

    convertIndexToExcelColumn: function(i) {
        var alpha = parseInt(i / 27, 10);
        var remainder = i - (alpha * 26);
        var column = '';

        if (alpha > 0) {
            column = String.fromCharCode(alpha + 64);
        }

        if (remainder > 0) {
            column += String.fromCharCode(remainder + 64);
        }

        return column;
    }
};

window.addEvent('load', function()
{
    Leads.initializeExportMenu();
    Leads.initializeColumnDisplayHelper();
});

