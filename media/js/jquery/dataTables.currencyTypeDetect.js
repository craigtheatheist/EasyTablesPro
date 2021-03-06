/*jslint nomen: true */
/*global jQuery*/
(function () {
    'use strict';
    // Change this list to the valid characters you want
    var validChars = '$£€c' + '0123456789' + ".-,'",
        str = jQuery.fn.dataTableExt.oApi._fnEscapeRegex(validChars),
        re = new RegExp('[^' + str + ']');

    jQuery.fn.dataTableExt.aTypes.unshift(
        function (data) {
            if (typeof data !== 'string' || re.test(data)) {
                return null;
            }

            return 'currency';
        }
    );

});
