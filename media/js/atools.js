/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// Only define com_EasyTablePro if it doesn't exist.
if (typeof(com_EasyTablePro) === 'undefined') {
    var com_EasyTablePro = {};
    com_EasyTablePro.Manager      = {};
    com_EasyTablePro.Table        = {};
    com_EasyTablePro.Record       = {};
    com_EasyTablePro.Upload       = {};
    com_EasyTablePro.Link         = {};
    com_EasyTablePro.requestData  = {};
    com_EasyTablePro.Tools        = {};
}

if(typeof jQuery == 'undefined')
{
    window.addEvent('domready', function () {
        com_EasyTablePro.setUp();
    });
}
else
{
    jQuery(document).ready(function(){
        com_EasyTablePro.setUp();
    });
}

com_EasyTablePro.setUp = function ()
{
    var token         = com_EasyTablePro.Tools.getToken();
    this.requestData[token]  = 1;
    this.requestData.id = com_EasyTablePro.Tools.getID();
}

com_EasyTablePro.Tools.getToken = function ()
{
    var theToken;
    var els = document.getElementsByTagName('input');
    for (var i = 0; i < els.length; i++) {
        if ((els[i].type === 'hidden') && (els[i].name.length === 32) && els[i].value === '1') {
            theToken = els[i].name;
        }
    }
    return theToken;
}

com_EasyTablePro.Tools.getID  = function ()
{
    if (document.getElementById('id')) {
        return document.getElementById('id').value;
    } else {
        return null;
    }
}

com_EasyTablePro.Tools.disableToolbarBtn = function (toolBarBtn, newToolTipText)
{
    "use strict";
    var ourBtn;

    // Setup the default vars
    if (typeof jQuery === 'undefined')
    {
        ourBtn = document.getElementById(toolBarBtn);
        var ourBtnLink = ourBtn.childNodes[1];
        var ourBtnSpan = ourBtnLink.childNodes[1];
        // Check to see if button class is already set to -off
        if (ourBtnSpan.get('class').indexOf('-off') > 0)
        {
            return;
        }
        // Disable the link
        ourBtnLink.removeEvents();
        ourBtnLink.removeAttribute('href');
        ourBtnLink.removeAttribute('rel');
        ourBtnLink.addClass('hasTip');

        // Setup the new tooltip message
        var newTitle = newToolTipText;
        ourBtnLink.set('title', newTitle);
        var ourBtnTips = ourBtnLink.get('title').split('::',2);
        ourBtnLink.store('tip:title', ourBtnTips[0]);
        ourBtnLink.store('tip:text', ourBtnTips[1]);
        // Re-init Tooltips - @todo find a less nuclear way of doing this...
        // don't remove this line, it's the side effects we need.
        var JTooltips = new Tips($$('.hasTip'), { maxTitleChars: 50, fixed: false});

        // Change icon
        // This could be a problem if buttons ever end up with multiple classes if different orders.
        var ourBtnSpanClassArray = ourBtnSpan.get('class').split(' ');
        var ourBtnSpanClassOff = ourBtnSpanClassArray[0] + '-off';

        ourBtnSpan.addClass( ourBtnSpanClassOff );
        ourBtnSpan.removeClass( ourBtnSpanClassArray[0] );
    }
    else
    {
        ourBtn = jQuery('#' + toolBarBtn);
        ourBtn[0].setProperty('disabled', true);
    }
}

com_EasyTablePro.Tools.addToList = function(theList, itemToAdd)
{
    newList = theList.split(', ');
    newList.push(itemToAdd);
    return newList.join(', ');
}

com_EasyTablePro.Tools.deleteFromList = function(theList, itemToRemove)
{
    originalList = theList.split(', ');
    newList = new Array();
    // Remove the matching element from the array
    for(var i=0; i<originalList.length; i++) {
        if (originalList[i] != itemToRemove) newList.push(originalList[i]);
    }
    return newList.join(', ');
}

com_EasyTablePro.Tools.removeOptions = function(theSelect, optionFrom, optionTo)
{
    optionFrom = (typeof optionFrom == 'undefined') ? 0 : optionFrom;

    if (typeof jQuery === 'undefined')
    {
        optionTo = (typeof optionTo == 'undefined') ? theSelect.options.length - 1 : optionTo;
        for(i=optionTo;i>=optionFrom;i--)
        {
            theSelect.remove(i);
        }
    }
    else
    {
        var theSelectOptions = theSelect.children('option');
        optionTo = (typeof optionTo == 'undefined') ? theSelectOptions.length - 1 : optionTo;

        for(i=optionTo;i>=optionFrom;i--)
        {
            theSelect.children("option[value='"+theSelectOptions[i].value+"']").remove();
        }
    }
}

com_EasyTablePro.Tools.makeURLSafe = function(str)
{
    // Modify it if it's just a number
    if(this.isNumber(str))
    {
        str = 'a' + str;
    }
    urlSafeStr = str.replace(/\s+/g,"-").replace(/[^A-Za-z0-9\-\%]/g,'').toLowerCase();
    if (urlSafeStr == '')
    {
        theAlias = encodeURIComponent(str).toLowerCase();
    } else {
        theAlias = urlSafeStr;
    }
    return theAlias; 
}

com_EasyTablePro.Tools.isEven = function (n)
{
    "use strict";
    return this.isNumber(n) && (n % 2 === 0);
};

com_EasyTablePro.Tools.isOdd = function (n)
{
    "use strict";
    return this.isNumber(n) && (Math.abs(n) % 2 === 1);
};

com_EasyTablePro.Tools.isNumber = function (n) {
    "use strict";
    return !isNaN(parseFloat(n)) && isFinite(n);
};

/**
sprintf() for JavaScript 0.7-beta1
http://www.diveintojavascript.com/projects/javascript-sprintf

Copyright (c) Alexandru Marasteanu <alexaholic [at) gmail (dot] com>
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of sprintf() for JavaScript nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Alexandru Marasteanu BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
**/
com_EasyTablePro.Tools.sprintf = (function() {
    function get_type(variable) {
        return Object.prototype.toString.call(variable).slice(8, -1).toLowerCase();
    }
    function str_repeat(input, multiplier) {
        for (var output = []; multiplier > 0; output[--multiplier] = input) {/* do nothing */}
        return output.join('');
    }

    var str_format = function() {
        if (!str_format.cache.hasOwnProperty(arguments[0])) {
            str_format.cache[arguments[0]] = str_format.parse(arguments[0]);
        }
        return str_format.format.call(null, str_format.cache[arguments[0]], arguments);
    };

    str_format.format = function(parse_tree, argv) {
        var cursor = 1, tree_length = parse_tree.length, node_type = '', arg, output = [], i, k, match, pad, pad_character, pad_length;
        for (i = 0; i < tree_length; i++) {
            node_type = get_type(parse_tree[i]);
            if (node_type === 'string') {
                output.push(parse_tree[i]);
            }
            else if (node_type === 'array') {
                match = parse_tree[i]; // convenience purposes only
                if (match[2]) { // keyword argument
                    arg = argv[cursor];
                    for (k = 0; k < match[2].length; k++) {
                        if (!arg.hasOwnProperty(match[2][k])) {
                            throw(sprintf('[sprintf] property "%s" does not exist', match[2][k]));
                        }
                        arg = arg[match[2][k]];
                    }
                }
                else if (match[1]) { // positional argument (explicit)
                    arg = argv[match[1]];
                }
                else { // positional argument (implicit)
                    arg = argv[cursor++];
                }

                if (/[^s]/.test(match[8]) && (get_type(arg) != 'number')) {
                    throw(sprintf('[sprintf] expecting number but found %s', get_type(arg)));
                }
                switch (match[8]) {
                    case 'b': arg = arg.toString(2); break;
                    case 'c': arg = String.fromCharCode(arg); break;
                    case 'd': arg = parseInt(arg, 10); break;
                    case 'e': arg = match[7] ? arg.toExponential(match[7]) : arg.toExponential(); break;
                    case 'f': arg = match[7] ? parseFloat(arg).toFixed(match[7]) : parseFloat(arg); break;
                    case 'o': arg = arg.toString(8); break;
                    case 's': arg = ((arg = String(arg)) && match[7] ? arg.substring(0, match[7]) : arg); break;
                    case 'u': arg = Math.abs(arg); break;
                    case 'x': arg = arg.toString(16); break;
                    case 'X': arg = arg.toString(16).toUpperCase(); break;
                }
                arg = (/[def]/.test(match[8]) && match[3] && arg >= 0 ? '+'+ arg : arg);
                pad_character = match[4] ? match[4] == '0' ? '0' : match[4].charAt(1) : ' ';
                pad_length = match[6] - String(arg).length;
                pad = match[6] ? str_repeat(pad_character, pad_length) : '';
                output.push(match[5] ? arg + pad : pad + arg);
            }
        }
        return output.join('');
    };

    str_format.cache = {};

    str_format.parse = function(fmt) {
        var _fmt = fmt, match = [], parse_tree = [], arg_names = 0;
        while (_fmt) {
            if ((match = /^[^\x25]+/.exec(_fmt)) !== null) {
                parse_tree.push(match[0]);
            }
            else if ((match = /^\x25{2}/.exec(_fmt)) !== null) {
                parse_tree.push('%');
            }
            else if ((match = /^\x25(?:([1-9]\d*)\$|\(([^\)]+)\))?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(_fmt)) !== null) {
                if (match[2]) {
                    arg_names |= 1;
                    var field_list = [], replacement_field = match[2], field_match = [];
                    if ((field_match = /^([a-z_][a-z_\d]*)/i.exec(replacement_field)) !== null) {
                        field_list.push(field_match[1]);
                        while ((replacement_field = replacement_field.substring(field_match[0].length)) !== '') {
                            if ((field_match = /^\.([a-z_][a-z_\d]*)/i.exec(replacement_field)) !== null) {
                                field_list.push(field_match[1]);
                            }
                            else if ((field_match = /^\[(\d+)\]/.exec(replacement_field)) !== null) {
                                field_list.push(field_match[1]);
                            }
                            else {
                                throw('[sprintf] huh?');
                            }
                        }
                    }
                    else {
                        throw('[sprintf] huh?');
                    }
                    match[2] = field_list;
                }
                else {
                    arg_names |= 2;
                }
                if (arg_names === 3) {
                    throw('[sprintf] mixing positional and named placeholders is not (yet) supported');
                }
                parse_tree.push(match);
            }
            else {
                throw('[sprintf] huh?');
            }
            _fmt = _fmt.substring(match[0].length);
        }
        return parse_tree;
    };

    return str_format;
})();

com_EasyTablePro.Tools.vsprintf = function(fmt, argv) {
    argv.unshift(fmt);
    return sprintf.apply(null, argv);
};
