!function(){"use strict";var e={p:"/bundles/terminal42leads/"};e.p,e.p;const t=e=>e.querySelectorAll("td.column_display"),n=e=>{e.forEach(((e,t)=>{const n=t+1;e.set("html",`\n        <div class="index">${n}</div>\n        <div class="excel">${(e=>{const t=parseInt(e/27,10),n=e-26*t;let o="";return t>0&&(o=String.fromCharCode(t+64)),n>0&&(o+=String.fromCharCode(n+64)),o})(n)}</div>`)}))};document.addEventListener("DOMContentLoaded",(()=>{!function(){const e=document.querySelectorAll("table.multicolumnwizard"),o=function(){const e=["WebKit","Moz","O","Ms",""];for(let t=0;t<e.length;t+=1)if(`${e[t]}MutationObserver`in window)return window[`${e[t]}MutationObserver`];return!1}();e.forEach((e=>{let r=t(e);if(o){n(r);const s={childList:!0,subtree:!0},d=new o((o=>{o.forEach((o=>{(o.addedNodes.length>0||o.removedNodes.length>0)&&(d.disconnect(),r=t(e),n(r),d.observe(e,s))}))}));d.observe(e,s)}else r.forEach((e=>{e.set("html","")}))}))}()}))}();
//# sourceMappingURL=leads.e4242972.js.map