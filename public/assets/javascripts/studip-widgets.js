!function(t){var e={};function i(n){if(e[n])return e[n].exports;var r=e[n]={i:n,l:!1,exports:{}};return t[n].call(r.exports,r,r.exports,i),r.l=!0,r.exports}i.m=t,i.c=e,i.d=function(t,e,n){i.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},i.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)i.d(n,r,function(e){return t[e]}.bind(null,r));return n},i.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="",i(i.s=53)}({47:function(t,e){!function(t,e){"use strict";t(document).on("click",".tabbable-widget > nav > a",function(e){var i=t(this).attr("href");t(this).addClass("active").siblings().removeClass("active"),t(i).addClass("active").siblings("section").removeClass("active").end(),setTimeout(function(){t(i).scrollTop(0)},0),history.pushState&&history.pushState(null,null,i),e.preventDefault()}).ready(function(){location.hash&&t('.tabbable-widget > nav > a[href="'+location.hash+'"]').click()})}(jQuery,STUDIP)},48:function(t,e){!function(t,e){"use strict";function i(i){return t.Deferred(function(n){if(t("#layout-sidebar .sidebar-secondary-widget").length>0)return n.resolve();t.get(i).then(function(i){var r=t(i),a=t(".addable-widgets",r).data().containerId,o=e.WidgetSystem.get(a),s=t(".addable-widgets div[data-widget-id]",r),d=Math.floor(t(o.grid).width()/o.width);t(r).appendTo("#layout-sidebar > .sidebar"),t(s).each(function(){var e=t(this).data().widgetId,i=t("h2",this).html(),n=t(this).children(":not(h2)").clone(),r=t('<div class="grid-stack-item widget-to-add" data-gs-width="1" data-gs-height="1">').attr("data-widget-id",e),a=t('<div class="grid-stack-item-content has-layout">').appendTo(r),o=t('<header class="widget-header">').appendTo(a);t('<h2 class="widget-title">').html(i).appendTo(o),t('<article class="widget-content">').append(n).appendTo(a),function e(i,n,r){var a=n.clone(),o=!1;i.append(a.width(r)),a.draggable({appendTo:"body",helper:function(){return t(this).clone().css({zIndex:1e3})},revert:function(a){return!1!==a?(e(i,n,r),t("#layout-sidebar").removeClass("second-display"),!1):(o=!0,!0)},stop:function(){o&&(a.draggable("destroy").remove(),e(i,n,r))}})}(t(this).parent(),r,d)}),t("#layout-sidebar .addable-widgets li").on("mousemove",function(e){var i=t(this).offset(),n={left:e.pageX-i.left-16,top:e.pageY-i.top-16};t(".widget-to-add",this).css(n)}),n.resolve()},n.reject)}).promise()}t(document).ready(function(){t("#layout-sidebar").on("click",".widget-add-toggle",function(){return i(this.href).done(function(){t("#layout-sidebar").toggleClass("second-display")}),!1})}).on("widget-add",function(e,i){var n=i.getResponseHeader("X-Widget-Remove"),r=i.getResponseHeader("X-Widget-Id");n&&t('.addable-widgets li:has([data-widget-id="'+r+'"])').each(function(){t(".ui-draggable",this).draggable("destroy"),t(this).slideUp(function(){t(this).remove()})})}).on("widget-remove",function(e,i){i.getResponseHeader("X-Refresh")&&t("#layout-sidebar .sidebar-secondary-widget").remove()}).on("click",function(e){0===t(e.target).closest(".sidebar-secondary-widget").length&&t("#layout-sidebar").removeClass("second-display")})}(jQuery,STUDIP)},49:function(t,e){!function(t,e){"use strict";var i="studip-widget-grid",n=function(t,i){return e.api.POST(["widgets",t.id,i.el.data().widgetId],{data:{x:i.x,y:i.y,width:i.width,height:i.height}}).then(function(t,e,n){var r=n.getResponseHeader("X-Widget-Element-Id");return i.el.attr("data-element-id",r).find(".grid-stack-item-content").replaceWith(t),n})};function r(r,a){this.id=r,this.grid=t(a).addClass(i).gridstack({acceptWidgets:".widget-to-add",width:6,cellHeight:100,handle:".widget-header",resizable:{autoHide:!1}}),this.gridstack=this.grid.data("gridstack"),this.hashcode=JSON.stringify(this.serialize()).crc32(),this.grid.on("change",function(){this.store()}.bind(this)).on("added",function(e,i){this.gridstack.batchUpdate(),i.forEach(function(e){n(this,e).done(function(e,i,n){t(document).trigger("widget-add",[n])})}.bind(this)),this.gridstack.commit(),this.gridstack.enableResize(!0,!0)}.bind(this)).on("click",".widget-action:not([href])",function(i){t.Deferred(function(n){if(i.isDefaultPrevented())n.reject();else if(t(i.target).attr("data-confirm")){var r=t(i.target).data().confirm;e.Dialog.confirm(r).then(n.resolve,n.reject)}else n.resolve()}).done(function(){var n=t(i.target).closest("[data-action]").data().action,r=t(i.target).closest(".grid-stack-item").data("element-id"),a=["widgets",this.id,n,r];t(i.target).data().hasOwnProperty("admin")&&a.push(1),e.api.POST(a).then(function(n,r,a){return t.Deferred(function(o){var s,d,c,l=a.getResponseHeader("X-Widget-Execute"),h="nocontent"!==r,u=t(i.target).closest(".grid-stack-item"),g=!0;if(l){if(l=decodeURIComponent(l),s=e.extractCallback(l),a.getResponseHeader("Content-Type").match(/json/)&&h)try{d=t.parseJSON(a.responseText)}catch(t){console.log("error parsing json response",a.responseText),d=null}else d=a.responseText;c=setTimeout(function(){u.off("transitionrun transitionend"),o.resolve(n,r,a)},100),u.one("transitionrun",function(){clearTimeout(c),u.one("transitionend",function(){o.resolve(n,r,a)})}),g=s(d)}!1!==g&&h&&(t(".widget-content",u).html(n),o.resolve(n,r,a))}).promise()}.bind(this)).done(function(e,i,r){t(document).trigger("widget-"+n,[r])})}.bind(this)).always(function(){i.preventDefault()})}.bind(this)).on("resizestart resizestop",function(e){t(this).toggleClass("resizing","resizestart"===e.type)})}r.prototype.getElement=function(t){var e=this.grid.find('[data-element-id="'+t+'"]');if(0===e.length)throw"Unknown element with id "+t;return e},r.prototype.store=function(){e.api.PUT(["widgets",this.id],{before:function(){var t=this.serialize(),e=JSON.stringify(t).crc32(),i=e!==this.hashcode;return this.hashcode=e,i}.bind(this),data:function(){return{elements:this.serialize()}}.bind(this),async:!0})},r.prototype.serialize=function(){var e=[];return this.gridstack.grid.nodes.forEach(function(i){e.push({id:t(i.el).data("element-id"),x:i.x,y:i.y,width:i.width,height:i.height})}),e.sort(function(t,e){return t.y-e.y||t.x-e.x})},r.prototype.addElement=function(e,i){var n=t(e).hide();return this.grid.append(n),this.grid.packery("appended",n).packery(),this.initializeWidgets(n),(i=i||{}).hasOwnProperty("refresh")&&!i.refresh||this.refreshElementLookup(),n.show(),i.hasOwnProperty("position")&&this.grid.packery("fit",n[0],i.position.left,i.position.top),this},r.prototype.removeElement=function(t){var e=this.getElement(t);this.gridstack.removeWidget(e)},r.prototype.lockElement=function(e,i){void 0===i&&(i=!0);var n=this.getElement(e);this.gridstack.locked(n,i),this.gridstack.movable(n,!i),this.gridstack.resizable(n,!i),i?t(n).closest(".grid-stack-item").attr("data-gs-locked",""):t(n).closest(".grid-stack-item").removeAttr("data-gs-locked")},r.prototype.setRemovableElement=function(e,i){void 0===i&&(i=!0);var n=this.getElement(e);i?t(n).closest(".grid-stack-item").attr("data-gs-removable",""):t(n).closest(".grid-stack-item").removeAttr("data-gs-removable")},e.WidgetSystem={cache:{}},e.WidgetSystem.initialize=function(e){var i=t(e).data().widgetsystem;return this.cache.hasOwnProperty(i.id)||(this.cache[i.id]=new r(i.id,e)),this.cache[i.id]},e.WidgetSystem.get=function(t){if(!this.cache.hasOwnProperty(t))throw"Widgetsystem with id "+t+" has not been initialized yet";return this.cache[t]},t(document).ready(function(){t(".grid-stack").each(function(){e.WidgetSystem.initialize(this)})})}(jQuery,STUDIP)},50:function(t,e){!function(t){"use strict";var e=function(){var t,e,i,n=[];for(e=0;e<256;e+=1){for(t=e,i=0;i<8;i+=1)t=1&t?3988292384^t>>>1:t>>>1;n[e]=t}return n}();t.prototype.crc32=function(){var t,i=-1;for(t=0;t<this.length;t+=1)i=i>>>8^e[255&(i^this.charCodeAt(t))];return(-1^i)>>>0}}(String)},52:function(t,e,i){},53:function(t,e,i){"use strict";i.r(e);i(52),i(50),i(49),i(48),i(47)}});
//# sourceMappingURL=studip-widgets.js.map