import './leads.scss';
import './images/export.svg';

import { Application, Controller } from '@hotwired/stimulus';

const application = Application.start();
application.debug = process.env.NODE_ENV === 'development';
application.register(
    'terminal42--column-display',
    class extends Controller {
        connect () {
            this.dispatch('change');
        }

        disconnect () {
            this.element.innerHTML = '';
        }

        update () {
            const index = this.element.closest('tr').rowIndex;

            this.element.innerHTML = `<div class="index">${index}</div>
            <div class="excel">${this.#convertIndexToExcelColumn(index)}</div>`;
        }

        #convertIndexToExcelColumn (i) {
            const alpha = parseInt(i / 27, 10);
            const remainder = i - alpha * 26;
            let column = '';

            if (alpha > 0) {
                column = String.fromCharCode(alpha + 64);
            }

            if (remainder > 0) {
                column += String.fromCharCode(remainder + 64);
            }

            return column;
        }
    },
);
