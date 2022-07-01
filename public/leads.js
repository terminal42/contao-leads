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


(function () {
    var initializeColumnDisplayHelper = function initializeColumnDisplayHelper() {
        var mcws = document.querySelectorAll('table.multicolumnwizard');

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
            var elements = fetchColumnDisplayElements(mcw);

            if (MutationObserver) {
                updateColumnDisplays(elements);

                // Register observer
                var observerConfig = { childList: true, subtree: true };
                var observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {

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
                elements.forEach(function (el) {
                    el.set('html', '');
                });
            }
        });
    };

    var fetchColumnDisplayElements = function fetchColumnDisplayElements(mcw) {
        return mcw.querySelectorAll('td.column_display');
    };

    var updateColumnDisplays = function updateColumnDisplays(elements) {
        elements.forEach(function (el, index) {
            var humanReadableIndex = index + 1;

            el.set('html', '<div class="index">' + humanReadableIndex + '</div>' + '<div class="excel">' + convertIndexToExcelColumn(humanReadableIndex) + '</div>');
        });
    };

    var convertIndexToExcelColumn = function convertIndexToExcelColumn(i) {
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
    };

    document.addEventListener('DOMContentLoaded', function () {
        initializeColumnDisplayHelper();
    });
})();

/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAgZjczNGZkYmJhZDM1NzU2YmU5ZTgiLCJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2xlYWRzLmNzcz85NDc0Iiwid2VicGFjazovLy8uL2Fzc2V0cy9sZWFkcy5qcyJdLCJuYW1lcyI6WyJpbml0aWFsaXplQ29sdW1uRGlzcGxheUhlbHBlciIsIm1jd3MiLCJkb2N1bWVudCIsInF1ZXJ5U2VsZWN0b3JBbGwiLCJNdXRhdGlvbk9ic2VydmVyIiwicHJlZml4ZXMiLCJpIiwibGVuZ3RoIiwid2luZG93IiwiZm9yRWFjaCIsIm1jdyIsImVsZW1lbnRzIiwiZmV0Y2hDb2x1bW5EaXNwbGF5RWxlbWVudHMiLCJ1cGRhdGVDb2x1bW5EaXNwbGF5cyIsIm9ic2VydmVyQ29uZmlnIiwiY2hpbGRMaXN0Iiwic3VidHJlZSIsIm9ic2VydmVyIiwibXV0YXRpb25zIiwibXV0YXRpb24iLCJhZGRlZE5vZGVzIiwicmVtb3ZlZE5vZGVzIiwiZGlzY29ubmVjdCIsIm9ic2VydmUiLCJlbCIsInNldCIsImluZGV4IiwiaHVtYW5SZWFkYWJsZUluZGV4IiwiY29udmVydEluZGV4VG9FeGNlbENvbHVtbiIsImFscGhhIiwicGFyc2VJbnQiLCJyZW1haW5kZXIiLCJjb2x1bW4iLCJTdHJpbmciLCJmcm9tQ2hhckNvZGUiLCJhZGRFdmVudExpc3RlbmVyIl0sIm1hcHBpbmdzIjoiO0FBQUE7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7OztBQUdBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGFBQUs7QUFDTDtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBLG1DQUEyQiwwQkFBMEIsRUFBRTtBQUN2RCx5Q0FBaUMsZUFBZTtBQUNoRDtBQUNBO0FBQ0E7O0FBRUE7QUFDQSw4REFBc0QsK0RBQStEOztBQUVySDtBQUNBOztBQUVBO0FBQ0E7Ozs7Ozs7Ozs7OztBQzdEQSx5Qzs7Ozs7Ozs7Ozs7OztBQ0FBO0FBQUE7QUFBQTtBQUFBOztBQUVBLENBQUMsWUFBTTtBQUNILFFBQU1BLGdDQUFnQyxTQUFoQ0EsNkJBQWdDLEdBQU07QUFDeEMsWUFBSUMsT0FBT0MsU0FBU0MsZ0JBQVQsQ0FBMEIseUJBQTFCLENBQVg7O0FBRUE7QUFDQSxZQUFJQyxtQkFBb0IsWUFBWTtBQUNoQyxnQkFBSUMsV0FBVyxDQUFDLFFBQUQsRUFBVyxLQUFYLEVBQWtCLEdBQWxCLEVBQXVCLElBQXZCLEVBQTZCLEVBQTdCLENBQWY7QUFDQSxpQkFBSSxJQUFJQyxJQUFFLENBQVYsRUFBYUEsSUFBSUQsU0FBU0UsTUFBMUIsRUFBa0NELEdBQWxDLEVBQXVDO0FBQ25DLG9CQUFHRCxTQUFTQyxDQUFULElBQWMsa0JBQWQsSUFBb0NFLE1BQXZDLEVBQStDO0FBQzNDLDJCQUFPQSxPQUFPSCxTQUFTQyxDQUFULElBQWMsa0JBQXJCLENBQVA7QUFDSDtBQUNKO0FBQ0QsbUJBQU8sS0FBUDtBQUNILFNBUnVCLEVBQXhCOztBQVVBTCxhQUFLUSxPQUFMLENBQWEsVUFBQ0MsR0FBRCxFQUFTO0FBQ2xCLGdCQUFJQyxXQUFXQywyQkFBMkJGLEdBQTNCLENBQWY7O0FBRUEsZ0JBQUlOLGdCQUFKLEVBQXNCO0FBQ2xCUyxxQ0FBcUJGLFFBQXJCOztBQUVBO0FBQ0Esb0JBQUlHLGlCQUFpQixFQUFDQyxXQUFXLElBQVosRUFBa0JDLFNBQVMsSUFBM0IsRUFBckI7QUFDQSxvQkFBSUMsV0FBVyxJQUFJYixnQkFBSixDQUFxQixVQUFDYyxTQUFELEVBQWU7QUFDL0NBLDhCQUFVVCxPQUFWLENBQWtCLFVBQUNVLFFBQUQsRUFBYzs7QUFFNUIsNEJBQUlBLFNBQVNDLFVBQVQsQ0FBb0JiLE1BQXBCLEdBQTZCLENBQTdCLElBQWtDWSxTQUFTRSxZQUFULENBQXNCZCxNQUF0QixHQUErQixDQUFyRSxFQUF3RTtBQUNwRVUscUNBQVNLLFVBQVQ7O0FBRUFYLHVDQUFXQywyQkFBMkJGLEdBQTNCLENBQVg7QUFDQUcsaURBQXFCRixRQUFyQjs7QUFFQU0scUNBQVNNLE9BQVQsQ0FBaUJiLEdBQWpCLEVBQXNCSSxjQUF0QjtBQUNIO0FBQ0oscUJBVkQ7QUFXSCxpQkFaYyxDQUFmOztBQWNBRyx5QkFBU00sT0FBVCxDQUFpQmIsR0FBakIsRUFBc0JJLGNBQXRCO0FBRUgsYUFyQkQsTUFxQk87QUFDSEgseUJBQVNGLE9BQVQsQ0FBaUIsVUFBU2UsRUFBVCxFQUFhO0FBQzFCQSx1QkFBR0MsR0FBSCxDQUFPLE1BQVAsRUFBZSxFQUFmO0FBQ0gsaUJBRkQ7QUFHSDtBQUNKLFNBN0JEO0FBOEJILEtBNUNEOztBQThDQSxRQUFNYiw2QkFBNkIsU0FBN0JBLDBCQUE2QixDQUFDRixHQUFELEVBQVM7QUFDeEMsZUFBT0EsSUFBSVAsZ0JBQUosQ0FBcUIsbUJBQXJCLENBQVA7QUFDSCxLQUZEOztBQUlBLFFBQU1VLHVCQUF1QixTQUF2QkEsb0JBQXVCLENBQUNGLFFBQUQsRUFBYztBQUN2Q0EsaUJBQVNGLE9BQVQsQ0FBaUIsVUFBU2UsRUFBVCxFQUFhRSxLQUFiLEVBQW9CO0FBQ2pDLGdCQUFJQyxxQkFBcUJELFFBQVEsQ0FBakM7O0FBRUFGLGVBQUdDLEdBQUgsQ0FBTyxNQUFQLEVBQWUsd0JBQ1hFLGtCQURXLEdBRVgsUUFGVyxHQUdYLHFCQUhXLEdBSVhDLDBCQUEwQkQsa0JBQTFCLENBSlcsR0FLWCxRQUxKO0FBTUgsU0FURDtBQVVILEtBWEQ7O0FBYUEsUUFBTUMsNEJBQTRCLFNBQTVCQSx5QkFBNEIsQ0FBQ3RCLENBQUQsRUFBTztBQUNyQyxZQUFJdUIsUUFBUUMsU0FBU3hCLElBQUksRUFBYixFQUFpQixFQUFqQixDQUFaO0FBQ0EsWUFBSXlCLFlBQVl6QixJQUFLdUIsUUFBUSxFQUE3QjtBQUNBLFlBQUlHLFNBQVMsRUFBYjs7QUFFQSxZQUFJSCxRQUFRLENBQVosRUFBZTtBQUNYRyxxQkFBU0MsT0FBT0MsWUFBUCxDQUFvQkwsUUFBUSxFQUE1QixDQUFUO0FBQ0g7O0FBRUQsWUFBSUUsWUFBWSxDQUFoQixFQUFtQjtBQUNmQyxzQkFBVUMsT0FBT0MsWUFBUCxDQUFvQkgsWUFBWSxFQUFoQyxDQUFWO0FBQ0g7O0FBRUQsZUFBT0MsTUFBUDtBQUNILEtBZEQ7O0FBZ0JBOUIsYUFBU2lDLGdCQUFULENBQTBCLGtCQUExQixFQUE4QyxZQUFNO0FBQ2hEbkM7QUFDSCxLQUZEO0FBR0gsQ0FuRkQsSSIsImZpbGUiOiJsZWFkcy5qcyIsInNvdXJjZXNDb250ZW50IjpbIiBcdC8vIFRoZSBtb2R1bGUgY2FjaGVcbiBcdHZhciBpbnN0YWxsZWRNb2R1bGVzID0ge307XG5cbiBcdC8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG4gXHRmdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cbiBcdFx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG4gXHRcdGlmKGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdKSB7XG4gXHRcdFx0cmV0dXJuIGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdLmV4cG9ydHM7XG4gXHRcdH1cbiBcdFx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcbiBcdFx0dmFyIG1vZHVsZSA9IGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdID0ge1xuIFx0XHRcdGk6IG1vZHVsZUlkLFxuIFx0XHRcdGw6IGZhbHNlLFxuIFx0XHRcdGV4cG9ydHM6IHt9XG4gXHRcdH07XG5cbiBcdFx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG4gXHRcdG1vZHVsZXNbbW9kdWxlSWRdLmNhbGwobW9kdWxlLmV4cG9ydHMsIG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG4gXHRcdC8vIEZsYWcgdGhlIG1vZHVsZSBhcyBsb2FkZWRcbiBcdFx0bW9kdWxlLmwgPSB0cnVlO1xuXG4gXHRcdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG4gXHRcdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbiBcdH1cblxuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZXMgb2JqZWN0IChfX3dlYnBhY2tfbW9kdWxlc19fKVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5tID0gbW9kdWxlcztcblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGUgY2FjaGVcbiBcdF9fd2VicGFja19yZXF1aXJlX18uYyA9IGluc3RhbGxlZE1vZHVsZXM7XG5cbiBcdC8vIGRlZmluZSBnZXR0ZXIgZnVuY3Rpb24gZm9yIGhhcm1vbnkgZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kID0gZnVuY3Rpb24oZXhwb3J0cywgbmFtZSwgZ2V0dGVyKSB7XG4gXHRcdGlmKCFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywgbmFtZSkpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgbmFtZSwge1xuIFx0XHRcdFx0Y29uZmlndXJhYmxlOiBmYWxzZSxcbiBcdFx0XHRcdGVudW1lcmFibGU6IHRydWUsXG4gXHRcdFx0XHRnZXQ6IGdldHRlclxuIFx0XHRcdH0pO1xuIFx0XHR9XG4gXHR9O1xuXG4gXHQvLyBnZXREZWZhdWx0RXhwb3J0IGZ1bmN0aW9uIGZvciBjb21wYXRpYmlsaXR5IHdpdGggbm9uLWhhcm1vbnkgbW9kdWxlc1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5uID0gZnVuY3Rpb24obW9kdWxlKSB7XG4gXHRcdHZhciBnZXR0ZXIgPSBtb2R1bGUgJiYgbW9kdWxlLl9fZXNNb2R1bGUgP1xuIFx0XHRcdGZ1bmN0aW9uIGdldERlZmF1bHQoKSB7IHJldHVybiBtb2R1bGVbJ2RlZmF1bHQnXTsgfSA6XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0TW9kdWxlRXhwb3J0cygpIHsgcmV0dXJuIG1vZHVsZTsgfTtcbiBcdFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kKGdldHRlciwgJ2EnLCBnZXR0ZXIpO1xuIFx0XHRyZXR1cm4gZ2V0dGVyO1xuIFx0fTtcblxuIFx0Ly8gT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm8gPSBmdW5jdGlvbihvYmplY3QsIHByb3BlcnR5KSB7IHJldHVybiBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwob2JqZWN0LCBwcm9wZXJ0eSk7IH07XG5cbiBcdC8vIF9fd2VicGFja19wdWJsaWNfcGF0aF9fXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnAgPSBcIi9idW5kbGVzL3Rlcm1pbmFsNDJsZWFkcy9cIjtcblxuIFx0Ly8gTG9hZCBlbnRyeSBtb2R1bGUgYW5kIHJldHVybiBleHBvcnRzXG4gXHRyZXR1cm4gX193ZWJwYWNrX3JlcXVpcmVfXyhfX3dlYnBhY2tfcmVxdWlyZV9fLnMgPSBcIi4vYXNzZXRzL2xlYWRzLmpzXCIpO1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIHdlYnBhY2svYm9vdHN0cmFwIGY3MzRmZGJiYWQzNTc1NmJlOWU4IiwiLy8gcmVtb3ZlZCBieSBleHRyYWN0LXRleHQtd2VicGFjay1wbHVnaW5cblxuXG4vLy8vLy8vLy8vLy8vLy8vLy9cbi8vIFdFQlBBQ0sgRk9PVEVSXG4vLyAuL2Fzc2V0cy9sZWFkcy5jc3Ncbi8vIG1vZHVsZSBpZCA9IC4vYXNzZXRzL2xlYWRzLmNzc1xuLy8gbW9kdWxlIGNodW5rcyA9IDAiLCJpbXBvcnQgJy4vbGVhZHMuY3NzJztcblxuKCgpID0+IHtcbiAgICBjb25zdCBpbml0aWFsaXplQ29sdW1uRGlzcGxheUhlbHBlciA9ICgpID0+IHtcbiAgICAgICAgdmFyIG1jd3MgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCd0YWJsZS5tdWx0aWNvbHVtbndpemFyZCcpO1xuXG4gICAgICAgIC8vIENhbm5vdCB1c2UgcmVndWxhciBjbGljayBldmVudHMgYmVjYXVzZSBvZiBNQ1dcbiAgICAgICAgdmFyIE11dGF0aW9uT2JzZXJ2ZXIgPSAoZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgdmFyIHByZWZpeGVzID0gWydXZWJLaXQnLCAnTW96JywgJ08nLCAnTXMnLCAnJ107XG4gICAgICAgICAgICBmb3IodmFyIGk9MDsgaSA8IHByZWZpeGVzLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgICAgICAgICAgaWYocHJlZml4ZXNbaV0gKyAnTXV0YXRpb25PYnNlcnZlcicgaW4gd2luZG93KSB7XG4gICAgICAgICAgICAgICAgICAgIHJldHVybiB3aW5kb3dbcHJlZml4ZXNbaV0gKyAnTXV0YXRpb25PYnNlcnZlciddO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgfSgpKTtcblxuICAgICAgICBtY3dzLmZvckVhY2goKG1jdykgPT4ge1xuICAgICAgICAgICAgdmFyIGVsZW1lbnRzID0gZmV0Y2hDb2x1bW5EaXNwbGF5RWxlbWVudHMobWN3KTtcblxuICAgICAgICAgICAgaWYgKE11dGF0aW9uT2JzZXJ2ZXIpIHtcbiAgICAgICAgICAgICAgICB1cGRhdGVDb2x1bW5EaXNwbGF5cyhlbGVtZW50cyk7XG5cbiAgICAgICAgICAgICAgICAvLyBSZWdpc3RlciBvYnNlcnZlclxuICAgICAgICAgICAgICAgIHZhciBvYnNlcnZlckNvbmZpZyA9IHtjaGlsZExpc3Q6IHRydWUsIHN1YnRyZWU6IHRydWV9O1xuICAgICAgICAgICAgICAgIHZhciBvYnNlcnZlciA9IG5ldyBNdXRhdGlvbk9ic2VydmVyKChtdXRhdGlvbnMpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgbXV0YXRpb25zLmZvckVhY2goKG11dGF0aW9uKSA9PiB7XG5cbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChtdXRhdGlvbi5hZGRlZE5vZGVzLmxlbmd0aCA+IDAgfHwgbXV0YXRpb24ucmVtb3ZlZE5vZGVzLmxlbmd0aCA+IDApIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBvYnNlcnZlci5kaXNjb25uZWN0KCk7XG5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBlbGVtZW50cyA9IGZldGNoQ29sdW1uRGlzcGxheUVsZW1lbnRzKG1jdyk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdXBkYXRlQ29sdW1uRGlzcGxheXMoZWxlbWVudHMpO1xuXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgb2JzZXJ2ZXIub2JzZXJ2ZShtY3csIG9ic2VydmVyQ29uZmlnKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgfSk7XG5cbiAgICAgICAgICAgICAgICBvYnNlcnZlci5vYnNlcnZlKG1jdywgb2JzZXJ2ZXJDb25maWcpO1xuXG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIGVsZW1lbnRzLmZvckVhY2goZnVuY3Rpb24oZWwpIHtcbiAgICAgICAgICAgICAgICAgICAgZWwuc2V0KCdodG1sJywgJycpO1xuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9O1xuXG4gICAgY29uc3QgZmV0Y2hDb2x1bW5EaXNwbGF5RWxlbWVudHMgPSAobWN3KSA9PiB7XG4gICAgICAgIHJldHVybiBtY3cucXVlcnlTZWxlY3RvckFsbCgndGQuY29sdW1uX2Rpc3BsYXknKTtcbiAgICB9O1xuXG4gICAgY29uc3QgdXBkYXRlQ29sdW1uRGlzcGxheXMgPSAoZWxlbWVudHMpID0+IHtcbiAgICAgICAgZWxlbWVudHMuZm9yRWFjaChmdW5jdGlvbihlbCwgaW5kZXgpIHtcbiAgICAgICAgICAgIHZhciBodW1hblJlYWRhYmxlSW5kZXggPSBpbmRleCArIDE7XG5cbiAgICAgICAgICAgIGVsLnNldCgnaHRtbCcsICc8ZGl2IGNsYXNzPVwiaW5kZXhcIj4nICtcbiAgICAgICAgICAgICAgICBodW1hblJlYWRhYmxlSW5kZXggK1xuICAgICAgICAgICAgICAgICc8L2Rpdj4nICtcbiAgICAgICAgICAgICAgICAnPGRpdiBjbGFzcz1cImV4Y2VsXCI+JyArXG4gICAgICAgICAgICAgICAgY29udmVydEluZGV4VG9FeGNlbENvbHVtbihodW1hblJlYWRhYmxlSW5kZXgpICtcbiAgICAgICAgICAgICAgICAnPC9kaXY+Jyk7XG4gICAgICAgIH0pO1xuICAgIH07XG5cbiAgICBjb25zdCBjb252ZXJ0SW5kZXhUb0V4Y2VsQ29sdW1uID0gKGkpID0+IHtcbiAgICAgICAgdmFyIGFscGhhID0gcGFyc2VJbnQoaSAvIDI3LCAxMCk7XG4gICAgICAgIHZhciByZW1haW5kZXIgPSBpIC0gKGFscGhhICogMjYpO1xuICAgICAgICB2YXIgY29sdW1uID0gJyc7XG5cbiAgICAgICAgaWYgKGFscGhhID4gMCkge1xuICAgICAgICAgICAgY29sdW1uID0gU3RyaW5nLmZyb21DaGFyQ29kZShhbHBoYSArIDY0KTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChyZW1haW5kZXIgPiAwKSB7XG4gICAgICAgICAgICBjb2x1bW4gKz0gU3RyaW5nLmZyb21DaGFyQ29kZShyZW1haW5kZXIgKyA2NCk7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gY29sdW1uO1xuICAgIH07XG5cbiAgICBkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgKCkgPT4ge1xuICAgICAgICBpbml0aWFsaXplQ29sdW1uRGlzcGxheUhlbHBlcigpO1xuICAgIH0pO1xufSkoKTtcblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyAuL2Fzc2V0cy9sZWFkcy5qcyJdLCJzb3VyY2VSb290IjoiIn0=