(window.webpackJsonp=window.webpackJsonp||[]).push([[0],{228:function(t,e,i){
/*!
 * Cropper.js v1.4.0
 * https://fengyuanchen.github.io/cropperjs
 *
 * Copyright 2015-present Chen Fengyuan
 * Released under the MIT license
 *
 * Date: 2018-06-01T15:18:18.692Z
 */
t.exports=function(){"use strict";var t="undefined"!=typeof window,e=t?window:{},i="cropper-hidden",a=e.PointerEvent?"pointerdown":"touchstart mousedown",n=e.PointerEvent?"pointermove":"touchmove mousemove",o=e.PointerEvent?"pointerup pointercancel":"touchend touchcancel mouseup",r=/^(?:e|w|s|n|se|sw|ne|nw|all|crop|move|zoom)$/,h=/^data:/,s=/^data:image\/jpeg;base64,/,c=/^(?:img|canvas)$/i,d={viewMode:0,dragMode:"crop",initialAspectRatio:NaN,aspectRatio:NaN,data:null,preview:"",responsive:!0,restore:!0,checkCrossOrigin:!0,checkOrientation:!0,modal:!0,guides:!0,center:!0,highlight:!0,background:!0,autoCrop:!0,autoCropArea:.8,movable:!0,rotatable:!0,scalable:!0,zoomable:!0,zoomOnTouch:!0,zoomOnWheel:!0,wheelZoomRatio:.1,cropBoxMovable:!0,cropBoxResizable:!0,toggleDragModeOnDblclick:!0,minCanvasWidth:0,minCanvasHeight:0,minCropBoxWidth:0,minCropBoxHeight:0,minContainerWidth:200,minContainerHeight:100,ready:null,cropstart:null,cropmove:null,cropend:null,crop:null,zoom:null},p="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},l=function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")},m=function(){function t(t,e){for(var i=0;i<e.length;i++){var a=e[i];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(t,a.key,a)}}return function(e,i,a){return i&&t(e.prototype,i),a&&t(e,a),e}}(),u=function(t){if(Array.isArray(t)){for(var e=0,i=Array(t.length);e<t.length;e++)i[e]=t[e];return i}return Array.from(t)},g=Number.isNaN||e.isNaN;function f(t){return"number"==typeof t&&!g(t)}function v(t){return void 0===t}function w(t){return"object"===(void 0===t?"undefined":p(t))&&null!==t}var b=Object.prototype.hasOwnProperty;function x(t){if(!w(t))return!1;try{var e=t.constructor,i=e.prototype;return e&&i&&b.call(i,"isPrototypeOf")}catch(t){return!1}}function y(t){return"function"==typeof t}function M(t,e){if(t&&y(e))if(Array.isArray(t)||f(t.length)){var i=t.length,a=void 0;for(a=0;a<i&&!1!==e.call(t,t[a],a,t);a+=1);}else w(t)&&Object.keys(t).forEach(function(i){e.call(t,t[i],i,t)});return t}var C=Object.assign||function(t){for(var e=arguments.length,i=Array(e>1?e-1:0),a=1;a<e;a++)i[a-1]=arguments[a];return w(t)&&i.length>0&&i.forEach(function(e){w(e)&&Object.keys(e).forEach(function(i){t[i]=e[i]})}),t},D=/\.\d*(?:0|9){12}\d*$/i;function B(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:1e11;return D.test(t)?Math.round(t*e)/e:t}var k=/^(?:width|height|left|top|marginLeft|marginTop)$/;function T(t,e){var i=t.style;M(e,function(t,e){k.test(e)&&f(t)&&(t+="px"),i[e]=t})}function W(t,e){if(e)if(f(t.length))M(t,function(t){W(t,e)});else if(t.classList)t.classList.add(e);else{var i=t.className.trim();i?i.indexOf(e)<0&&(t.className=i+" "+e):t.className=e}}function N(t,e){e&&(f(t.length)?M(t,function(t){N(t,e)}):t.classList?t.classList.remove(e):t.className.indexOf(e)>=0&&(t.className=t.className.replace(e,"")))}function E(t,e,i){e&&(f(t.length)?M(t,function(t){E(t,e,i)}):i?W(t,e):N(t,e))}var H=/([a-z\d])([A-Z])/g;function z(t){return t.replace(H,"$1-$2").toLowerCase()}function L(t,e){return w(t[e])?t[e]:t.dataset?t.dataset[e]:t.getAttribute("data-"+z(e))}function O(t,e,i){w(i)?t[e]=i:t.dataset?t.dataset[e]=i:t.setAttribute("data-"+z(e),i)}function Y(t,e){if(w(t[e]))try{delete t[e]}catch(i){t[e]=void 0}else if(t.dataset)try{delete t.dataset[e]}catch(i){t.dataset[e]=void 0}else t.removeAttribute("data-"+z(e))}var X=/\s\s*/,R=function(){var i=!1;if(t){var a=!1,n=function(){},o=Object.defineProperty({},"once",{get:function(){return i=!0,a},set:function(t){a=t}});e.addEventListener("test",n,o),e.removeEventListener("test",n,o)}return i}();function A(t,e,i){var a=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{},n=i;e.trim().split(X).forEach(function(e){if(!R){var o=t.listeners;o&&o[e]&&o[e][i]&&(n=o[e][i],delete o[e][i],0===Object.keys(o[e]).length&&delete o[e],0===Object.keys(o).length&&delete t.listeners)}t.removeEventListener(e,n,a)})}function S(t,e,i){var a=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{},n=i;e.trim().split(X).forEach(function(e){if(a.once&&!R){var o=t.listeners,r=void 0===o?{}:o;n=function(){for(var o=arguments.length,h=Array(o),s=0;s<o;s++)h[s]=arguments[s];delete r[e][i],t.removeEventListener(e,n,a),i.apply(t,h)},r[e]||(r[e]={}),r[e][i]&&t.removeEventListener(e,r[e][i],a),r[e][i]=n,t.listeners=r}t.addEventListener(e,n,a)})}function I(t,e,i){var a=void 0;return y(Event)&&y(CustomEvent)?a=new CustomEvent(e,{detail:i,bubbles:!0,cancelable:!0}):(a=document.createEvent("CustomEvent")).initCustomEvent(e,!0,!0,i),t.dispatchEvent(a)}function P(t){var e=t.getBoundingClientRect();return{left:e.left+(window.pageXOffset-document.documentElement.clientLeft),top:e.top+(window.pageYOffset-document.documentElement.clientTop)}}var U=e.location,j=/^(https?:)\/\/([^:/?#]+):?(\d*)/i;function q(t){var e=t.match(j);return e&&(e[1]!==U.protocol||e[2]!==U.hostname||e[3]!==U.port)}function $(t){var e="timestamp="+(new Date).getTime();return t+(-1===t.indexOf("?")?"?":"&")+e}function Q(t){var e=t.rotate,i=t.scaleX,a=t.scaleY,n=t.translateX,o=t.translateY,r=[];f(n)&&0!==n&&r.push("translateX("+n+"px)"),f(o)&&0!==o&&r.push("translateY("+o+"px)"),f(e)&&0!==e&&r.push("rotate("+e+"deg)"),f(i)&&1!==i&&r.push("scaleX("+i+")"),f(a)&&1!==a&&r.push("scaleY("+a+")");var h=r.length?r.join(" "):"none";return{WebkitTransform:h,msTransform:h,transform:h}}function Z(t,e){var i=t.pageX,a=t.pageY,n={endX:i,endY:a};return e?n:C({startX:i,startY:a},n)}var F=Number.isFinite||e.isFinite;function J(t){var e=t.aspectRatio,i=t.height,a=t.width,n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"contain",o=function(t){return F(t)&&t>0};if(o(a)&&o(i)){var r=i*e;"contain"===n&&r>a||"cover"===n&&r<a?i=a/e:a=i*e}else o(a)?i=a/e:o(i)&&(a=i*e);return{width:a,height:i}}var K=String.fromCharCode,V=/^data:.*,/;function G(t){var e=new DataView(t),i=void 0,a=void 0,n=void 0,o=void 0;if(255===e.getUint8(0)&&216===e.getUint8(1))for(var r=e.byteLength,h=2;h<r;){if(255===e.getUint8(h)&&225===e.getUint8(h+1)){n=h;break}h+=1}if(n){var s=n+4,c=n+10;if("Exif"===function(t,e,i){var a="",n=void 0;for(i+=e,n=e;n<i;n+=1)a+=K(t.getUint8(n));return a}(e,s,4)){var d=e.getUint16(c);if(((a=18761===d)||19789===d)&&42===e.getUint16(c+2,a)){var p=e.getUint32(c+4,a);p>=8&&(o=c+p)}}}if(o){var l=e.getUint16(o,a),m=void 0,u=void 0;for(u=0;u<l;u+=1)if(m=o+12*u+2,274===e.getUint16(m,a)){m+=8,i=e.getUint16(m,a),e.setUint16(m,1,a);break}}return i}var _={render:function(){this.initContainer(),this.initCanvas(),this.initCropBox(),this.renderCanvas(),this.cropped&&this.renderCropBox()},initContainer:function(){var t=this.element,e=this.options,a=this.container,n=this.cropper;W(n,i),N(t,i);var o={width:Math.max(a.offsetWidth,Number(e.minContainerWidth)||200),height:Math.max(a.offsetHeight,Number(e.minContainerHeight)||100)};this.containerData=o,T(n,{width:o.width,height:o.height}),W(t,i),N(n,i)},initCanvas:function(){var t=this.containerData,e=this.imageData,i=this.options.viewMode,a=Math.abs(e.rotate)%180==90,n=a?e.naturalHeight:e.naturalWidth,o=a?e.naturalWidth:e.naturalHeight,r=n/o,h=t.width,s=t.height;t.height*r>t.width?3===i?h=t.height*r:s=t.width/r:3===i?s=t.width/r:h=t.height*r;var c={aspectRatio:r,naturalWidth:n,naturalHeight:o,width:h,height:s};c.left=(t.width-h)/2,c.top=(t.height-s)/2,c.oldLeft=c.left,c.oldTop=c.top,this.canvasData=c,this.limited=1===i||2===i,this.limitCanvas(!0,!0),this.initialImageData=C({},e),this.initialCanvasData=C({},c)},limitCanvas:function(t,e){var i=this.options,a=this.containerData,n=this.canvasData,o=this.cropBoxData,r=i.viewMode,h=n.aspectRatio,s=this.cropped&&o;if(t){var c=Number(i.minCanvasWidth)||0,d=Number(i.minCanvasHeight)||0;r>1?(c=Math.max(c,a.width),d=Math.max(d,a.height),3===r&&(d*h>c?c=d*h:d=c/h)):r>0&&(c?c=Math.max(c,s?o.width:0):d?d=Math.max(d,s?o.height:0):s&&(c=o.width,(d=o.height)*h>c?c=d*h:d=c/h));var p=J({aspectRatio:h,width:c,height:d});c=p.width,d=p.height,n.minWidth=c,n.minHeight=d,n.maxWidth=1/0,n.maxHeight=1/0}if(e)if(r){var l=a.width-n.width,m=a.height-n.height;n.minLeft=Math.min(0,l),n.minTop=Math.min(0,m),n.maxLeft=Math.max(0,l),n.maxTop=Math.max(0,m),s&&this.limited&&(n.minLeft=Math.min(o.left,o.left+(o.width-n.width)),n.minTop=Math.min(o.top,o.top+(o.height-n.height)),n.maxLeft=o.left,n.maxTop=o.top,2===r&&(n.width>=a.width&&(n.minLeft=Math.min(0,l),n.maxLeft=Math.max(0,l)),n.height>=a.height&&(n.minTop=Math.min(0,m),n.maxTop=Math.max(0,m))))}else n.minLeft=-n.width,n.minTop=-n.height,n.maxLeft=a.width,n.maxTop=a.height},renderCanvas:function(t,e){var i=this.canvasData,a=this.imageData;if(e){var n=function(t){var e=t.width,i=t.height,a=t.degree;if(90==(a=Math.abs(a)%180))return{width:i,height:e};var n=a%90*Math.PI/180,o=Math.sin(n),r=Math.cos(n),h=e*r+i*o,s=e*o+i*r;return a>90?{width:s,height:h}:{width:h,height:s}}({width:a.naturalWidth*Math.abs(a.scaleX||1),height:a.naturalHeight*Math.abs(a.scaleY||1),degree:a.rotate||0}),o=n.width,r=n.height,h=i.width*(o/i.naturalWidth),s=i.height*(r/i.naturalHeight);i.left-=(h-i.width)/2,i.top-=(s-i.height)/2,i.width=h,i.height=s,i.aspectRatio=o/r,i.naturalWidth=o,i.naturalHeight=r,this.limitCanvas(!0,!1)}(i.width>i.maxWidth||i.width<i.minWidth)&&(i.left=i.oldLeft),(i.height>i.maxHeight||i.height<i.minHeight)&&(i.top=i.oldTop),i.width=Math.min(Math.max(i.width,i.minWidth),i.maxWidth),i.height=Math.min(Math.max(i.height,i.minHeight),i.maxHeight),this.limitCanvas(!1,!0),i.left=Math.min(Math.max(i.left,i.minLeft),i.maxLeft),i.top=Math.min(Math.max(i.top,i.minTop),i.maxTop),i.oldLeft=i.left,i.oldTop=i.top,T(this.canvas,C({width:i.width,height:i.height},Q({translateX:i.left,translateY:i.top}))),this.renderImage(t),this.cropped&&this.limited&&this.limitCropBox(!0,!0)},renderImage:function(t){var e=this.canvasData,i=this.imageData,a=i.naturalWidth*(e.width/e.naturalWidth),n=i.naturalHeight*(e.height/e.naturalHeight);C(i,{width:a,height:n,left:(e.width-a)/2,top:(e.height-n)/2}),T(this.image,C({width:i.width,height:i.height},Q(C({translateX:i.left,translateY:i.top},i)))),t&&this.output()},initCropBox:function(){var t=this.options,e=this.canvasData,i=t.aspectRatio||t.initialAspectRatio,a=Number(t.autoCropArea)||.8,n={width:e.width,height:e.height};i&&(e.height*i>e.width?n.height=n.width/i:n.width=n.height*i),this.cropBoxData=n,this.limitCropBox(!0,!0),n.width=Math.min(Math.max(n.width,n.minWidth),n.maxWidth),n.height=Math.min(Math.max(n.height,n.minHeight),n.maxHeight),n.width=Math.max(n.minWidth,n.width*a),n.height=Math.max(n.minHeight,n.height*a),n.left=e.left+(e.width-n.width)/2,n.top=e.top+(e.height-n.height)/2,n.oldLeft=n.left,n.oldTop=n.top,this.initialCropBoxData=C({},n)},limitCropBox:function(t,e){var i=this.options,a=this.containerData,n=this.canvasData,o=this.cropBoxData,r=this.limited,h=i.aspectRatio;if(t){var s=Number(i.minCropBoxWidth)||0,c=Number(i.minCropBoxHeight)||0,d=Math.min(a.width,r?n.width:a.width),p=Math.min(a.height,r?n.height:a.height);s=Math.min(s,a.width),c=Math.min(c,a.height),h&&(s&&c?c*h>s?c=s/h:s=c*h:s?c=s/h:c&&(s=c*h),p*h>d?p=d/h:d=p*h),o.minWidth=Math.min(s,d),o.minHeight=Math.min(c,p),o.maxWidth=d,o.maxHeight=p}e&&(r?(o.minLeft=Math.max(0,n.left),o.minTop=Math.max(0,n.top),o.maxLeft=Math.min(a.width,n.left+n.width)-o.width,o.maxTop=Math.min(a.height,n.top+n.height)-o.height):(o.minLeft=0,o.minTop=0,o.maxLeft=a.width-o.width,o.maxTop=a.height-o.height))},renderCropBox:function(){var t=this.options,e=this.containerData,i=this.cropBoxData;(i.width>i.maxWidth||i.width<i.minWidth)&&(i.left=i.oldLeft),(i.height>i.maxHeight||i.height<i.minHeight)&&(i.top=i.oldTop),i.width=Math.min(Math.max(i.width,i.minWidth),i.maxWidth),i.height=Math.min(Math.max(i.height,i.minHeight),i.maxHeight),this.limitCropBox(!1,!0),i.left=Math.min(Math.max(i.left,i.minLeft),i.maxLeft),i.top=Math.min(Math.max(i.top,i.minTop),i.maxTop),i.oldLeft=i.left,i.oldTop=i.top,t.movable&&t.cropBoxMovable&&O(this.face,"cropperAction",i.width>=e.width&&i.height>=e.height?"move":"all"),T(this.cropBox,C({width:i.width,height:i.height},Q({translateX:i.left,translateY:i.top}))),this.cropped&&this.limited&&this.limitCanvas(!0,!0),this.disabled||this.output()},output:function(){this.preview(),I(this.element,"crop",this.getData())}},tt={initPreview:function(){var t=this.crossOrigin,e=this.options.preview,i=t?this.crossOriginUrl:this.url,a=document.createElement("img");if(t&&(a.crossOrigin=t),a.src=i,this.viewBox.appendChild(a),this.viewBoxImage=a,e){var n=e;"string"==typeof e?n=this.element.ownerDocument.querySelectorAll(e):e.querySelector&&(n=[e]),this.previews=n,M(n,function(e){var a=document.createElement("img");O(e,"cropperPreview",{width:e.offsetWidth,height:e.offsetHeight,html:e.innerHTML}),t&&(a.crossOrigin=t),a.src=i,a.style.cssText='display:block;width:100%;height:auto;min-width:0!important;min-height:0!important;max-width:none!important;max-height:none!important;image-orientation:0deg!important;"',e.innerHTML="",e.appendChild(a)})}},resetPreview:function(){M(this.previews,function(t){var e=L(t,"cropperPreview");T(t,{width:e.width,height:e.height}),t.innerHTML=e.html,Y(t,"cropperPreview")})},preview:function(){var t=this.imageData,e=this.canvasData,i=this.cropBoxData,a=i.width,n=i.height,o=t.width,r=t.height,h=i.left-e.left-t.left,s=i.top-e.top-t.top;this.cropped&&!this.disabled&&(T(this.viewBoxImage,C({width:o,height:r},Q(C({translateX:-h,translateY:-s},t)))),M(this.previews,function(e){var i=L(e,"cropperPreview"),c=i.width,d=i.height,p=c,l=d,m=1;a&&(l=n*(m=c/a)),n&&l>d&&(p=a*(m=d/n),l=d),T(e,{width:p,height:l}),T(e.getElementsByTagName("img")[0],C({width:o*m,height:r*m},Q(C({translateX:-h*m,translateY:-s*m},t))))}))}},et={bind:function(){var t=this.element,e=this.options,i=this.cropper;y(e.cropstart)&&S(t,"cropstart",e.cropstart),y(e.cropmove)&&S(t,"cropmove",e.cropmove),y(e.cropend)&&S(t,"cropend",e.cropend),y(e.crop)&&S(t,"crop",e.crop),y(e.zoom)&&S(t,"zoom",e.zoom),S(i,a,this.onCropStart=this.cropStart.bind(this)),e.zoomable&&e.zoomOnWheel&&S(i,"wheel mousewheel DOMMouseScroll",this.onWheel=this.wheel.bind(this)),e.toggleDragModeOnDblclick&&S(i,"dblclick",this.onDblclick=this.dblclick.bind(this)),S(t.ownerDocument,n,this.onCropMove=this.cropMove.bind(this)),S(t.ownerDocument,o,this.onCropEnd=this.cropEnd.bind(this)),e.responsive&&S(window,"resize",this.onResize=this.resize.bind(this))},unbind:function(){var t=this.element,e=this.options,i=this.cropper;y(e.cropstart)&&A(t,"cropstart",e.cropstart),y(e.cropmove)&&A(t,"cropmove",e.cropmove),y(e.cropend)&&A(t,"cropend",e.cropend),y(e.crop)&&A(t,"crop",e.crop),y(e.zoom)&&A(t,"zoom",e.zoom),A(i,a,this.onCropStart),e.zoomable&&e.zoomOnWheel&&A(i,"wheel mousewheel DOMMouseScroll",this.onWheel),e.toggleDragModeOnDblclick&&A(i,"dblclick",this.onDblclick),A(t.ownerDocument,n,this.onCropMove),A(t.ownerDocument,o,this.onCropEnd),e.responsive&&A(window,"resize",this.onResize)}},it={resize:function(){var t=this.options,e=this.container,i=this.containerData,a=Number(t.minContainerWidth)||200,n=Number(t.minContainerHeight)||100;if(!(this.disabled||i.width<=a||i.height<=n)){var o=e.offsetWidth/i.width;if(1!==o||e.offsetHeight!==i.height){var r=void 0,h=void 0;t.restore&&(r=this.getCanvasData(),h=this.getCropBoxData()),this.render(),t.restore&&(this.setCanvasData(M(r,function(t,e){r[e]=t*o})),this.setCropBoxData(M(h,function(t,e){h[e]=t*o})))}}},dblclick:function(){var t,e;this.disabled||"none"===this.options.dragMode||this.setDragMode((t=this.dragBox,e="cropper-crop",(t.classList?t.classList.contains(e):t.className.indexOf(e)>-1)?"move":"crop"))},wheel:function(t){var e=this,i=Number(this.options.wheelZoomRatio)||.1,a=1;this.disabled||(t.preventDefault(),this.wheeling||(this.wheeling=!0,setTimeout(function(){e.wheeling=!1},50),t.deltaY?a=t.deltaY>0?1:-1:t.wheelDelta?a=-t.wheelDelta/120:t.detail&&(a=t.detail>0?1:-1),this.zoom(-a*i,t)))},cropStart:function(t){if(!this.disabled){var e=this.options,i=this.pointers,a=void 0;t.changedTouches?M(t.changedTouches,function(t){i[t.identifier]=Z(t)}):i[t.pointerId||0]=Z(t),a=Object.keys(i).length>1&&e.zoomable&&e.zoomOnTouch?"zoom":L(t.target,"cropperAction"),r.test(a)&&!1!==I(this.element,"cropstart",{originalEvent:t,action:a})&&(t.preventDefault(),this.action=a,this.cropping=!1,"crop"===a&&(this.cropping=!0,W(this.dragBox,"cropper-modal")))}},cropMove:function(t){var e=this.action;if(!this.disabled&&e){var i=this.pointers;t.preventDefault(),!1!==I(this.element,"cropmove",{originalEvent:t,action:e})&&(t.changedTouches?M(t.changedTouches,function(t){C(i[t.identifier],Z(t,!0))}):C(i[t.pointerId||0],Z(t,!0)),this.change(t))}},cropEnd:function(t){if(!this.disabled){var e=this.action,i=this.pointers;t.changedTouches?M(t.changedTouches,function(t){delete i[t.identifier]}):delete i[t.pointerId||0],e&&(t.preventDefault(),Object.keys(i).length||(this.action=""),this.cropping&&(this.cropping=!1,E(this.dragBox,"cropper-modal",this.cropped&&this.options.modal)),I(this.element,"cropend",{originalEvent:t,action:e}))}}},at={change:function(t){var e=this.options,a=this.canvasData,n=this.containerData,o=this.cropBoxData,r=this.pointers,h=this.action,s=e.aspectRatio,c=o.left,d=o.top,p=o.width,l=o.height,m=c+p,u=d+l,g=0,f=0,v=n.width,w=n.height,b=!0,x=void 0;!s&&t.shiftKey&&(s=p&&l?p/l:1),this.limited&&(g=o.minLeft,f=o.minTop,v=g+Math.min(n.width,a.width,a.left+a.width),w=f+Math.min(n.height,a.height,a.top+a.height));var y=r[Object.keys(r)[0]],D={x:y.endX-y.startX,y:y.endY-y.startY},B=function(t){switch(t){case"e":m+D.x>v&&(D.x=v-m);break;case"w":c+D.x<g&&(D.x=g-c);break;case"n":d+D.y<f&&(D.y=f-d);break;case"s":u+D.y>w&&(D.y=w-u)}};switch(h){case"all":c+=D.x,d+=D.y;break;case"e":if(D.x>=0&&(m>=v||s&&(d<=f||u>=w))){b=!1;break}B("e"),(p+=D.x)<0&&(h="w",c-=p=-p),s&&(l=p/s,d+=(o.height-l)/2);break;case"n":if(D.y<=0&&(d<=f||s&&(c<=g||m>=v))){b=!1;break}B("n"),l-=D.y,d+=D.y,l<0&&(h="s",d-=l=-l),s&&(p=l*s,c+=(o.width-p)/2);break;case"w":if(D.x<=0&&(c<=g||s&&(d<=f||u>=w))){b=!1;break}B("w"),p-=D.x,c+=D.x,p<0&&(h="e",c-=p=-p),s&&(l=p/s,d+=(o.height-l)/2);break;case"s":if(D.y>=0&&(u>=w||s&&(c<=g||m>=v))){b=!1;break}B("s"),(l+=D.y)<0&&(h="n",d-=l=-l),s&&(p=l*s,c+=(o.width-p)/2);break;case"ne":if(s){if(D.y<=0&&(d<=f||m>=v)){b=!1;break}B("n"),l-=D.y,d+=D.y,p=l*s}else B("n"),B("e"),D.x>=0?m<v?p+=D.x:D.y<=0&&d<=f&&(b=!1):p+=D.x,D.y<=0?d>f&&(l-=D.y,d+=D.y):(l-=D.y,d+=D.y);p<0&&l<0?(h="sw",d-=l=-l,c-=p=-p):p<0?(h="nw",c-=p=-p):l<0&&(h="se",d-=l=-l);break;case"nw":if(s){if(D.y<=0&&(d<=f||c<=g)){b=!1;break}B("n"),l-=D.y,d+=D.y,p=l*s,c+=o.width-p}else B("n"),B("w"),D.x<=0?c>g?(p-=D.x,c+=D.x):D.y<=0&&d<=f&&(b=!1):(p-=D.x,c+=D.x),D.y<=0?d>f&&(l-=D.y,d+=D.y):(l-=D.y,d+=D.y);p<0&&l<0?(h="se",d-=l=-l,c-=p=-p):p<0?(h="ne",c-=p=-p):l<0&&(h="sw",d-=l=-l);break;case"sw":if(s){if(D.x<=0&&(c<=g||u>=w)){b=!1;break}B("w"),p-=D.x,c+=D.x,l=p/s}else B("s"),B("w"),D.x<=0?c>g?(p-=D.x,c+=D.x):D.y>=0&&u>=w&&(b=!1):(p-=D.x,c+=D.x),D.y>=0?u<w&&(l+=D.y):l+=D.y;p<0&&l<0?(h="ne",d-=l=-l,c-=p=-p):p<0?(h="se",c-=p=-p):l<0&&(h="nw",d-=l=-l);break;case"se":if(s){if(D.x>=0&&(m>=v||u>=w)){b=!1;break}B("e"),p+=D.x,l=p/s}else B("s"),B("e"),D.x>=0?m<v?p+=D.x:D.y>=0&&u>=w&&(b=!1):p+=D.x,D.y>=0?u<w&&(l+=D.y):l+=D.y;p<0&&l<0?(h="nw",d-=l=-l,c-=p=-p):p<0?(h="sw",c-=p=-p):l<0&&(h="ne",d-=l=-l);break;case"move":this.move(D.x,D.y),b=!1;break;case"zoom":this.zoom(function(t){var e=C({},t),i=[];return M(t,function(t,a){delete e[a],M(e,function(e){var a=Math.abs(t.startX-e.startX),n=Math.abs(t.startY-e.startY),o=Math.abs(t.endX-e.endX),r=Math.abs(t.endY-e.endY),h=Math.sqrt(a*a+n*n),s=(Math.sqrt(o*o+r*r)-h)/h;i.push(s)})}),i.sort(function(t,e){return Math.abs(t)<Math.abs(e)}),i[0]}(r),t),b=!1;break;case"crop":if(!D.x||!D.y){b=!1;break}x=P(this.cropper),c=y.startX-x.left,d=y.startY-x.top,p=o.minWidth,l=o.minHeight,D.x>0?h=D.y>0?"se":"ne":D.x<0&&(c-=p,h=D.y>0?"sw":"nw"),D.y<0&&(d-=l),this.cropped||(N(this.cropBox,i),this.cropped=!0,this.limited&&this.limitCropBox(!0,!0))}b&&(o.width=p,o.height=l,o.left=c,o.top=d,this.action=h,this.renderCropBox()),M(r,function(t){t.startX=t.endX,t.startY=t.endY})}},nt={crop:function(){return!this.ready||this.cropped||this.disabled||(this.cropped=!0,this.limitCropBox(!0,!0),this.options.modal&&W(this.dragBox,"cropper-modal"),N(this.cropBox,i),this.setCropBoxData(this.initialCropBoxData)),this},reset:function(){return this.ready&&!this.disabled&&(this.imageData=C({},this.initialImageData),this.canvasData=C({},this.initialCanvasData),this.cropBoxData=C({},this.initialCropBoxData),this.renderCanvas(),this.cropped&&this.renderCropBox()),this},clear:function(){return this.cropped&&!this.disabled&&(C(this.cropBoxData,{left:0,top:0,width:0,height:0}),this.cropped=!1,this.renderCropBox(),this.limitCanvas(!0,!0),this.renderCanvas(),N(this.dragBox,"cropper-modal"),W(this.cropBox,i)),this},replace:function(t){var e=arguments.length>1&&void 0!==arguments[1]&&arguments[1];return!this.disabled&&t&&(this.isImg&&(this.element.src=t),e?(this.url=t,this.image.src=t,this.ready&&(this.viewBoxImage.src=t,M(this.previews,function(e){e.getElementsByTagName("img")[0].src=t}))):(this.isImg&&(this.replaced=!0),this.options.data=null,this.uncreate(),this.load(t))),this},enable:function(){return this.ready&&this.disabled&&(this.disabled=!1,N(this.cropper,"cropper-disabled")),this},disable:function(){return this.ready&&!this.disabled&&(this.disabled=!0,W(this.cropper,"cropper-disabled")),this},destroy:function(){var t=this.element;return L(t,"cropper")?(this.isImg&&this.replaced&&(t.src=this.originalUrl),this.uncreate(),Y(t,"cropper"),this):this},move:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:t,i=this.canvasData,a=i.left,n=i.top;return this.moveTo(v(t)?t:a+Number(t),v(e)?e:n+Number(e))},moveTo:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:t,i=this.canvasData,a=!1;return t=Number(t),e=Number(e),this.ready&&!this.disabled&&this.options.movable&&(f(t)&&(i.left=t,a=!0),f(e)&&(i.top=e,a=!0),a&&this.renderCanvas(!0)),this},zoom:function(t,e){var i=this.canvasData;return t=(t=Number(t))<0?1/(1-t):1+t,this.zoomTo(i.width*t/i.naturalWidth,null,e)},zoomTo:function(t,e,i){var a=this.options,n=this.canvasData,o=n.width,r=n.height,h=n.naturalWidth,s=n.naturalHeight;if((t=Number(t))>=0&&this.ready&&!this.disabled&&a.zoomable){var c=h*t,d=s*t;if(!1===I(this.element,"zoom",{ratio:t,oldRatio:o/h,originalEvent:i}))return this;if(i){var p=this.pointers,l=P(this.cropper),m=p&&Object.keys(p).length?function(t){var e=0,i=0,a=0;return M(t,function(t){var n=t.startX,o=t.startY;e+=n,i+=o,a+=1}),{pageX:e/=a,pageY:i/=a}}(p):{pageX:i.pageX,pageY:i.pageY};n.left-=(c-o)*((m.pageX-l.left-n.left)/o),n.top-=(d-r)*((m.pageY-l.top-n.top)/r)}else x(e)&&f(e.x)&&f(e.y)?(n.left-=(c-o)*((e.x-n.left)/o),n.top-=(d-r)*((e.y-n.top)/r)):(n.left-=(c-o)/2,n.top-=(d-r)/2);n.width=c,n.height=d,this.renderCanvas(!0)}return this},rotate:function(t){return this.rotateTo((this.imageData.rotate||0)+Number(t))},rotateTo:function(t){return f(t=Number(t))&&this.ready&&!this.disabled&&this.options.rotatable&&(this.imageData.rotate=t%360,this.renderCanvas(!0,!0)),this},scaleX:function(t){var e=this.imageData.scaleY;return this.scale(t,f(e)?e:1)},scaleY:function(t){var e=this.imageData.scaleX;return this.scale(f(e)?e:1,t)},scale:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:t,i=this.imageData,a=!1;return t=Number(t),e=Number(e),this.ready&&!this.disabled&&this.options.scalable&&(f(t)&&(i.scaleX=t,a=!0),f(e)&&(i.scaleY=e,a=!0),a&&this.renderCanvas(!0,!0)),this},getData:function(){var t=arguments.length>0&&void 0!==arguments[0]&&arguments[0],e=this.options,i=this.imageData,a=this.canvasData,n=this.cropBoxData,o=void 0;if(this.ready&&this.cropped){o={x:n.left-a.left,y:n.top-a.top,width:n.width,height:n.height};var r=i.width/i.naturalWidth;if(M(o,function(t,e){o[e]=t/r}),t){var h=Math.round(o.y+o.height),s=Math.round(o.x+o.width);o.x=Math.round(o.x),o.y=Math.round(o.y),o.width=s-o.x,o.height=h-o.y}}else o={x:0,y:0,width:0,height:0};return e.rotatable&&(o.rotate=i.rotate||0),e.scalable&&(o.scaleX=i.scaleX||1,o.scaleY=i.scaleY||1),o},setData:function(t){var e=this.options,i=this.imageData,a=this.canvasData,n={};if(this.ready&&!this.disabled&&x(t)){var o=!1;e.rotatable&&f(t.rotate)&&t.rotate!==i.rotate&&(i.rotate=t.rotate,o=!0),e.scalable&&(f(t.scaleX)&&t.scaleX!==i.scaleX&&(i.scaleX=t.scaleX,o=!0),f(t.scaleY)&&t.scaleY!==i.scaleY&&(i.scaleY=t.scaleY,o=!0)),o&&this.renderCanvas(!0,!0);var r=i.width/i.naturalWidth;f(t.x)&&(n.left=t.x*r+a.left),f(t.y)&&(n.top=t.y*r+a.top),f(t.width)&&(n.width=t.width*r),f(t.height)&&(n.height=t.height*r),this.setCropBoxData(n)}return this},getContainerData:function(){return this.ready?C({},this.containerData):{}},getImageData:function(){return this.sized?C({},this.imageData):{}},getCanvasData:function(){var t=this.canvasData,e={};return this.ready&&M(["left","top","width","height","naturalWidth","naturalHeight"],function(i){e[i]=t[i]}),e},setCanvasData:function(t){var e=this.canvasData,i=e.aspectRatio;return this.ready&&!this.disabled&&x(t)&&(f(t.left)&&(e.left=t.left),f(t.top)&&(e.top=t.top),f(t.width)?(e.width=t.width,e.height=t.width/i):f(t.height)&&(e.height=t.height,e.width=t.height*i),this.renderCanvas(!0)),this},getCropBoxData:function(){var t=this.cropBoxData,e=void 0;return this.ready&&this.cropped&&(e={left:t.left,top:t.top,width:t.width,height:t.height}),e||{}},setCropBoxData:function(t){var e=this.cropBoxData,i=this.options.aspectRatio,a=void 0,n=void 0;return this.ready&&this.cropped&&!this.disabled&&x(t)&&(f(t.left)&&(e.left=t.left),f(t.top)&&(e.top=t.top),f(t.width)&&t.width!==e.width&&(a=!0,e.width=t.width),f(t.height)&&t.height!==e.height&&(n=!0,e.height=t.height),i&&(a?e.height=e.width/i:n&&(e.width=e.height*i)),this.renderCropBox()),this},getCroppedCanvas:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};if(!this.ready||!window.HTMLCanvasElement)return null;var e=this.canvasData,i=function(t,e,i,a){var n=e.aspectRatio,o=e.naturalWidth,r=e.naturalHeight,h=e.rotate,s=void 0===h?0:h,c=e.scaleX,d=void 0===c?1:c,p=e.scaleY,l=void 0===p?1:p,m=i.aspectRatio,g=i.naturalWidth,f=i.naturalHeight,v=a.fillColor,w=void 0===v?"transparent":v,b=a.imageSmoothingEnabled,x=void 0===b||b,y=a.imageSmoothingQuality,M=void 0===y?"low":y,C=a.maxWidth,D=void 0===C?1/0:C,k=a.maxHeight,T=void 0===k?1/0:k,W=a.minWidth,N=void 0===W?0:W,E=a.minHeight,H=void 0===E?0:E,z=document.createElement("canvas"),L=z.getContext("2d"),O=J({aspectRatio:m,width:D,height:T}),Y=J({aspectRatio:m,width:N,height:H},"cover"),X=Math.min(O.width,Math.max(Y.width,g)),R=Math.min(O.height,Math.max(Y.height,f)),A=J({aspectRatio:n,width:D,height:T}),S=J({aspectRatio:n,width:N,height:H},"cover"),I=Math.min(A.width,Math.max(S.width,o)),P=Math.min(A.height,Math.max(S.height,r)),U=[-I/2,-P/2,I,P];return z.width=B(X),z.height=B(R),L.fillStyle=w,L.fillRect(0,0,X,R),L.save(),L.translate(X/2,R/2),L.rotate(s*Math.PI/180),L.scale(d,l),L.imageSmoothingEnabled=x,L.imageSmoothingQuality=M,L.drawImage.apply(L,[t].concat(u(U.map(function(t){return Math.floor(B(t))})))),L.restore(),z}(this.image,this.imageData,e,t);if(!this.cropped)return i;var a=this.getData(),n=a.x,o=a.y,r=a.width,h=a.height,s=i.width/Math.floor(e.naturalWidth);1!==s&&(n*=s,o*=s,r*=s,h*=s);var c=r/h,d=J({aspectRatio:c,width:t.maxWidth||1/0,height:t.maxHeight||1/0}),p=J({aspectRatio:c,width:t.minWidth||0,height:t.minHeight||0},"cover"),l=J({aspectRatio:c,width:t.width||(1!==s?i.width:r),height:t.height||(1!==s?i.height:h)}),m=l.width,g=l.height;m=Math.min(d.width,Math.max(p.width,m)),g=Math.min(d.height,Math.max(p.height,g));var f=document.createElement("canvas"),v=f.getContext("2d");f.width=B(m),f.height=B(g),v.fillStyle=t.fillColor||"transparent",v.fillRect(0,0,m,g);var w=t.imageSmoothingEnabled,b=void 0===w||w,x=t.imageSmoothingQuality;v.imageSmoothingEnabled=b,x&&(v.imageSmoothingQuality=x);var y=i.width,M=i.height,C=n,D=o,k=void 0,T=void 0,W=void 0,N=void 0,E=void 0,H=void 0;C<=-r||C>y?(C=0,k=0,W=0,E=0):C<=0?(W=-C,C=0,k=Math.min(y,r+C),E=k):C<=y&&(W=0,k=Math.min(r,y-C),E=k),k<=0||D<=-h||D>M?(D=0,T=0,N=0,H=0):D<=0?(N=-D,D=0,T=Math.min(M,h+D),H=T):D<=M&&(N=0,T=Math.min(h,M-D),H=T);var z=[C,D,k,T];if(E>0&&H>0){var L=m/r;z.push(W*L,N*L,E*L,H*L)}return v.drawImage.apply(v,[i].concat(u(z.map(function(t){return Math.floor(B(t))})))),f},setAspectRatio:function(t){var e=this.options;return this.disabled||v(t)||(e.aspectRatio=Math.max(0,t)||NaN,this.ready&&(this.initCropBox(),this.cropped&&this.renderCropBox())),this},setDragMode:function(t){var e=this.options,i=this.dragBox,a=this.face;if(this.ready&&!this.disabled){var n="crop"===t,o=e.movable&&"move"===t;t=n||o?t:"none",e.dragMode=t,O(i,"cropperAction",t),E(i,"cropper-crop",n),E(i,"cropper-move",o),e.cropBoxMovable||(O(a,"cropperAction",t),E(a,"cropper-crop",n),E(a,"cropper-move",o))}return this}},ot=e.Cropper,rt=function(){function t(e){var i=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};if(l(this,t),!e||!c.test(e.tagName))throw new Error("The first argument is required and must be an <img> or <canvas> element.");this.element=e,this.options=C({},d,x(i)&&i),this.cropped=!1,this.disabled=!1,this.pointers={},this.ready=!1,this.reloading=!1,this.replaced=!1,this.sized=!1,this.sizing=!1,this.init()}return m(t,[{key:"init",value:function(){var t=this.element,e=t.tagName.toLowerCase(),i=void 0;if(!L(t,"cropper")){if(O(t,"cropper",this),"img"===e){if(this.isImg=!0,i=t.getAttribute("src")||"",this.originalUrl=i,!i)return;i=t.src}else"canvas"===e&&window.HTMLCanvasElement&&(i=t.toDataURL());this.load(i)}}},{key:"load",value:function(t){var e=this;if(t){this.url=t,this.imageData={};var i=this.element,a=this.options;if(a.rotatable||a.scalable||(a.checkOrientation=!1),a.checkOrientation&&window.ArrayBuffer)if(h.test(t))s.test(t)?this.read((n=t.replace(V,""),o=atob(n),r=new ArrayBuffer(o.length),M(c=new Uint8Array(r),function(t,e){c[e]=o.charCodeAt(e)}),r)):this.clone();else{var n,o,r,c,d=new XMLHttpRequest;this.reloading=!0,this.xhr=d;var p=function(){e.reloading=!1,e.xhr=null};d.ontimeout=p,d.onabort=p,d.onerror=function(){p(),e.clone()},d.onload=function(){p(),e.read(d.response)},a.checkCrossOrigin&&q(t)&&i.crossOrigin&&(t=$(t)),d.open("get",t),d.responseType="arraybuffer",d.withCredentials="use-credentials"===i.crossOrigin,d.send()}else this.clone()}}},{key:"read",value:function(t){var e=this.options,i=this.imageData,a=G(t),n=0,o=1,r=1;if(a>1){this.url=function(t,e){var i="";return M(new Uint8Array(t),function(t){i+=K(t)}),"data:"+e+";base64,"+btoa(i)}(t,"image/jpeg");var h=function(t){var e=0,i=1,a=1;switch(t){case 2:i=-1;break;case 3:e=-180;break;case 4:a=-1;break;case 5:e=90,a=-1;break;case 6:e=90;break;case 7:e=90,i=-1;break;case 8:e=-90}return{rotate:e,scaleX:i,scaleY:a}}(a);n=h.rotate,o=h.scaleX,r=h.scaleY}e.rotatable&&(i.rotate=n),e.scalable&&(i.scaleX=o,i.scaleY=r),this.clone()}},{key:"clone",value:function(){var t=this.element,e=this.url,i=void 0,a=void 0;this.options.checkCrossOrigin&&q(e)&&((i=t.crossOrigin)?a=e:(i="anonymous",a=$(e))),this.crossOrigin=i,this.crossOriginUrl=a;var n=document.createElement("img");i&&(n.crossOrigin=i),n.src=a||e,this.image=n,n.onload=this.start.bind(this),n.onerror=this.stop.bind(this),W(n,"cropper-hide"),t.parentNode.insertBefore(n,t.nextSibling)}},{key:"start",value:function(){var t=this,i=this.isImg?this.element:this.image;i.onload=null,i.onerror=null,this.sizing=!0;var a=e.navigator&&/(Macintosh|iPhone|iPod|iPad).*AppleWebKit/i.test(e.navigator.userAgent),n=function(e,i){C(t.imageData,{naturalWidth:e,naturalHeight:i,aspectRatio:e/i}),t.sizing=!1,t.sized=!0,t.build()};if(!i.naturalWidth||a){var o=document.createElement("img"),r=document.body||document.documentElement;this.sizingImage=o,o.onload=function(){n(o.width,o.height),a||r.removeChild(o)},o.src=i.src,a||(o.style.cssText="left:0;max-height:none!important;max-width:none!important;min-height:0!important;min-width:0!important;opacity:0;position:absolute;top:0;z-index:-1;",r.appendChild(o))}else n(i.naturalWidth,i.naturalHeight)}},{key:"stop",value:function(){var t=this.image;t.onload=null,t.onerror=null,t.parentNode.removeChild(t),this.image=null}},{key:"build",value:function(){if(this.sized&&!this.ready){var t=this.element,e=this.options,a=this.image,n=t.parentNode,o=document.createElement("div");o.innerHTML='<div class="cropper-container" touch-action="none"><div class="cropper-wrap-box"><div class="cropper-canvas"></div></div><div class="cropper-drag-box"></div><div class="cropper-crop-box"><span class="cropper-view-box"></span><span class="cropper-dashed dashed-h"></span><span class="cropper-dashed dashed-v"></span><span class="cropper-center"></span><span class="cropper-face"></span><span class="cropper-line line-e" data-cropper-action="e"></span><span class="cropper-line line-n" data-cropper-action="n"></span><span class="cropper-line line-w" data-cropper-action="w"></span><span class="cropper-line line-s" data-cropper-action="s"></span><span class="cropper-point point-e" data-cropper-action="e"></span><span class="cropper-point point-n" data-cropper-action="n"></span><span class="cropper-point point-w" data-cropper-action="w"></span><span class="cropper-point point-s" data-cropper-action="s"></span><span class="cropper-point point-ne" data-cropper-action="ne"></span><span class="cropper-point point-nw" data-cropper-action="nw"></span><span class="cropper-point point-sw" data-cropper-action="sw"></span><span class="cropper-point point-se" data-cropper-action="se"></span></div></div>';var r=o.querySelector(".cropper-container"),h=r.querySelector(".cropper-canvas"),s=r.querySelector(".cropper-drag-box"),c=r.querySelector(".cropper-crop-box"),d=c.querySelector(".cropper-face");this.container=n,this.cropper=r,this.canvas=h,this.dragBox=s,this.cropBox=c,this.viewBox=r.querySelector(".cropper-view-box"),this.face=d,h.appendChild(a),W(t,i),n.insertBefore(r,t.nextSibling),this.isImg||N(a,"cropper-hide"),this.initPreview(),this.bind(),e.initialAspectRatio=Math.max(0,e.initialAspectRatio)||NaN,e.aspectRatio=Math.max(0,e.aspectRatio)||NaN,e.viewMode=Math.max(0,Math.min(3,Math.round(e.viewMode)))||0,W(c,i),e.guides||W(c.getElementsByClassName("cropper-dashed"),i),e.center||W(c.getElementsByClassName("cropper-center"),i),e.background&&W(r,"cropper-bg"),e.highlight||W(d,"cropper-invisible"),e.cropBoxMovable&&(W(d,"cropper-move"),O(d,"cropperAction","all")),e.cropBoxResizable||(W(c.getElementsByClassName("cropper-line"),i),W(c.getElementsByClassName("cropper-point"),i)),this.render(),this.ready=!0,this.setDragMode(e.dragMode),e.autoCrop&&this.crop(),this.setData(e.data),y(e.ready)&&S(t,"ready",e.ready,{once:!0}),I(t,"ready")}}},{key:"unbuild",value:function(){this.ready&&(this.ready=!1,this.unbind(),this.resetPreview(),this.cropper.parentNode.removeChild(this.cropper),N(this.element,i))}},{key:"uncreate",value:function(){this.ready?(this.unbuild(),this.ready=!1,this.cropped=!1):this.sizing?(this.sizingImage.onload=null,this.sizing=!1,this.sized=!1):this.reloading?this.xhr.abort():this.image&&this.stop()}}],[{key:"noConflict",value:function(){return window.Cropper=ot,t}},{key:"setDefaults",value:function(t){C(d,x(t)&&t)}}]),t}();return C(rt.prototype,_,tt,et,it,at,nt),rt}()}}]);
//# sourceMappingURL=vendors~avatarcropper.chunk.js.map