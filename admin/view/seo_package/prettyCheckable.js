!function(e,t,a,i){"use strict";var n="prettyCheckable",s="plugin_"+n,l={label:"",labelPosition:"right",customClass:"",color:"blue"},o=function(a){t.ko&&e(a).on("change",function(t){if(t.preventDefault(),t.originalEvent===i){var a=e(this).closest(".clearfix"),n=e(a).find("a:first"),s=n.hasClass("checked");s===!0?n.addClass("checked"):n.removeClass("checked")}}),a.find("a:first, label").on("touchstart click",function(a){a.preventDefault();var i=e(this).closest(".clearfix"),n=i.find("input"),s=i.find("a:first");s.hasClass("disabled")!==!0&&("radio"===n.prop("type")&&e('input[name="'+n.attr("name")+'"]').each(function(t,a){e(a).prop("checked",!1).parent().find("a:first").removeClass("checked")}),t.ko?ko.utils.triggerEvent(n[0],"click"):n.prop("checked")?n.prop("checked",!1).change():n.prop("checked",!0).change(),s.toggleClass("checked"))}),a.find("a:first").on("keyup",function(t){32===t.keyCode&&e(this).click()})},r=function(t){this.element=t,this.options=e.extend({},l)};r.prototype={init:function(t){e.extend(this.options,t);var a=e(this.element);a.parent().addClass("has-pretty-child"),a.css("display","none");var n=a.data("type")!==i?a.data("type"):a.attr("type"),s=null,l=a.attr("id");if(l!==i){var r=e("label[for="+l+"]");r.length>0&&(s=r.text())}""===this.options.label&&(this.options.label=s),s=a.data("label")!==i?a.data("label"):this.options.label;var c=a.data("labelposition")!==i?"label"+a.data("labelposition"):"label"+this.options.labelPosition,d=a.data("customclass")!==i?a.data("customclass"):this.options.customClass,p=a.data("color")!==i?a.data("color"):this.options.color,h=a.prop("disabled")===!0?"disabled":"",f=["pretty"+n,c,d,p].join(" ");a.wrap('<div class="clearfix '+f+'"></div>').parent().html();var u=[],b=a.prop("checked")?"checked":"";"labelright"===c?(u.push('<a href="#" class="'+b+" "+h+'"></a>'),u.push('<label for="'+a.attr("id")+'">'+s+"</label>")):"labelnone"===c?u.push('<a href="#" class="'+b+" "+h+'"></a>'):(u.push('<label for="'+a.attr("id")+'">'+s+"</label>"),u.push('<a href="#" class="'+b+" "+h+'"></a>')),a.parent().append(u.join("\n")),o(a.parent())},check:function(){"radio"===e(this.element).prop("type")&&e('input[name="'+e(this.element).attr("name")+'"]').each(function(t,a){e(a).prop("checked",!1).attr("checked",!1).parent().find("a:first").removeClass("checked")}),e(this.element).prop("checked",!0).attr("checked",!0).parent().find("a:first").addClass("checked")},uncheck:function(){e(this.element).prop("checked",!1).attr("checked",!1).parent().find("a:first").removeClass("checked")},enable:function(){e(this.element).removeAttr("disabled").parent().find("a:first").removeClass("disabled")},disable:function(){e(this.element).attr("disabled","disabled").parent().find("a:first").addClass("disabled")},destroy:function(){var t=e(this.element),a=t.clone(),n=t.attr("id");if(n!==i){var s=e("label[for="+n+"]");s.length>0&&s.insertBefore(t.parent())}a.removeAttr("style").insertAfter(s),t.parent().remove()}},e.fn[n]=function(t){var a,i;if(this.data(s)instanceof r||this.data(s,new r(this)),i=this.data(s),i.element=this,"undefined"==typeof t||"object"==typeof t)"function"==typeof i.init&&i.init(t);else{if("string"==typeof t&&"function"==typeof i[t])return a=Array.prototype.slice.call(arguments,1),i[t].apply(i,a);e.error("Method "+t+" does not exist on jQuery."+n)}}}(jQuery,window,document);