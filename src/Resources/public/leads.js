/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/bundles/terminal42leads/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/leads.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/leads.css":
/*!**************************!*\
  !*** ./assets/leads.css ***!
  \**************************/
/*! dynamic exports provided */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./assets/leads.js":
/*!*************************!*\
  !*** ./assets/leads.js ***!
  \*************************/
/*! no exports provided */
/*! all exports used */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__leads_css__ = __webpack_require__(/*! ./leads.css */ "./assets/leads.css");
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__leads_css___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__leads_css__);


var Leads = {

    initializeColumnDisplayHelper: function initializeColumnDisplayHelper() {
        var mcws = document.getElements('table.multicolumnwizard');
        var self = this;

        // Cannot use regular click events because of MCW
        var MutationObserver = function () {
            var prefixes = ['WebKit', 'Moz', 'O', 'Ms', ''];
            for (var i = 0; i < prefixes.length; i++) {
                if (prefixes[i] + 'MutationObserver' in window) {
                    return window[prefixes[i] + 'MutationObserver'];
                }
            }
            return false;
        }();

        mcws.forEach(function (mcw) {
            var elements = self.fetchColumnDisplayElements(mcw);

            if (MutationObserver) {
                self.updateColumnDisplays(elements);

                // Register observer
                var observerConfig = { childList: true, subtree: true };
                var observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {

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
                elements.forEach(function (el) {
                    el.set('html', '');
                });
            }
        });
    },

    fetchColumnDisplayElements: function fetchColumnDisplayElements(mcw) {
        return mcw.getElements('td.column_display');
    },

    updateColumnDisplays: function updateColumnDisplays(elements) {
        var self = this;

        elements.forEach(function (el, index) {
            var humanReadableIndex = index + 1;

            el.set('html', '<div class="index">' + humanReadableIndex + '</div>' + '<div class="excel">' + self.convertIndexToExcelColumn(humanReadableIndex) + '</div>');
        });
    },

    convertIndexToExcelColumn: function convertIndexToExcelColumn(i) {
        var alpha = parseInt(i / 27, 10);
        var remainder = i - alpha * 26;
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

window.addEvent('load', function () {
    Leads.initializeColumnDisplayHelper();
});

/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAgOTI3MDU0NjA3YWRkZmNjNzBkNDUiLCJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2xlYWRzLmNzcz85NDc0Iiwid2VicGFjazovLy8uL2Fzc2V0cy9sZWFkcy5qcyJdLCJuYW1lcyI6WyJMZWFkcyIsImluaXRpYWxpemVDb2x1bW5EaXNwbGF5SGVscGVyIiwibWN3cyIsImRvY3VtZW50IiwiZ2V0RWxlbWVudHMiLCJzZWxmIiwiTXV0YXRpb25PYnNlcnZlciIsInByZWZpeGVzIiwiaSIsImxlbmd0aCIsIndpbmRvdyIsImZvckVhY2giLCJtY3ciLCJlbGVtZW50cyIsImZldGNoQ29sdW1uRGlzcGxheUVsZW1lbnRzIiwidXBkYXRlQ29sdW1uRGlzcGxheXMiLCJvYnNlcnZlckNvbmZpZyIsImNoaWxkTGlzdCIsInN1YnRyZWUiLCJvYnNlcnZlciIsIm11dGF0aW9ucyIsIm11dGF0aW9uIiwiYWRkZWROb2RlcyIsInJlbW92ZWROb2RlcyIsImRpc2Nvbm5lY3QiLCJvYnNlcnZlIiwiZWwiLCJzZXQiLCJpbmRleCIsImh1bWFuUmVhZGFibGVJbmRleCIsImNvbnZlcnRJbmRleFRvRXhjZWxDb2x1bW4iLCJhbHBoYSIsInBhcnNlSW50IiwicmVtYWluZGVyIiwiY29sdW1uIiwiU3RyaW5nIiwiZnJvbUNoYXJDb2RlIiwiYWRkRXZlbnQiXSwibWFwcGluZ3MiOiI7QUFBQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7O0FBR0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsYUFBSztBQUNMO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsbUNBQTJCLDBCQUEwQixFQUFFO0FBQ3ZELHlDQUFpQyxlQUFlO0FBQ2hEO0FBQ0E7QUFDQTs7QUFFQTtBQUNBLDhEQUFzRCwrREFBK0Q7O0FBRXJIO0FBQ0E7O0FBRUE7QUFDQTs7Ozs7Ozs7Ozs7O0FDN0RBLHlDOzs7Ozs7Ozs7Ozs7O0FDQUE7QUFBQTtBQUFBO0FBQUE7O0FBRUEsSUFBSUEsUUFBUTs7QUFFUkMsbUNBQStCLHlDQUFXO0FBQ3RDLFlBQUlDLE9BQU9DLFNBQVNDLFdBQVQsQ0FBcUIseUJBQXJCLENBQVg7QUFDQSxZQUFJQyxPQUFPLElBQVg7O0FBRUE7QUFDQSxZQUFJQyxtQkFBb0IsWUFBWTtBQUNoQyxnQkFBSUMsV0FBVyxDQUFDLFFBQUQsRUFBVyxLQUFYLEVBQWtCLEdBQWxCLEVBQXVCLElBQXZCLEVBQTZCLEVBQTdCLENBQWY7QUFDQSxpQkFBSSxJQUFJQyxJQUFFLENBQVYsRUFBYUEsSUFBSUQsU0FBU0UsTUFBMUIsRUFBa0NELEdBQWxDLEVBQXVDO0FBQ25DLG9CQUFHRCxTQUFTQyxDQUFULElBQWMsa0JBQWQsSUFBb0NFLE1BQXZDLEVBQStDO0FBQzNDLDJCQUFPQSxPQUFPSCxTQUFTQyxDQUFULElBQWMsa0JBQXJCLENBQVA7QUFDSDtBQUNKO0FBQ0QsbUJBQU8sS0FBUDtBQUNILFNBUnVCLEVBQXhCOztBQVVBTixhQUFLUyxPQUFMLENBQWEsVUFBU0MsR0FBVCxFQUFjO0FBQ3ZCLGdCQUFJQyxXQUFXUixLQUFLUywwQkFBTCxDQUFnQ0YsR0FBaEMsQ0FBZjs7QUFFQSxnQkFBSU4sZ0JBQUosRUFBc0I7QUFDbEJELHFCQUFLVSxvQkFBTCxDQUEwQkYsUUFBMUI7O0FBRUE7QUFDQSxvQkFBSUcsaUJBQWlCLEVBQUNDLFdBQVcsSUFBWixFQUFrQkMsU0FBUyxJQUEzQixFQUFyQjtBQUNBLG9CQUFJQyxXQUFXLElBQUliLGdCQUFKLENBQXFCLFVBQVNjLFNBQVQsRUFBb0I7QUFDcERBLDhCQUFVVCxPQUFWLENBQWtCLFVBQVNVLFFBQVQsRUFBbUI7O0FBRWpDLDRCQUFJQSxTQUFTQyxVQUFULENBQW9CYixNQUFwQixHQUE2QixDQUE3QixJQUFrQ1ksU0FBU0UsWUFBVCxDQUFzQmQsTUFBdEIsR0FBK0IsQ0FBckUsRUFBd0U7O0FBRXBFVSxxQ0FBU0ssVUFBVDs7QUFFQVgsdUNBQVdSLEtBQUtTLDBCQUFMLENBQWdDRixHQUFoQyxDQUFYO0FBQ0FQLGlDQUFLVSxvQkFBTCxDQUEwQkYsUUFBMUI7O0FBRUFNLHFDQUFTTSxPQUFULENBQWlCYixHQUFqQixFQUFzQkksY0FBdEI7QUFDSDtBQUNKLHFCQVhEO0FBWUgsaUJBYmMsQ0FBZjs7QUFlQUcseUJBQVNNLE9BQVQsQ0FBaUJiLEdBQWpCLEVBQXNCSSxjQUF0QjtBQUVILGFBdEJELE1Bc0JPO0FBQ0hILHlCQUFTRixPQUFULENBQWlCLFVBQVNlLEVBQVQsRUFBYTtBQUMxQkEsdUJBQUdDLEdBQUgsQ0FBTyxNQUFQLEVBQWUsRUFBZjtBQUNILGlCQUZEO0FBR0g7QUFDSixTQTlCRDtBQStCSCxLQWhETzs7QUFrRFJiLGdDQUE0QixvQ0FBU0YsR0FBVCxFQUFjO0FBQ3RDLGVBQU9BLElBQUlSLFdBQUosQ0FBZ0IsbUJBQWhCLENBQVA7QUFDSCxLQXBETzs7QUFzRFJXLDBCQUFzQiw4QkFBU0YsUUFBVCxFQUFtQjtBQUNyQyxZQUFJUixPQUFPLElBQVg7O0FBRUFRLGlCQUFTRixPQUFULENBQWlCLFVBQVNlLEVBQVQsRUFBYUUsS0FBYixFQUFvQjtBQUNqQyxnQkFBSUMscUJBQXFCRCxRQUFRLENBQWpDOztBQUVBRixlQUFHQyxHQUFILENBQU8sTUFBUCxFQUFlLHdCQUNYRSxrQkFEVyxHQUVYLFFBRlcsR0FHWCxxQkFIVyxHQUlYeEIsS0FBS3lCLHlCQUFMLENBQStCRCxrQkFBL0IsQ0FKVyxHQUtYLFFBTEo7QUFNSCxTQVREO0FBVUgsS0FuRU87O0FBcUVSQywrQkFBMkIsbUNBQVN0QixDQUFULEVBQVk7QUFDbkMsWUFBSXVCLFFBQVFDLFNBQVN4QixJQUFJLEVBQWIsRUFBaUIsRUFBakIsQ0FBWjtBQUNBLFlBQUl5QixZQUFZekIsSUFBS3VCLFFBQVEsRUFBN0I7QUFDQSxZQUFJRyxTQUFTLEVBQWI7O0FBRUEsWUFBSUgsUUFBUSxDQUFaLEVBQWU7QUFDWEcscUJBQVNDLE9BQU9DLFlBQVAsQ0FBb0JMLFFBQVEsRUFBNUIsQ0FBVDtBQUNIOztBQUVELFlBQUlFLFlBQVksQ0FBaEIsRUFBbUI7QUFDZkMsc0JBQVVDLE9BQU9DLFlBQVAsQ0FBb0JILFlBQVksRUFBaEMsQ0FBVjtBQUNIOztBQUVELGVBQU9DLE1BQVA7QUFDSDtBQW5GTyxDQUFaOztBQXNGQXhCLE9BQU8yQixRQUFQLENBQWdCLE1BQWhCLEVBQXdCLFlBQ3hCO0FBQ0lyQyxVQUFNQyw2QkFBTjtBQUNILENBSEQsRSIsImZpbGUiOiJsZWFkcy5qcyIsInNvdXJjZXNDb250ZW50IjpbIiBcdC8vIFRoZSBtb2R1bGUgY2FjaGVcbiBcdHZhciBpbnN0YWxsZWRNb2R1bGVzID0ge307XG5cbiBcdC8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG4gXHRmdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cbiBcdFx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG4gXHRcdGlmKGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdKSB7XG4gXHRcdFx0cmV0dXJuIGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdLmV4cG9ydHM7XG4gXHRcdH1cbiBcdFx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcbiBcdFx0dmFyIG1vZHVsZSA9IGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdID0ge1xuIFx0XHRcdGk6IG1vZHVsZUlkLFxuIFx0XHRcdGw6IGZhbHNlLFxuIFx0XHRcdGV4cG9ydHM6IHt9XG4gXHRcdH07XG5cbiBcdFx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG4gXHRcdG1vZHVsZXNbbW9kdWxlSWRdLmNhbGwobW9kdWxlLmV4cG9ydHMsIG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG4gXHRcdC8vIEZsYWcgdGhlIG1vZHVsZSBhcyBsb2FkZWRcbiBcdFx0bW9kdWxlLmwgPSB0cnVlO1xuXG4gXHRcdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG4gXHRcdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbiBcdH1cblxuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZXMgb2JqZWN0IChfX3dlYnBhY2tfbW9kdWxlc19fKVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5tID0gbW9kdWxlcztcblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGUgY2FjaGVcbiBcdF9fd2VicGFja19yZXF1aXJlX18uYyA9IGluc3RhbGxlZE1vZHVsZXM7XG5cbiBcdC8vIGRlZmluZSBnZXR0ZXIgZnVuY3Rpb24gZm9yIGhhcm1vbnkgZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kID0gZnVuY3Rpb24oZXhwb3J0cywgbmFtZSwgZ2V0dGVyKSB7XG4gXHRcdGlmKCFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywgbmFtZSkpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgbmFtZSwge1xuIFx0XHRcdFx0Y29uZmlndXJhYmxlOiBmYWxzZSxcbiBcdFx0XHRcdGVudW1lcmFibGU6IHRydWUsXG4gXHRcdFx0XHRnZXQ6IGdldHRlclxuIFx0XHRcdH0pO1xuIFx0XHR9XG4gXHR9O1xuXG4gXHQvLyBnZXREZWZhdWx0RXhwb3J0IGZ1bmN0aW9uIGZvciBjb21wYXRpYmlsaXR5IHdpdGggbm9uLWhhcm1vbnkgbW9kdWxlc1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5uID0gZnVuY3Rpb24obW9kdWxlKSB7XG4gXHRcdHZhciBnZXR0ZXIgPSBtb2R1bGUgJiYgbW9kdWxlLl9fZXNNb2R1bGUgP1xuIFx0XHRcdGZ1bmN0aW9uIGdldERlZmF1bHQoKSB7IHJldHVybiBtb2R1bGVbJ2RlZmF1bHQnXTsgfSA6XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0TW9kdWxlRXhwb3J0cygpIHsgcmV0dXJuIG1vZHVsZTsgfTtcbiBcdFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kKGdldHRlciwgJ2EnLCBnZXR0ZXIpO1xuIFx0XHRyZXR1cm4gZ2V0dGVyO1xuIFx0fTtcblxuIFx0Ly8gT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm8gPSBmdW5jdGlvbihvYmplY3QsIHByb3BlcnR5KSB7IHJldHVybiBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwob2JqZWN0LCBwcm9wZXJ0eSk7IH07XG5cbiBcdC8vIF9fd2VicGFja19wdWJsaWNfcGF0aF9fXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnAgPSBcIi9idW5kbGVzL3Rlcm1pbmFsNDJsZWFkcy9cIjtcblxuIFx0Ly8gTG9hZCBlbnRyeSBtb2R1bGUgYW5kIHJldHVybiBleHBvcnRzXG4gXHRyZXR1cm4gX193ZWJwYWNrX3JlcXVpcmVfXyhfX3dlYnBhY2tfcmVxdWlyZV9fLnMgPSBcIi4vYXNzZXRzL2xlYWRzLmpzXCIpO1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIHdlYnBhY2svYm9vdHN0cmFwIDkyNzA1NDYwN2FkZGZjYzcwZDQ1IiwiLy8gcmVtb3ZlZCBieSBleHRyYWN0LXRleHQtd2VicGFjay1wbHVnaW5cblxuXG4vLy8vLy8vLy8vLy8vLy8vLy9cbi8vIFdFQlBBQ0sgRk9PVEVSXG4vLyAuL2Fzc2V0cy9sZWFkcy5jc3Ncbi8vIG1vZHVsZSBpZCA9IC4vYXNzZXRzL2xlYWRzLmNzc1xuLy8gbW9kdWxlIGNodW5rcyA9IDAiLCJpbXBvcnQgJy4vbGVhZHMuY3NzJztcblxudmFyIExlYWRzID0ge1xuXG4gICAgaW5pdGlhbGl6ZUNvbHVtbkRpc3BsYXlIZWxwZXI6IGZ1bmN0aW9uKCkge1xuICAgICAgICB2YXIgbWN3cyA9IGRvY3VtZW50LmdldEVsZW1lbnRzKCd0YWJsZS5tdWx0aWNvbHVtbndpemFyZCcpO1xuICAgICAgICB2YXIgc2VsZiA9IHRoaXM7XG5cbiAgICAgICAgLy8gQ2Fubm90IHVzZSByZWd1bGFyIGNsaWNrIGV2ZW50cyBiZWNhdXNlIG9mIE1DV1xuICAgICAgICB2YXIgTXV0YXRpb25PYnNlcnZlciA9IChmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICB2YXIgcHJlZml4ZXMgPSBbJ1dlYktpdCcsICdNb3onLCAnTycsICdNcycsICcnXTtcbiAgICAgICAgICAgIGZvcih2YXIgaT0wOyBpIDwgcHJlZml4ZXMubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgICAgICAgICBpZihwcmVmaXhlc1tpXSArICdNdXRhdGlvbk9ic2VydmVyJyBpbiB3aW5kb3cpIHtcbiAgICAgICAgICAgICAgICAgICAgcmV0dXJuIHdpbmRvd1twcmVmaXhlc1tpXSArICdNdXRhdGlvbk9ic2VydmVyJ107XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9KCkpO1xuXG4gICAgICAgIG1jd3MuZm9yRWFjaChmdW5jdGlvbihtY3cpIHtcbiAgICAgICAgICAgIHZhciBlbGVtZW50cyA9IHNlbGYuZmV0Y2hDb2x1bW5EaXNwbGF5RWxlbWVudHMobWN3KTtcblxuICAgICAgICAgICAgaWYgKE11dGF0aW9uT2JzZXJ2ZXIpIHtcbiAgICAgICAgICAgICAgICBzZWxmLnVwZGF0ZUNvbHVtbkRpc3BsYXlzKGVsZW1lbnRzKTtcblxuICAgICAgICAgICAgICAgIC8vIFJlZ2lzdGVyIG9ic2VydmVyXG4gICAgICAgICAgICAgICAgdmFyIG9ic2VydmVyQ29uZmlnID0ge2NoaWxkTGlzdDogdHJ1ZSwgc3VidHJlZTogdHJ1ZX07XG4gICAgICAgICAgICAgICAgdmFyIG9ic2VydmVyID0gbmV3IE11dGF0aW9uT2JzZXJ2ZXIoZnVuY3Rpb24obXV0YXRpb25zKSB7XG4gICAgICAgICAgICAgICAgICAgIG11dGF0aW9ucy5mb3JFYWNoKGZ1bmN0aW9uKG11dGF0aW9uKSB7XG5cbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChtdXRhdGlvbi5hZGRlZE5vZGVzLmxlbmd0aCA+IDAgfHwgbXV0YXRpb24ucmVtb3ZlZE5vZGVzLmxlbmd0aCA+IDApIHtcblxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9ic2VydmVyLmRpc2Nvbm5lY3QoKTtcblxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGVsZW1lbnRzID0gc2VsZi5mZXRjaENvbHVtbkRpc3BsYXlFbGVtZW50cyhtY3cpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNlbGYudXBkYXRlQ29sdW1uRGlzcGxheXMoZWxlbWVudHMpO1xuXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgb2JzZXJ2ZXIub2JzZXJ2ZShtY3csIG9ic2VydmVyQ29uZmlnKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgfSk7XG5cbiAgICAgICAgICAgICAgICBvYnNlcnZlci5vYnNlcnZlKG1jdywgb2JzZXJ2ZXJDb25maWcpO1xuXG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIGVsZW1lbnRzLmZvckVhY2goZnVuY3Rpb24oZWwpIHtcbiAgICAgICAgICAgICAgICAgICAgZWwuc2V0KCdodG1sJywgJycpO1xuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9LFxuXG4gICAgZmV0Y2hDb2x1bW5EaXNwbGF5RWxlbWVudHM6IGZ1bmN0aW9uKG1jdykge1xuICAgICAgICByZXR1cm4gbWN3LmdldEVsZW1lbnRzKCd0ZC5jb2x1bW5fZGlzcGxheScpO1xuICAgIH0sXG5cbiAgICB1cGRhdGVDb2x1bW5EaXNwbGF5czogZnVuY3Rpb24oZWxlbWVudHMpIHtcbiAgICAgICAgdmFyIHNlbGYgPSB0aGlzO1xuXG4gICAgICAgIGVsZW1lbnRzLmZvckVhY2goZnVuY3Rpb24oZWwsIGluZGV4KSB7XG4gICAgICAgICAgICB2YXIgaHVtYW5SZWFkYWJsZUluZGV4ID0gaW5kZXggKyAxO1xuXG4gICAgICAgICAgICBlbC5zZXQoJ2h0bWwnLCAnPGRpdiBjbGFzcz1cImluZGV4XCI+JyArXG4gICAgICAgICAgICAgICAgaHVtYW5SZWFkYWJsZUluZGV4ICtcbiAgICAgICAgICAgICAgICAnPC9kaXY+JyArXG4gICAgICAgICAgICAgICAgJzxkaXYgY2xhc3M9XCJleGNlbFwiPicgK1xuICAgICAgICAgICAgICAgIHNlbGYuY29udmVydEluZGV4VG9FeGNlbENvbHVtbihodW1hblJlYWRhYmxlSW5kZXgpICtcbiAgICAgICAgICAgICAgICAnPC9kaXY+Jyk7XG4gICAgICAgIH0pO1xuICAgIH0sXG5cbiAgICBjb252ZXJ0SW5kZXhUb0V4Y2VsQ29sdW1uOiBmdW5jdGlvbihpKSB7XG4gICAgICAgIHZhciBhbHBoYSA9IHBhcnNlSW50KGkgLyAyNywgMTApO1xuICAgICAgICB2YXIgcmVtYWluZGVyID0gaSAtIChhbHBoYSAqIDI2KTtcbiAgICAgICAgdmFyIGNvbHVtbiA9ICcnO1xuXG4gICAgICAgIGlmIChhbHBoYSA+IDApIHtcbiAgICAgICAgICAgIGNvbHVtbiA9IFN0cmluZy5mcm9tQ2hhckNvZGUoYWxwaGEgKyA2NCk7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAocmVtYWluZGVyID4gMCkge1xuICAgICAgICAgICAgY29sdW1uICs9IFN0cmluZy5mcm9tQ2hhckNvZGUocmVtYWluZGVyICsgNjQpO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIGNvbHVtbjtcbiAgICB9XG59O1xuXG53aW5kb3cuYWRkRXZlbnQoJ2xvYWQnLCBmdW5jdGlvbigpXG57XG4gICAgTGVhZHMuaW5pdGlhbGl6ZUNvbHVtbkRpc3BsYXlIZWxwZXIoKTtcbn0pO1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vYXNzZXRzL2xlYWRzLmpzIl0sInNvdXJjZVJvb3QiOiIifQ==