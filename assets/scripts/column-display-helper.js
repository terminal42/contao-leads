
const fetchColumnDisplayElements = (mcw) => {
    return mcw.querySelectorAll('td.column_display');
};

const updateColumnDisplays = (elements) => {
    elements.forEach(function(el, index) {
        const humanReadableIndex = index + 1;

        el.set('html', '<div class="index">' +
            humanReadableIndex +
            '</div>' +
            '<div class="excel">' +
            convertIndexToExcelColumn(humanReadableIndex) +
            '</div>');
    });
};

const convertIndexToExcelColumn = (i) => {
    const alpha = parseInt(i / 27, 10);
    const remainder = i - (alpha * 26);
    let column = '';

    if (alpha > 0) {
        column = String.fromCharCode(alpha + 64);
    }

    if (remainder > 0) {
        column += String.fromCharCode(remainder + 64);
    }

    return column;
};

export default function () {
    const mcws = document.querySelectorAll('table.multicolumnwizard');

    // Cannot use regular click events because of MCW
    const MutationObserver = (function () {
        const prefixes = ['WebKit', 'Moz', 'O', 'Ms', ''];
        for(let i=0; i < prefixes.length; i++) {
            if(prefixes[i] + 'MutationObserver' in window) {
                return window[prefixes[i] + 'MutationObserver'];
            }
        }
        return false;
    }());

    mcws.forEach((mcw) => {
        let elements = fetchColumnDisplayElements(mcw);

        if (MutationObserver) {
            updateColumnDisplays(elements);

            // Register observer
            const observerConfig = {childList: true, subtree: true};
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {

                    if (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0) {
                        observer.disconnect();

                        elements = fetchColumnDisplayElements(mcw);
                        updateColumnDisplays(elements);

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
};
