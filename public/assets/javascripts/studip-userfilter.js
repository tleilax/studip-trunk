!function(n){var o={};function e(i){if(o[i])return o[i].exports;var t=o[i]={i:i,l:!1,exports:{}};return n[i].call(t.exports,t,t.exports,e),t.l=!0,t.exports}e.m=n,e.c=o,e.d=function(n,o,i){e.o(n,o)||Object.defineProperty(n,o,{enumerable:!0,get:i})},e.r=function(n){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(n,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(n,"__esModule",{value:!0})},e.t=function(n,o){if(1&o&&(n=e(n)),8&o)return n;if(4&o&&"object"==typeof n&&n&&n.__esModule)return n;var i=Object.create(null);if(e.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:n}),2&o&&"string"!=typeof n)for(var t in n)e.d(i,t,function(o){return n[o]}.bind(null,t));return i},e.n=function(n){var o=n&&n.__esModule?function(){return n.default}:function(){return n};return e.d(o,"a",o),o},e.o=function(n,o){return Object.prototype.hasOwnProperty.call(n,o)},e.p="",e(e.s=54)}({20:function(n,o){STUDIP.UserFilter={new_group_nr:1,configureCondition:function(n,o){return STUDIP.Dialog.fromURL(o,{title:"Bedingung konfigurieren".toLocaleString(),size:Math.min(Math.round(.9*$(window).width()),850)+"x400",method:"post",id:"configurecondition"}),!1},addCondition:function(n,o){$(".conditionfield");var e="";$(".conditionfield").each(function(){e+="&field[]="+encodeURIComponent($(this).children(".conditionfield_class:first").val())+"&compare_operator[]="+encodeURIComponent($(this).children(".conditionfield_compare_op:first").val())+"&value[]="+encodeURIComponent($(this).children(".conditionfield_value:first").val())}),$.ajax({type:"post",url:o,data:e,dataType:"html",success:function(o,e,i){var t="";$("#"+n).children(".nofilter:visible").length>0?($("#"+n).children(".nofilter").hide(),$("#"+n).children(".userfilter").show()):$("#"+n).children(".ungrouped_conditions .condition_list").length>0&&(t+="<b>"+"oder".toLocaleString()+"</b>"),t+=o,$("#"+n).find(".userfilter .ungrouped_conditions .condition_list").append(t),$("#no_conditiongroups").length>0&&$(".userfilter .ungrouped_conditions .condition_list input[type=checkbox]").hide(),$(".userfilter .group_conditions").show()}}),STUDIP.Dialog.close({id:"configurecondition"})},groupConditions:function(){var n=$(".userfilter input:checked").parent("div"),o=$(".grouped_conditions_template").clone();return n.length>0&&($(".userfilter input[type=checkbox]:checked").prop("checked",!1).hide(),$(".userfilter .group_conditions").after(o.show()),n.find("input[name^=conditiongroup_]").prop("value",STUDIP.UserFilter.new_group_nr),$(".grouped_conditions_template:last .condition_list").append(n),$(".grouped_conditions_template:last .condition_list input[name=quota]").prop("name","quota_"+STUDIP.UserFilter.new_group_nr),$(".grouped_conditions_template:last").prop("id","new_conditiongroup_"+STUDIP.UserFilter.new_group_nr),$(".grouped_conditions_template:last").prop("class","grouped_conditions"),STUDIP.UserFilter.new_group_nr++),0==$(".userfilter .ungrouped_conditions .condition_list .condition").length&&$(".userfilter .group_conditions").hide(),!1},ungroupConditions:function(n){var o=$(n).parents(".grouped_conditions").find(".condition"),e=$(n).parents(".grouped_conditions");return o.length>0&&(o.find("input[name^=conditiongroup_]").prop("value",""),$(".ungrouped_conditions .condition_list").append(o),$(".ungrouped_conditions input[type=checkbox]:not(:visible)").show(),e.remove()),$(".userfilter .group_conditions").show(),!1},getConditionFieldConfiguration:function(n,o){var e=$(n).parent();return $.ajax(o,{url:o,data:{fieldtype:$(n).val()},success:function(n,o,i){e.children(".conditionfield_compare_op").remove(),e.children(".conditionfield_value").remove(),e.children(".conditionfield_delete").first().before(n)},error:function(n,o,e){alert("Status: "+o+"\nError: "+e)}}),!1},addConditionField:function(n,o){return $.ajax({url:o,success:function(o,e,i){$("#"+n).append(o)},error:function(n,o,e){alert("Status: "+o+"\nError: "+e)}}),!1},removeConditionField:function(n){return n.remove(),STUDIP.Dialogs.closeConfirmDialog(),!1},closeDialog:function(n){return $(n).parents("div[role=dialog]").first().remove(),!1}}},21:function(n,o){STUDIP.Dialogs={showConfirmDialog:function(n,o){var e=_.memoize(function(n){return _.template(jQuery("#"+n).html())})("confirm_dialog");return $("body").append(e({question:n,confirm:o})),!1},closeConfirmDialog:function(){$("div.modaloverlay").remove()}}},54:function(n,o,e){"use strict";e.r(o);e(21),e(20)}});
//# sourceMappingURL=studip-userfilter.js.map