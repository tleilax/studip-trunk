(function(){var a,b;a=this.jQuery||window.jQuery;b=a(window);a.fn.stick_in_parent=function(d){var q,n,p,o,k,j,l,g,m,f,e,c,h;if(d==null){d={}}h=d.sticky_class,j=d.inner_scrolling,c=d.recalc_every,e=d.parent,m=d.offset_top,g=d.spacer,p=d.bottoming;if(m==null){m=0}if(e==null){e=void 0}if(j==null){j=true}if(h==null){h="is_stuck"}q=a(document);if(p==null){p=true}f=function(r){var t,s,i;if(window.getComputedStyle){t=r[0];s=window.getComputedStyle(r[0]);i=parseFloat(s.getPropertyValue("width"))+parseFloat(s.getPropertyValue("margin-left"))+parseFloat(s.getPropertyValue("margin-right"));if(s.getPropertyValue("box-sizing")!=="border-box"){i+=parseFloat(s.getPropertyValue("border-left-width"))+parseFloat(s.getPropertyValue("border-right-width"))+parseFloat(s.getPropertyValue("padding-left"))+parseFloat(s.getPropertyValue("padding-right"))}return i}else{return r.outerWidth(true)}};o=function(u,H,r,i,C,D,z,A){var E,I,s,G,J,t,x,v,y,B,w,F;if(u.data("sticky_kit")){return}u.data("sticky_kit",true);J=q.height();x=u.parent();if(e!=null){x=x.closest(e)}if(!x.length){throw"failed to find stick parent"}s=false;E=false;w=g!=null?g&&u.closest(g):a("<div />");if(w){w.css("position",u.css("position"))}v=function(){var K,M,L;if(A){return}J=q.height();K=parseInt(x.css("border-top-width"),10);M=parseInt(x.css("padding-top"),10);H=parseInt(x.css("padding-bottom"),10);r=x.offset().top+K+M;i=x.height();if(s){s=false;E=false;if(g==null){u.insertAfter(w);w.detach()}u.css({position:"",top:"",width:"",bottom:""}).removeClass(h);L=true}C=u.offset().top-(parseInt(u.css("margin-top"),10)||0)-m;D=u.outerHeight(true);z=u.css("float");if(w){w.css({width:f(u),height:D,display:u.css("display"),"vertical-align":u.css("vertical-align"),"float":z})}if(L){return F()}};v();if(D===i){return}G=void 0;t=m;B=c;F=function(){var M,P,N,L,K,O;if(A){return}N=false;if(B!=null){B-=1;if(B<=0){B=c;v();N=true}}if(!N&&q.height()!==J){v();N=true}L=b.scrollTop();if(G!=null){P=L-G}G=L;if(s){if(p){K=L+D+t>i+r;if(E&&!K){E=false;u.css({position:"fixed",bottom:"",top:t}).trigger("sticky_kit:unbottom")}}if(L<C){s=false;t=m;if(g==null){if(z==="left"||z==="right"){u.insertAfter(w)}w.detach()}M={position:"",width:"",top:""};u.css(M).removeClass(h).trigger("sticky_kit:unstick")}if(j){O=b.height();if(D+m>O){if(!E){t-=P;t=Math.max(O-D,t);t=Math.min(m,t);if(s){u.css({top:t+"px"})}}}}}else{if(L>C){s=true;M={position:"fixed",top:t};M.width=u.css("box-sizing")==="border-box"?u.outerWidth()+"px":u.width()+"px";u.css(M).addClass(h);if(g==null){u.after(w);if(z==="left"||z==="right"){w.append(u)}}u.trigger("sticky_kit:stick")}}if(s&&p){if(K==null){K=L+D+t>i+r}if(!E&&K){E=true;if(x.css("position")==="static"){x.css({position:"relative"})}return u.css({position:"absolute",bottom:H,top:"auto"}).trigger("sticky_kit:bottom")}}};y=function(){v();return F()};I=function(){A=true;b.off("touchmove",F);b.off("scroll",F);b.off("resize",y);a(document.body).off("sticky_kit:recalc",y);u.off("sticky_kit:detach",I);u.removeData("sticky_kit");u.css({position:"",bottom:"",top:"",width:""});x.position("position","");if(s){if(g==null){if(z==="left"||z==="right"){u.insertAfter(w)}w.remove()}return u.removeClass(h)}};b.on("touchmove",F);b.on("scroll",F);b.on("resize",y);a(document.body).on("sticky_kit:recalc",y);u.on("sticky_kit:detach",I);return setTimeout(F,0)};for(k=0,l=this.length;k<l;k++){n=this[k];o(a(n))}return this}}).call(this);(function(d,b){b.Sidebar={};b.Sidebar.setSticky=function(e){if(e===undefined||e){d("#layout-sidebar .sidebar").stick_in_parent({offset_top:d("#barBottomContainer").outerHeight(true),inner_scrolling:true}).on("sticky_kit:stick sticky_kit:unbottom",function(){var f=function(h,g){d("#layout-sidebar .sidebar").css("margin-left",-g)};b.Scroll.addHandler("sticky.horizontal",f);f(0,d(window).scrollLeft())}).on("sticky_kit:unstick sticky_kit:bottom",function(){b.Scroll.removeHandler("sticky.horizontal");d(this).css("margin-left",0)})}else{b.Scroll.removeHandler("sticky.horizontal");d("#layout-sidebar .sidebar").trigger("sticky_kit:unstick").trigger("sticky_kit:detach")}};d(document).on("tourstart.studip tourend.studip",function(e){b.Sidebar.setSticky(e.type==="tourend.studip")});if(window.MutationObserver!==undefined){d(document).ready(function(){if(d("#layout_content").length===0){return}var f=d("#layout_content").get(0),e=new window.MutationObserver(function(){window.requestAnimationFrame(function(){d(document.body).trigger("sticky_kit:recalc")})});e.observe(f,{attributes:true,attributeFilter:["style","class"],characterData:true,childList:true,subtree:true})})}else{var c,a=function(){var e=d(document).height();if(c!==e){c=e;d(document.body).trigger("sticky_kit:recalc")}};d(document).on("ready",function(){c=d(document).height()});d(document).on("ajaxComplete",a);d(document).on("load","#layout_content img",a);if(b.wysiwyg){d(document).on("load.wysiwyg","textarea",function(){d(document.body).trigger("sticky_kit:recalc")})}}d(document).on("ready",function(){b.Sidebar.setSticky()});window.stickySidebar=b.Sidebar.setSticky}(jQuery,STUDIP));