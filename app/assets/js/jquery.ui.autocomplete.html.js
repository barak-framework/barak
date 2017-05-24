/*
 * jQuery UI Autocomplete HTML Extension
 *
 * Copyright 2010, Scott González (http://scottgonzalez.com)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * http://github.com/scottgonzalez/jquery-ui-extensions
 */
(function(a){function d(b,c){var d=new RegExp(a.ui.autocomplete.escapeRegex(c),"i");return a.grep(b,function(b){return d.test(a("<div>").html(b.label||b.value||b).text())})}var b=a.ui.autocomplete.prototype,c=b._initSource;a.extend(b,{_initSource:function(){this.options.html&&a.isArray(this.options.source)?this.source=function(a,b){b(d(this.options.source,a.term))}:c.call(this)},_renderItem:function(b,c){return a("<li></li>").data("item.autocomplete",c).append(a("<a></a>")[this.options.html?"html":"text"](c.label)).appendTo(b)}})})(jQuery)