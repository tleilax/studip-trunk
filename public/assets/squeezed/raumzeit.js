(function(a){a(document).on("ready dialog-open dialog-update",function(){a("#block_appointments_days input").click(function(){var b=parseInt(this.id.split("_").pop(),10);if(b===0||b===1){a("#block_appointments_days input:checkbox").prop("checked",function(c){return c===b})}else{a("#block_appointments_days_0").prop("checked",false);a("#block_appointments_days_1").prop("checked",false)}});a(".single_room").change(function(){window.alert("Handler for .change() called.")})});a(document).on("change","select[name=room_sd]",function(){a("input[type=radio][name=room][value=room]").prop("checked",true)});a(document).on("focus","input[name=freeRoomText_sd]",function(){a("input[type=radio][name=room][value=freetext]").prop("checked",true)});a(document).on("click","a.bookable_rooms_action",function(d){var b=a(this).prev("select")[0];var c=a(this);if(b!==null&&b!==undefined){if(c.data("state")==="enabled"){STUDIP.Raumzeit.disableBookableRooms(c)}else{if(c.data("options")===undefined){c.data("options",a(b).children("option").clone(true))}else{a(b).empty().append(c.data("options").clone(true))}a.ajax({type:"POST",url:STUDIP.ABSOLUTE_URI_STUDIP+"dispatch.php/resources/helpers/bookable_rooms",data:{rooms:_.pluck(b.options,"value"),selected_dates:_.pluck(a('input[name="singledate[]"]:checked'),"value"),singleDateID:a("input[name=singleDateID]").attr("value"),new_date:_.map(a("#startDate,#start_stunde,#start_minute,#end_stunde,#end_minute"),function(e){return{name:e.id,value:e.value}})},success:function(e){if(a.isArray(e)){if(e.length){var f=_.map(e,function(g){return a(b).children("option[value="+g+"]").text().trim()});b.title="Nicht buchbare Räume:".toLocaleString()+" "+f.join(", ")}else{b.title=""}_.each(e,function(g){a(b).children("option[value="+g+"]").prop("disabled",true)})}else{b.title=""}c.attr("title","Alle Räume anzeigen".toLocaleString());c.data("state","enabled")}})}}d.preventDefault()});a(document).on("change",'input[name="singledate[]"]',function(){STUDIP.Raumzeit.disableBookableRooms(a("a.bookable_rooms_action"))});a(document).on("ready",function(){a("a.bookable_rooms_action").show()});STUDIP.Dialog.handlers.header["X-Raumzeit-Update-Times"]=function(b){var c=a.parseJSON(b);a(".course-admin #course-"+c.course_id+" .raumzeit").html(c.html)}}(jQuery,STUDIP));STUDIP.Raumzeit={toggleRadio:function(a){},toggleCheckboxes:function(b){var a=false;jQuery("table[data-cycleid="+b+"] input[name^=singledate]").each(function(){if(jQuery(this).prop("checked")){a=true}});jQuery("table[data-cycleid="+b+"] input[name*=singledate]").prop("checked",!a)},addLecturer:function(){jQuery("select[name=teachers] option:selected").each(function(){var a=jQuery(this).val();if(a==="none"){return}jQuery("li[data-lecturerid="+a+"]").show();jQuery("select[name=teachers] option[value="+a+"]").hide();jQuery("select[name=teachers] option[value=none]").prop("selected",true)});STUDIP.Raumzeit.addFormLecturers()},removeLecturer:function(a){if(jQuery("ul.teachers li:visible").size()>1){jQuery("li[data-lecturerid="+a+"]").hide();jQuery("select[name=teachers] option[value="+a+"]").show()}else{if(jQuery("div.at_least_one_teacher").size()===0){jQuery("ul.teachers").before('<div class="at_least_one_teacher" style="display: none"><i>'+"Jeder Termin muss mindestens eine Person haben, die ihn durchführt!".toLocaleString()+"</i><div>");jQuery("div.at_least_one_teacher").slideDown().delay(3000).fadeOut(400,function(){jQuery(this).remove()});jQuery("li[data-lecturerid="+a+"]").effect("shake",100)}}STUDIP.Raumzeit.addFormLecturers()},addFormLecturers:function(){var a=[];jQuery("ul.teachers li:visible").each(function(){a.push(jQuery(this).data("lecturerid"))});jQuery("input[name=related_teachers]").val(a.join(","))},addFormGroups:function(){var a=[];jQuery("ul.groups li:visible").each(function(){a.push(jQuery(this).data("groupid"))});jQuery("input[name=related_statusgruppen]").val(a.join(","))},addGroup:function(){jQuery("select[name=groups] option:selected").each(function(){var a=jQuery(this).val();if(a==="none"){return}jQuery("li[data-groupid="+a+"]").show();jQuery("select[name=groups] option[value="+a+"]").hide();jQuery("select[name=groups] option[value=none]").prop("selected",true)});STUDIP.Raumzeit.addFormGroups()},removeGroup:function(a){jQuery("li[data-groupid="+a+"]").hide();jQuery("select[name=groups] option[value="+a+"]").show();STUDIP.Raumzeit.addFormGroups()},disableBookableRooms:function(b){var a=$(b).prev("select")[0];var c=$(b);a.title="";$(a).children("option").each(function(){$(this).prop("disabled",false)});c.data("state",false);c.attr("title","Nur buchbare Räume anzeigen".toLocaleString())}};