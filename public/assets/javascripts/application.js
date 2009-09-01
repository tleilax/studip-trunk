/* ------------------------------------------------------------------------
 * application.js
 * This file is part of Stud.IP - http://www.studip.de
 *
 * Stud.IP is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Stud.IP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Stud.IP; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301  USA
 */

/* ------------------------------------------------------------------------
 * prototypejs helpers
 * ------------------------------------------------------------------------ */

(function () {
  var methods = {
    defaultValueActsAsHint: function (element) {
      element = $(element);
      element._default = element.value;

      return element.observe('focus', function () {
        if (element._default != element.value) {
          return;
        }
        element.removeClassName('hint').value = '';
      }).observe('blur', function () {
        if (element.value.strip() !== '') {
          return;
        }
        element.addClassName('hint').value = element._default;
      }).addClassName('hint');
    }
  };

  $w('input textarea').each(function (tag) {
    Element.addMethods(tag, methods);
  });
})();


/* ------------------------------------------------------------------------
 * the global STUDIP namespace
 * ------------------------------------------------------------------------ */
if (typeof STUDIP == "undefined" || !STUDIP) {
    var STUDIP = {};
}


/* ------------------------------------------------------------------------
 * study area selection for courses
 * ------------------------------------------------------------------------ */

STUDIP.study_area_selection = {

  url: function(action, args) {
    return STUDIP.ABSOLUTE_URI_STUDIP + "dispatch.php/course/study_areas/" +
           $A(arguments).join("/");
  },

  swishAndFlick: function(element, target) {

    // clone element
    var clone = element.cloneNode(true);
    element.parentNode.insertBefore(clone, element);
    clone.absolutize();

    target = $(target);
    var o = target.cumulativeOffset();
    o[1] += target.getHeight();

    new Effect.Parallel(
      [
        new Effect.Move(clone, { sync: true, x: o[0], y: o[1], mode: "absolute"}),
        new Effect.Opacity(clone, { sync: true, from: 1, to: 0})
      ],
      {
        duration:    0.4,
        delay:       0,
        transition:  Effect.Transitions.sinoidal,
        afterFinish: function() { clone.remove(); }
      });
  },

  add: function(id, course_id) {

      course_id = course_id || "";

      // may not be visible at the current
      $$(".study_area_selection_add_" + id).each(function(add) {
          // prevent selecting twice
          add.disable();
          new Effect.Opacity(add, {from: 1, to: 0, duration: 0.25,
            afterFinish: function() {
              add.setStyle({visibility: "hidden"}).enable();
            }
          });
        });

      new Ajax.Request(STUDIP.study_area_selection.url("add", course_id), {
        method: "post",
        parameters: { "id": id },
        onSuccess: function(transport) {
          STUDIP.study_area_selection.swishAndFlick($$(".study_area_selection_add_" + id)[0],
                                                    "study_area_selection_selected");
          $("study_area_selection_none").fade();
          $("study_area_selection_selected").replace(transport.responseText);
          STUDIP.study_area_selection.refreshSelection();
        }});
  },

  remove: function(id, course_id) {

      course_id = course_id || "";

      var selection = $("study_area_selection_" + id);

      if (selection.siblings().size() == 0) {
        $("study_area_selection_at_least_one").appear();
        $("study_area_selection_at_least_one").fade({ delay: 5, queue: 'end' });
        selection.shake();
        return;
      }

      new Ajax.Request(STUDIP.study_area_selection.url("remove", course_id), {
        method: "post",
        parameters: { "id": id },
        onSuccess: function(transport) {
          selection.remove();
          if (!$$("#study_area_selection_selected li").length) {
            $("study_area_selection_none").appear();
          }

          $$(".study_area_selection_add_" + id).each(function(add) {
            add.setStyle({opacity: 0, visibility: "visible"});
            new Effect.Opacity(add, {from: 0, to: 1});
          });

          STUDIP.study_area_selection.refreshSelection();
        },
        onFailure: function() {
          selection.appear();
        }
      });
  },

  expandSelection: function(id, course_id) {

    course_id = course_id || "";

    new Ajax.Request(STUDIP.study_area_selection.url("expand", course_id, id), {
      method: 'post',
      onSuccess: function(transport) {
        $("study_area_selection_selectables").down("ul").replace(transport.responseText);
      }});
  },

  refreshSelection: function() {
    $$("#study_area_selection_selected li").each(function(element, index) {
      if (index % 2) {
        element.removeClassName("odd").addClassName("even");
      } else {
        element.removeClassName("even").addClassName("odd");
      }
    });
  }
};

STUDIP.OverDiv = Object.extend(Class.create(),
  {
    overdivs: {},
    BindInline: function(options, event) {
      event = Event.extend(event);
      if(!this.overdivs[options.id]){
        options.event_type = event.type;
        this.overdivs[options.id] = new STUDIP.OverDiv(options);
      }
      this.overdivs[options.id].show(event);
      return false;
    },

    BindToEvent: function(options, event_type) {
      event_type = event_type || 'mouseover';
      if(!this.overdivs[options.id]){
        options.event_type = event.type;
        this.overdivs[options.id] = new STUDIP.OverDiv(options);
        Event.observe($(options.initiator), event_type, this.overdivs[options.id].show.bindAsEventListener(this.overdivs[options.id]));
      }
      return this.overdivs[options.id];
    }
  }
);

STUDIP.OverDiv.prototype = {
  options: {
        id:'',
        title:'',
        content:'',
        content_url:'',
        content_element_type:'',
        position: 'bottom right',
        width: 0,
        is_moveable: true,
        inititator: null,
        event_type: 'mouseover'
  },
  is_drawn: false,
  is_hidden: true,
  is_scaled : false,
  id: '',
  container: null,
  title: null,
  content: null,

  initialize: function(options) {
    Object.extend(this.options, options || {});
    this.id = this.options.id;
    this.initiator = $(this.options.initiator);
    if(options.content_element_type){
      this.options.content_url = STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/content_element/get_formatted/' + options.content_element_type + '/' + this.id;
    }
  },

  draw: function() {
    if(!this.is_drawn){
      var outer = new Element('div', {className: 'overdiv', id: 'overdiv_' + this.id});
      var inner = new Element('div', {className: 'title'});
      var title = new Element('h4', {className: 'title'});
      var closer = new Element('a', {className: 'title', href:'#'});
      var content = new Element('div', {className: 'content'});
      if(this.options.is_moveable){
        closer.appendChild(new Element('img', {src: STUDIP.ASSETS_URL + 'images/hide.gif'}));
        Event.observe(closer, 'click', this.hide.bindAsEventListener(this));
        Event.observe(inner, 'dblclick', this.scale.bindAsEventListener(this));
        new Draggable(outer, {scroll:window, handle:inner});
      }
      title.update(this.options.title);
      content.update(this.options.content);
      this.title = title;
      this.content = content;
      inner.appendChild(title);
      inner.appendChild(closer);
      outer.appendChild(inner);
      outer.appendChild(content);
      this.container = outer;
      this.container.absolutize();
      this.container.hide();
      $('overdiv_container').appendChild(this.container);
      this.is_drawn = true;
      if(this.options.content_url){
        var self = this;
        new Ajax.Request(this.options.content_url, {
            method: 'get',
            onSuccess: function(transport){self.update(transport)}
        });
      }
    }
  },

  update: function(transport){
    this.title.update(transport.responseJSON.title);
    this.content.update(transport.responseJSON.content);
  },

  getOffset: function(){
    var ho = this.initiator.getWidth() / 2;
    var vo = this.initiator.getHeight() / 2;
    var positions = $w(this.options.position);
    for(i = 0; i < positions.length; ++i) {
      switch(positions[i].toLowerCase()) {
      case 'left':
        ho = this.container.getWidth() * -1;
        break;
      case 'right':
        ho = this.initiator.getWidth();
        break;
      case 'center':
        ho = this.initiator.getWidth() / 2;
        break;
      case 'top':
        vo = this.container.getHeight() * -1;
        break;
      case 'middle':
        vo = this.initiator.getHeight() / 2;
        break;
      case 'bottom':
        vo = this.initiator.getHeight();
        break;
      default:
      }
    }
   return {left: Math.floor(ho), top: Math.floor(vo) };
  },

  getWidth: function() {
    return this.options.width > 0 ? this.options.width : Math.floor(document.viewport.getWidth()/3);
  },

  show: function(event){
    this.draw();
    var offset = this.getOffset();
    this.container.clonePosition(this.initiator, {setWidth: false, setHeight: false, offsetLeft: offset.left, offsetTop: offset.top});
    this.container.setStyle({width: this.getWidth() + 'px'});
    this.container.show();
    this.is_hidden = false;
    if(this.options.event_type == 'mouseover'){
      Event.observe(this.initiator, 'mouseout', this.hide.bindAsEventListener(this));
    }
    event.stop();
  },

  hide: function(event){
    if(!(this.options.is_moveable && event.relatedTarget && $(event.relatedTarget).descendantOf(this.container))){
      this.container.hide();
      this.is_hidden = true;
    }
    if(this.options.event_type == 'mouseover'){
      Event.stopObserving(this.initiator, 'mouseout', this.hide.bindAsEventListener(this));
    }
    event.stop();
  },

  scale: function(event){
    new Effect.Scale(this.container, this.is_scaled ? 50 : 200, {scaleContent:false,scaleY:false});
    this.is_scaled = !this.is_scaled;
  }

};

/* ------------------------------------------------------------------------
 * forum toolbar
 * ------------------------------------------------------------------------ */
STUDIP.Forum = {};
STUDIP.Forum.Toolbar = Class.create(function() {

  var markup = [
    { "name": "bold",          "label": "<strong>B</strong>", open: "**",     close: "**"},
    { "name": "italic",        "label": "<em>i</em>",         open: "%%",     close: "%%"},
    { "name": "underline",     "label": "<u>u</u>",           open: "__",     close: "__"},
    { "name": "strikethrough", "label": "<del>u</del>",       open: "{-",     close: "-}"},
    { "name": "code",          "label": "code",               open: "[code]", close: "[/code]"},
    { "name": "larger",        "label": "A+",                 open: "++",     close: "++"},
    { "name": "smaller",       "label": "A-",                 open: "--",     close: "--"}
  ];

  var initialize = function (editor) {
    this.editor = $(editor);
    this.element = this.createToolbarElement();
    this.addButtonSet(markup);
  };

  var createToolbarElement = function () {
    var toolbar = new Element('div', { 'class': 'editor_toolbar' });
    this.editor.insert({before: toolbar});
    return toolbar;
  };

  var addButtonSet = function (set) {
    var toolbar = this;
    $A(set).each(function (button) {
      toolbar.addButton(button);
    });
  };

  var addButton = function (options) {
    options = $H(options);

    var button = this.createButtonElement(this.element, options);
    button.observe('click', this.buttonHandler(options).bind(this));
  };

  var createButtonElement= function (toolbar, options) {
    var button = Element('button');
    button.update(options.get('label'));
    button.addClassName(options.get('name'));

    toolbar.appendChild(button);

    return button;
  };

  var buttonHandler = function (options) {
    return function (event) {
      event.stop();
      replaceSelection(this.editor, options.get("open") +
                                    getSelection(this.editor) +
                                    options.get("close"));
    };
  };

  var getSelection = function (element)  {
    if (!!document.selection) {
      return document.selection.createRange().text;
    } else if (!!element.setSelectionRange) {
      return element.value.substring(element.selectionStart, element.selectionEnd);
    } else {
      return false;
    }
  };

  var replaceSelection = function (element, text) {
    var scroll_top = element.scrollTop;
    if (!!document.selection) {
      element.focus();
      var range = document.selection.createRange();
      range.text = text;
      range.select();
    } else if (!!element.setSelectionRange) {
      var selection_start = element.selectionStart;
      element.value = element.value.substring(0, selection_start) +
                      text +
                      element.value.substring(element.selectionEnd);
      element.setSelectionRange(selection_start + text.length,
                                selection_start + text.length);
    }
    element.focus();
    element.scrollTop = scroll_top;
  };

  return {
    initialize: initialize,
    createToolbarElement: createToolbarElement,
    addButtonSet: addButtonSet,
    addButton: addButton,
    createButtonElement: createButtonElement,
    buttonHandler: buttonHandler
  };
}());

/* ------------------------------------------------------------------------
 * automatic compression of tabs
 * ------------------------------------------------------------------------ */

STUDIP.Tabs = function () {

  var list, items, list_item_height, viewport_width;

  // check heights of list and items to check for wrapping
  var needs_compression = function () {
    if (!list_item_height) {
      list_item_height = list.down('li').getHeight();
    }
    return list.clientHeight > list_item_height;
  };

  // returns the largest feasible item
  var getLargest = function() {

    var i = items.length,
        largest = 5, item, letters;

    while (i--) {
      letters = items[i].innerHTML.length;
      if (letters > largest) {
        item = items[i];
        largest = letters;
      }
    }
    return item;
  };

  // truncates an item
  var truncate = function (item) {
    var text = item.innerHTML;
    var len = text.length - 4 > 4 ? text.length - 4 : 4;
    if (len < text.length) {
      item.innerHTML = text.substr(0, len) + "\u2026";
    }
  };

  return {

    // initializes, observes resize events and compresses the tabs
    initialize: function () {
      list = $("tabs");
      if (list !== null) {
        items = list.select("li a");
        viewport_width = document.viewport.getWidth();

        // strip contents and set titles
        items.each(function (item) {
          item.title = item.innerHTML = item.innerHTML.strip();
        });

        Event.observe(window, "resize", this.resize.bind(this));
        this.compress();
      }
    },


    // try to fit all the tabs into a single line
    compress: function () {
      var item;
      if (!needs_compression()) {
        return;
      }
      do {
        item = getLargest();
        if (!item) {
          break;
        }
        truncate(item);
      } while (needs_compression());
    },

    // event handler called when resizing the browser
    resize: function () {
      var new_width = document.viewport.getWidth();
      if (new_width > viewport_width) {
        items.each(function (item) {
          item.innerHTML = item.title;
        });
      }
      viewport_width = new_width;
      this.compress();
    }
  };
}();


/* ------------------------------------------------------------------------
 * Studentische Arbeitsgruppen
 * ------------------------------------------------------------------------ */

STUDIP.Arbeitsgruppen = {

    toggleOption: function(user_id) {
        if ($('user_opt_' + user_id).visible()) {
            $('user_opt_' + user_id).fade({ duration: 0.2 });
            $('user_' + user_id).morph('width:0px;', { queue: 'end', duration: 0.2 });

        } else {
            $('user_' + user_id).morph('width:110px;', { duration: 0.2 });
            $('user_opt_' + user_id).appear({ queue: 'end', duration: 0.2 });
        }
    }
};

/* ------------------------------------------------------------------------
 * application wide setup
 * ------------------------------------------------------------------------ */
document.observe('dom:loaded', function() {

  // message highlighting
  $$(".effect_highlight").invoke('highlight');

  // ajax responder
  var indicator = $('ajax_notification');
  if (indicator) {
    Ajax.Responders.register({
      onCreate:   function(request) {
        if (Ajax.activeRequestCount) {
          request.usability_timer = setTimeout(function() {
            indicator.show();
          }, 100);
        }
      },
      onComplete: function(request) {
        clearTimeout(request.usability_timer);
        if (!Ajax.activeRequestCount) {
          indicator.hide();
        }
      }
    });
  }

  // compress tabs
  STUDIP.Tabs.initialize();
});
