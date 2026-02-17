import './styles/leads.scss';
import './images/data.svg';
import './images/export.svg';

import ColumnDisplayHelper from './scripts/column-display-helper';

document.addEventListener('DOMContentLoaded', ColumnDisplayHelper);
document.addEventListener('turbo:load', ColumnDisplayHelper);
document.addEventListener('turbo:render', ColumnDisplayHelper);

document.addEventListener('turbo:before-cache', () => {
    document.querySelectorAll('table.multicolumnwizard').forEach((mcw) => {
        if (mcw._leadsColumnDisplayHelperObserver) {
            mcw._leadsColumnDisplayHelperObserver.disconnect();
            mcw._leadsColumnDisplayHelperObserver = null;
        }

        mcw._leadsColumnDisplayHelperInitialized = false;
    });
});
