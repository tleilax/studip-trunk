!function(e){var t={};function n(i){if(t[i])return t[i].exports;var c=t[i]={i:i,l:!1,exports:{}};return e[i].call(c.exports,c,c.exports,n),c.l=!0,c.exports}n.m=e,n.c=t,n.d=function(e,t,i){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:i})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var i=Object.create(null);if(n.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var c in e)n.d(i,c,function(t){return e[t]}.bind(null,c));return i},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=62)}({62:function(e,t){var n;(n=jQuery)(document).on("change","[data-target]",function(){var e=n(this).data().target;n(e).val(this.value)}),n(document).ready(function(){n("#edit_userdata").on("change","input[name^=email]",function(){var e=!1;n("#edit_userdata input[name^=email]").each(function(){e=e||this.value!==this.defaultValue}),n("#edit_userdata .email-change-confirm").toggle(e)}),n("#edit_userdata .email-change-confirm").hide()}),n(document).on("change","#settings-notifications :checkbox",function(){var e=n(this).attr("name");if("all[all]"!==e){if(/all\[columns\]/.test(e)){var t=n(this).closest("td").index()+2;n(this).closest("table").find("tbody td:nth-child("+t+") :checkbox").prop("checked",this.checked)}else/all\[rows\]/.test(e)&&n(this).closest("td").siblings().find(":checkbox").prop("checked",this.checked);n(".notification.settings tbody :checkbox[name^=all]").each(function(){var e=n(this).closest("td").siblings().find(":checkbox");this.checked=0===e.filter(":not(:checked)").length}),n(".notification.settings thead :checkbox").each(function(){var e=n(this).closest("td").index()+2,t=n(this).closest("table").find("tbody td:nth-child("+e+") :checkbox");this.checked=0===t.filter(":not(:checked)").length})}else n(this).closest("table").find(":checkbox").prop("checked",this.checked)})}});
//# sourceMappingURL=studip-settings.js.map