/*global window, $, jQuery */
/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 2, onevar: false */
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
 * jQuery plugin "metadata" configuration
 * ------------------------------------------------------------------------ */
if ("metadata" in jQuery) {
  jQuery.metadata.setType("html5");
}

/* ------------------------------------------------------------------------
 * jQuery plugin "elementAjaxNotifications"
 * ------------------------------------------------------------------------ */

(function ($) {

  $.fn.extend({
    showAjaxNotification: function (position) {
      position = position || 'left';
      return this.each(function () {
        if ($(this).data('ajax_notification')) {
          return;
        }

        $(this).wrap('<span class="ajax_notification" />');
        var notification = $('<span class="notification" />').hide().insertBefore(this),
          changes = {marginLeft: 0, marginRight: 0};

        if (position === 'right') {
          changes.marginRight = notification.outerWidth(true) + 'px';
        } else {
          changes.marginLeft = notification.outerWidth(true) + 'px';
        }

        $(this).data({
          ajax_notification: notification
        }).parent().animate(changes, 'fast', function () {
          var offset = $(this).children(':not(.notification)').position(),
            styles = {
              left: offset.left - notification.outerWidth(true),
              top: offset.top + Math.floor(($(this).height() - notification.outerHeight(true)) / 2)
            };
          if (position === 'right') {
            styles.left += $(this).outerWidth(true);
          }
          notification.css(styles).fadeIn('fast');
        });
      });
    },
    hideAjaxNotification: function () {
      return this.each(function () {
        var $this = $(this).stop(),
          notification = $this.data('ajax_notification');
        if (!notification) {
          return;
        }

        notification.stop().fadeOut('fast', function () {
          $this.animate({marginLeft: 0, marginRight: 0}, 'fast', function () {
            $this.unwrap();
          });
          $(this).remove();
        });
        $(this).removeData('ajax_notification');
      });
    }
  });

}(jQuery));

/* ------------------------------------------------------------------------
 * jQuery plugin "addToolbar"
 * ------------------------------------------------------------------------ */
(function ($) {

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

  $.fn.extend({
    addToolbar: function (button_set) {
      // Bail out if no button set is defined
      if (!button_set) {
        return this;
      }

      return this.each(function () {
        if (!$(this).is('textarea') || $(this).data('toolbar_added')) {
          return;
        }

        var $this = $(this),
          toolbar = $('<div class="editor_toolbar" />');

        jQuery.each(button_set, function (index, value) {
          $('<button />')
            .html(value.label)
            .addClass(value.name)
            .appendTo(toolbar)
            .click(function () {
              var replacement = value.open + getSelection($this[0]) + value.close;
              replaceSelection($this[0], replacement);
              return false;
            });
        });

        $this.before(toolbar).data('toolbar_added', true);
      });
    }
  });
}(jQuery));

/* ------------------------------------------------------------------------
 * the global STUDIP namespace
 * ------------------------------------------------------------------------ */
var STUDIP = STUDIP || {};


/* ------------------------------------------------------------------------
 * URLHelper
 * ------------------------------------------------------------------------ */

/**
 * This class helps to handle URLs of hyperlinks and change their parameters.
 * For example a javascript-page may open an item and the user expects other links
 * on the same page to "know" that this item is now open. But because we don't use
 * PHP session-variables here, this is difficult to use. This class can help. You
 * can overwrite the href-attribute of the link by:
 *
 *  [code]
 *  link.href = STUDIP.URLHelper.getURL("adresse.php?hello=world#anchor");
 *  [/code]
 * Returns something like:
 * "http://uni-adresse.de/studip/adresse.php?hello=world&mandatory=parameter#anchor"
 */
STUDIP.URLHelper = {
  base_url: null, //the base url for all links
  /**
   * base URL for all links generated from relative URLs
   */
  setBaseURL: function (url) {
    this.base_url = url;
  },
  /**
   * method to extend short URLs like "about.php" to "http://.../about.php"
   */
  resolveURL: function (url) {
    if (this.base_url === null) {
      this.base_url = STUDIP.ABSOLUTE_URI_STUDIP;
    }
    if (this.base_url === "" ||
        url.match(/^[a-z]+:/) !== null ||
        url.charAt(0) === "?") {
      //this method cannot do any more:
      return url;
    }
    var base_url = this.base_url;
    if (url.charAt(0) === "/") {
      var host = this.base_url.match(/^[a-z]+:\/\/[\w:.\-]+/);
      base_url = host ? host : '';
    }
    return base_url + url;
  },
  /**
   * Creates an URL with the mandatory parameters
   * @param url string: any url-string
   * @param param_object map: associative object for extra values
   * @return: url with all necessary parameters - non URI-encoded!
   */
  getURL: function (url, param_object) {
    if (param_object === undefined) {
      param_object = {};
    }
    url = STUDIP.URLHelper.resolveURL(url);
    // splitting the url:
    url = url.split("#");
    var anchor = (url.length > 1) ? url[url.length - 1] : "";
    url = url[0].split("?");
    var url_parameters = (url.length > 1) ? url[url.length - 1].split("&") : [];
    var parameters = {};
    jQuery.each(url_parameters, function (index, value) {
      var assoc = value.split("=");
      parameters[assoc[0]] = assoc[1];
    });
    url = url[0];
    //merging in the param_object - as you see this has got priority:
    parameters = jQuery.extend(parameters, param_object);
    // glueing together:
    var param_strings = [];
    jQuery.each(parameters, function (param, value) {
      param_strings.push(param + "=" + value);
    });
    if (param_strings.length > 0) {
      url += "?" + param_strings.join("&");
    }
    if (anchor !== "") {
      url += "#" + anchor;
    }
    return url;
  }
};

/* ------------------------------------------------------------------------
 * study area selection for courses
 * ------------------------------------------------------------------------ */
STUDIP.study_area_selection = {

  initialize: function () {
    // Ein bisschen h�sslich im Sinne von "DRY", aber wie sonst?
    jQuery('input[name^="study_area_selection[add]"]').live('click', function () {
      var parameters = jQuery(this).metadata();
      if (!(parameters && parameters.id)) {
        return;
      }
      STUDIP.study_area_selection.add(parameters.id, parameters.course_id || '-');
      return false;
    });
    jQuery('input[name^="study_area_selection[remove]"]').live('click', function () {
      var parameters = jQuery(this).metadata();
      if (!(parameters && parameters.id)) {
        return;
      }
      STUDIP.study_area_selection.remove(parameters.id, parameters.course_id || '-');
      return false;
    });
    jQuery('a.study_area_selection_expand').live('click', function () {
      var parameters = jQuery(this).metadata();
      if (!(parameters && parameters.id)) {
        return;
      }
      STUDIP.study_area_selection.expandSelection(parameters.id, parameters.course_id || '-');
      return false;
    });
  },

  url: function (action, args) {
    return STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/course/study_areas/' +
           jQuery.makeArray(arguments).join('/');
  },

  add: function (id, course_id) {
    // may not be visible at the current
    jQuery('.study_area_selection_add_' + id).attr('disabled', true).fadeTo('slow', 0);

    jQuery.ajax({
      type: 'POST',
      url: STUDIP.study_area_selection.url('add', course_id || '-'),
      data: ({id: id}),
      dataType: 'html',
      async: false, // Critical request thus synchronous
      success: function (data) {
//      STUDIP.study_area_selection.swishAndFlick(id, 'study_area_selection_selected');
        jQuery('#study_area_selection_none').fadeOut();
        jQuery('#study_area_selection_selected').replaceWith(data);
        STUDIP.study_area_selection.refreshSelection();
      }
    });
  },

  remove: function (id, course_id) {
    var jQueryselection = jQuery('#study_area_selection_' + id);

    if (jQueryselection.siblings().length === 0) {
      jQuery('#study_area_selection_at_least_one').fadeIn().delay(5000).fadeOut();
      jQueryselection.effect('bounce', 'fast');
      return;
    }

    jQuery.ajax({
      type: 'POST',
      url: STUDIP.study_area_selection.url('remove', course_id || '-'),
      data: ({id: id}),
      dataType: 'html',
      async: false, // Critical request thus synchronous
      success: function (data) {
        jQueryselection.fadeOut(function () {
          jQuery(this).remove();
        });
        if (jQuery('#study_area_selection_selected li').length === 0) {
          jQuery('#study_area_selection_none').fadeIn();
        }
        jQuery('.study_area_selection_add_' + id).css({
          visibility: 'visible',
          opacity: 0
        }).fadeTo('slow', 1, function () {
          jQuery(this).attr('disabled', false);
        });

        STUDIP.study_area_selection.refreshSelection();
      },
      error: function () {
        jQueryselection.fadeIn();
      }
    });
  },

  expandSelection: function (id, course_id) {
    jQuery.post(STUDIP.study_area_selection.url('expand', course_id || '-', id), function (data) {
        jQuery('#study_area_selection_selectables ul').replaceWith(data);
      }, 'html');
  },

  refreshSelection: function () {
    // "even=odd && odd=even ??" - this may seem strange but jQuery and Stud.IP differ in odd/even
    jQuery('#study_area_selection_selected li:odd').removeClass('odd').addClass('even');
    jQuery('#study_area_selection_selected li:even').removeClass('even').addClass('odd');
  }
};

/* ------------------------------------------------------------------------
 * Markup toolbar
 * ------------------------------------------------------------------------ */
STUDIP.Markup = {
  buttonSet: [
    {"name": "bold",          "label": "<strong>B</strong>", open: "**",     close: "**"},
    {"name": "italic",        "label": "<em>i</em>",         open: "%%",     close: "%%"},
    {"name": "underline",     "label": "<u>u</u>",           open: "__",     close: "__"},
    {"name": "strikethrough", "label": "<del>u</del>",       open: "{-",     close: "-}"},
    {"name": "code",          "label": "code",               open: "[code]", close: "[/code]"},
    {"name": "larger",        "label": "A+",                 open: "++",     close: "++"},
    {"name": "smaller",       "label": "A-",                 open: "--",     close: "--"}
  ]
};

/* ------------------------------------------------------------------------
 * automatic compression of tabs
 * ------------------------------------------------------------------------ */
STUDIP.Tabs = (function () {

  var list, items;

  // check heights of list and items to check for wrapping
  function needs_compression() {
    return jQuery(list).height() > jQuery('li:first', list).height();
  }

  // returns the largest feasible item
  function getLargest() {
    var largest = 5, item, letters;

    items.each(function () {
      letters = jQuery(this).html().length;
      if (letters > largest) {
        item = this;
        largest = letters;
      }
    });
    return item;
  }

  // truncates an item
  function truncate(item) {
    var text = jQuery(item).html(),
      length = Math.max(text.length - 4, 4);
    if (length < text.length) {
      jQuery(item).html(text.substr(0, length) + "\u2026");
    }
  }

  return {
    // initializes, observes resize events and compresses the tabs
    initialize: function () {
      list = jQuery('#tabs');
      if (list.length === 0) {
        return;
      }
      items = jQuery('li a span', list);
      jQuery(list).data('old_width', jQuery(window).width());

      // strip contents and set titles
      items.each(function (index, element) {
        jQuery(element).html(jQuery.trim(jQuery(element).html()));
        jQuery(element).attr('title', jQuery(element).html());
      });

      jQuery(window).bind('resize', this.resize);
      this.compress();
    },


    // try to fit all the tabs into a single line
    compress: function () {
      var item;
      while (needs_compression() && (item = getLargest())) {
        truncate(item);
      }
    },

    // event handler called when resizing the browser
    resize: function () {
      var new_width = jQuery(window).width();
      if (new_width > jQuery(list).data('old_width')) {
        items.each(function () {
          jQuery(this).html(jQuery(this).attr('title'));
        });
      }
      jQuery(list).data('old_width', new_width);
      STUDIP.Tabs.compress();
    }
  };
}());

/* ------------------------------------------------------------------------
 * automatic compression of page title
 * ------------------------------------------------------------------------ */
STUDIP.Title = (function () {

  var title, reference;

  // truncates an item
  function truncate(item) {
    var text = jQuery(item).text(),
      length = Math.max(text.length - 4, 4);
    if (length < text.length) {
      jQuery(item).text(text.substr(0, length) + "\u2026");
    }
  }

  return {
    // initializes, observes resize events and compresses the title
    initialize: function () {
      title = jQuery('#barBottommiddle');
      reference = jQuery('#barBottomright');
      title.data('old_width', jQuery(window).width());

      // strip contents and set titles
      title.text(jQuery.trim(title.text()));
      title.attr('title', title.text());

      jQuery(window).bind('resize', this.resize);
      this.compress();
    },


    // try to fit the title into a single line
    compress: function () {
      while (title.height() > reference.height()) {
        truncate(title);
      }
    },

    // event handler called when resizing the browser
    resize: function () {
      var new_width = jQuery(window).width();
      if (new_width > jQuery(title).data('old_width')) {
        title.text(title.attr('title'));
      }
      jQuery(title).data('old_width', new_width);
      STUDIP.Title.compress();
    }
  };
}());

/* ------------------------------------------------------------------------
 * Dialogbox
 * ------------------------------------------------------------------------ */

/**
 * The dialogbox is an element from jQuery UI that presents content like in
 * a window that is draggable and resizable. All you need is a title and a
 * content for that window. You can also define an id to identify that window
 * later on. Only one window with the same id will be shown at a time.
 * Also you can define a scope, so only one window of one scope will be shown
 * at the same time.
 */
STUDIP.Dialogbox = {
  currentScopes: {},
  currentBoxes: {},
  forumTimeout: null,
  cache: {},

  openBox: function (id, title, content, coord, scope) {
    if (scope && this.currentBoxes[this.currentScopes[scope]] && (id !== this.currentBoxes[this.currentScopes[scope]])) {
      this.closeScope(scope);
    }
    if (!this.currentBoxes[id]) {
      jQuery('<div id="Dialogbox_' + id + '">' + content + '</div>').dialog({
        show: 'slide',
        hide: 'slide',
        title: title,
        position: coord,
        width: Math.min(600, jQuery(window).width() - 64),
        height: 'auto',
        maxHeight: jQuery(window).height(),
        close: function (event, ui) {
          STUDIP.Dialogbox.closeBox(id, true);
        },
        drag: function (event, ui) {
          STUDIP.Dialogbox.closeBox(id, false);
        }
      });

      this.currentScopes[scope] = id;
      this.currentBoxes[id] = true;
    }
  },

  closeScope: function (scope) {
    jQuery("#Dialogbox_" + this.currentScopes[scope]).dialog('close');
    delete this.currentScopes[scope];
  },

  closeBox: function (id, kill) {
    delete this.currentBoxes[id];
    if (kill) {
      jQuery("#Dialogbox_" + id).remove();
    } else {
      jQuery("#Dialogbox_" + id).attr("id", "#Dialogbox_" + id + "_dragged");
    }
  },

  openForumPosting: function (id, element) {
    var coord = "center", //coordinates to give to dialogbox - "center" means center of window
        data = STUDIP.Dialogbox.cache["forum_" + id];

    if (element) {
      coord = jQuery(element).position();
      coord = [coord.left + jQuery(element).width() + 2, coord.top - jQuery(window).scrollTop()];
    }

    STUDIP.Dialogbox.closeForumPosting(id);
    STUDIP.Dialogbox.forumTimeout = window.setTimeout(function () {
      if (!data) {
        jQuery.getJSON("dispatch.php/content_element/get_formatted/forum/" + id, function (data) {
          STUDIP.Dialogbox.cache["forum_" + id] = data;
          STUDIP.Dialogbox.openBox(id, data.title, data.content, coord, "forum");
        });
      } else {
        STUDIP.Dialogbox.openBox(id, data.title, data.content, coord, "forum");
      }
    }, 300);
  },

  closeForumPosting: function () {
    window.clearTimeout(STUDIP.Dialogbox.forumTimeout);
    STUDIP.Dialogbox.forumTimeout = null;

    STUDIP.Dialogbox.closeScope("forum");
  }
};

jQuery('.forum-icon').live('mouseenter', function () {
  STUDIP.Dialogbox.openForumPosting(jQuery(this).metadata().forumid, this);
}).live('mouseleave', function () {
  STUDIP.Dialogbox.closeForumPosting();
});

/* ------------------------------------------------------------------------
 * Dateibereich
 * ------------------------------------------------------------------------ */

// hier ein paar "globale" Variablen, die nur in Funktionen des Filesystem-Namespace verwendet werden:
STUDIP.Filesystem = {
  hover_begin    : 0,             //erste Zeit, dass eine Datei �ber den Ordner ...hovered_folder bewegt wurde.
  hovered_folder : '',            //letzter Ordner, �ber den eine gezogene Datei bewegt wurde.
  movelock       : false,         //wenn auf true gesetzt, findet gerade eine Animation statt.
  sendstop       : false,         //wenn auf true gesetzt, wurde eine Datei in einen Ordner gedropped und die Seite l�dt sich gerade neu.
  getURL         : function (url) {
    if (url) {
      return url.split("#", 1)[0];
    }
    return document.URL.split("#", 1)[0];
  },
  /**
   * L�sst die gelben Pfeile verschwinden und ersetzt sie durch Anfassersymbole.
   * Wichtig f�r Javascript-Nichtjavascript Behandlung. Nutzer ohne Javascript
   * sehen nur die gelben Pfeile zum Sortieren.
   */
  unsetarrows     : function () {
    jQuery("span.move_arrows,span.updown_marker").hide();
    jQuery(".sortable .draggable").css("cursor", "move");
    jQuery(".sortable .draggable_folder").css("cursor", "move");
  }
};


/**
 * deklariert Ordner und Dateien als ziehbare Elemente bzw. macht sie sortierbar
 */
STUDIP.Filesystem.setdraggables = function () {
  jQuery("div.folder_container.sortable").each(function () {
    var id = this.getAttribute('id');
    var md5_id = id.substr(id.lastIndexOf('_') + 1);
    //wenn es einen Anfasser gibt, also wenn Nutzer verschieben darf
    jQuery(this).sortable({
      axis: "y",
      opacity: 0.6,
      revert: 300,
      scroll: true,
      update: function () {
        var id = this.getAttribute('id');
        var sorttype = (id.lastIndexOf('subfolders') !== -1 ? "folder" : "file");
        md5_id = id.substr(id.lastIndexOf('_') + 1);
        var order = jQuery(this).sortable('serialize', {key: "order"}).split("&");
        order = jQuery.map(order, function (component) {
          return component.substr(component.lastIndexOf('=') + 1);
        });
        var order_ids = jQuery.map(order, function (order_number) {
          if (sorttype === "folder") {
            // Unterordner:
            return jQuery("#getmd5_fo" + md5_id + "_" + order_number).html();
          } else {
            // Dateien:
            return jQuery("#getmd5_fi" + md5_id + "_"  + order_number).html();
          }
        });
        jQuery.ajax({
          url: STUDIP.Filesystem.getURL(),
          data: {
            sorttype: sorttype,
            folder_sort: md5_id,
            file_order: order_ids.join(",")
          }
        });
      }
    });
  });
};

/**
 * deklariert Ordner als Objekte, in die Dateien gedropped werden k�nnen
 */
STUDIP.Filesystem.setdroppables = function () {
  jQuery("div.droppable").droppable({
    accept: '.draggable',
    hoverClass: 'hover',
    over: function () {
      var folder_md5_id = this.getAttribute('id');
      folder_md5_id = folder_md5_id.substr(folder_md5_id.lastIndexOf('_') + 1);
      STUDIP.Filesystem.openhoveredfolder(folder_md5_id);
    },
    drop: function (event, ui) {
      var id = ui.draggable.attr('id');
      var file_md5_id = id.substr(id.indexOf('_') + 1);
      file_md5_id = jQuery("#getmd5_fi" + file_md5_id).html();
      var folder_md5_id = jQuery(this).attr('id');
      folder_md5_id = folder_md5_id.substr(folder_md5_id.lastIndexOf('_') + 1);
      //alert("Drop "+file_md5_id+" on "+folder_md5_id);
      var adress = STUDIP.Filesystem.getURL();
      if ((event.keyCode === 17)  || (event.ctrlKey)) {
        jQuery.ajax({
          url: adress,
          data: {
            copyintofolder: folder_md5_id,
            copyfile: file_md5_id
          },
          success: function () {
            location.href = adress + '&cmd=tree&open=' + folder_md5_id;
          }
        });
      } else {
        jQuery.ajax({
          url: adress,
          data: {
            moveintofolder: folder_md5_id,
            movefile: file_md5_id
          },
          success: function () {
            location.href = adress + '&cmd=tree&open=' + folder_md5_id;
          }
        });
      }
      STUDIP.Filesystem.sendstop = true;
    }
  });
};

/**
 * �ffnet einen Dateiordner, wenn eine Datei lange genug dr�ber gehalten wird.
 */
STUDIP.Filesystem.openhoveredfolder = function (md5_id) {
  var zeit = new Date();
  if (md5_id === STUDIP.Filesystem.hovered_folder) {
    if (STUDIP.Filesystem.hover_begin < zeit.getTime() - 1000) {
      if (jQuery("#folder_" + md5_id + "_body").is(':hidden')) {
        STUDIP.Filesystem.changefolderbody(md5_id);
        STUDIP.Filesystem.hover_begin = zeit.getTime();
      }
    }
  } else {
    STUDIP.Filesystem.hovered_folder = md5_id;
    STUDIP.Filesystem.hover_begin = zeit.getTime();
  }
};

/**
 * �ffnet/schlie�t einen Dateiordner entweder per AJAX oder nur per Animation,
 * wenn Inhalt schon geladen wurde.
 */
STUDIP.Filesystem.changefolderbody = function (md5_id) {
  if (!STUDIP.Filesystem.movelock) {
    STUDIP.Filesystem.movelock = true;
    window.setTimeout("STUDIP.Filesystem.movelock = false;", 410);
    if (jQuery("#folder_" + md5_id + "_body").is(':visible')) {
      jQuery("#folder_" + md5_id + "_header").css('fontWeight', 'normal');
      jQuery("#folder_" + md5_id + "_arrow_img").attr('src', STUDIP.ASSETS_URL + "images/forumgrau2.png");
      jQuery("#folder_" + md5_id + "_arrow_td").addClass('printhead2')
                                               .removeClass('printhead3');
      jQuery("#folder_" + md5_id + "_body").slideUp(400);
    } else {
      if (jQuery("#folder_" + md5_id + "_body").html() === "") {
        var adress = STUDIP.Filesystem.getURL(jQuery("#folder_" + md5_id + "_arrow_img").parent()[0].href);
        jQuery("#folder_" + md5_id + "_body").load(adress, {getfolderbody: md5_id}, function () {
          jQuery("#folder_" + md5_id + "_header").css('fontWeight', 'bold');
          jQuery("#folder_" + md5_id + "_arrow_img").attr('src', STUDIP.ASSETS_URL + "images/forumgraurunt2.png");
          jQuery("#folder_" + md5_id + "_arrow_td").addClass('printhead3')
                                                   .removeClass('printhead2');
          STUDIP.Filesystem.unsetarrows();
          STUDIP.Filesystem.setdraggables();
          STUDIP.Filesystem.setdroppables();
          jQuery("#folder_" + md5_id + "_body").slideDown(400);
        });
      } else {
        jQuery("#folder_" + md5_id + "_header").css('fontWeight', 'bold');
        jQuery("#folder_" + md5_id + "_arrow_img").attr('src', STUDIP.ASSETS_URL + "images/forumgraurunt2.png");
        jQuery("#folder_" + md5_id + "_arrow_td").addClass('printhead3')
                                                 .removeClass('printhead2');
        STUDIP.Filesystem.unsetarrows();
        STUDIP.Filesystem.setdraggables();
        STUDIP.Filesystem.setdroppables();
        jQuery("#folder_" + md5_id + "_body").slideDown(400);
      }
    }
  }
  return false;
};

/**
 * �ffnet/schlie�t eine Datei entweder per AJAX oder nur per Animation,
 * wenn Inhalt schon geladen wurde.
 */

STUDIP.Filesystem.changefilebody = function (md5_id) {
  if (!STUDIP.Filesystem.movelock) {
    STUDIP.Filesystem.movelock = true;
    window.setTimeout("STUDIP.Filesystem.movelock = false;", 410);

    if (jQuery("#file_" + md5_id + "_body").is(':visible')) {
      jQuery("#file_" + md5_id + "_body").slideUp(400);
      jQuery("#file_" + md5_id + "_header").css("fontWeight", 'normal');
      jQuery("#file_" + md5_id + "_arrow_td").addClass('printhead2')
                                             .removeClass('printhead3');
      jQuery("#file_" + md5_id + "_arrow_img").attr('src', STUDIP.ASSETS_URL + "images/forumgrau2.png");
    } else {
      if (jQuery("#file_" + md5_id + "_body").html() === "") {
        var adress = STUDIP.Filesystem.getURL(jQuery("#file_" + md5_id + "_arrow_img").parent()[0].href);
        jQuery("#file_" + md5_id + "_body").load(adress, {getfilebody: md5_id}, function () {
          jQuery("#file_" + md5_id + "_header").css('fontWeight', 'bold');
          jQuery("#file_" + md5_id + "_arrow_img").attr('src', STUDIP.ASSETS_URL + "images/forumgraurunt2.png");
          jQuery("#file_" + md5_id + "_arrow_td").addClass('printhead3')
                                                 .removeClass('printhead2');
          jQuery("#file_" + md5_id + "_body").slideDown(400);
        });
      } else {
        //Falls der Dateik�rper schon geladen ist.
        jQuery("#file_" + md5_id + "_body_row").show();
        jQuery("#file_" + md5_id + "_header").css('fontWeight', 'bold');
        jQuery("#file_" + md5_id + "_arrow_td").addClass('printhead3')
                                               .removeClass('printhead2');
        jQuery("#file_" + md5_id + "_arrow_img").attr('src', STUDIP.ASSETS_URL + "images/forumgraurunt2.png");
        jQuery("#file_" + md5_id + "_body").slideDown(400);
      }
    }
  }
  return false;
};


/* ------------------------------------------------------------------------
 * Studentische Arbeitsgruppen
 * ------------------------------------------------------------------------ */

STUDIP.Arbeitsgruppen = {

  toggleOption: function (user_id) {
    if (jQuery('#user_opt_' + user_id).is(':hidden')) {
      jQuery('#user_opt_' + user_id).show('slide', {direction: 'left'}, 400, function () {
        jQuery('#user_opt_' + user_id).css("display", "inline-block");
      });
    } else {
      jQuery('#user_opt_' + user_id).hide('slide', {direction: 'left'}, 400);
    }
  }
};

/* ------------------------------------------------------------------------
 * News
 * ------------------------------------------------------------------------ */

STUDIP.News = {
  openclose: function (id, admin_link) {
    if (jQuery("#news_item_" + id + "_content").is(':visible')) {
      STUDIP.News.close(id);
    } else {
      STUDIP.News.open(id, admin_link);
    }
  },

  open: function (id, admin_link) {
    jQuery("#news_item_" + id + "_content").load(
      STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/news/get_news/' + id,
      {admin_link: admin_link},
      function () {
        jQuery("#news_item_" + id + "_content").slideDown(400);
        jQuery("#news_item_" + id + " .printhead2 img")
            .attr('src', STUDIP.ASSETS_URL + "images/forumgraurunt2.png");
        jQuery("#news_item_" + id + " .printhead2")
            .removeClass("printhead2")
            .addClass("printhead3");
        jQuery("#news_item_" + id + " .printhead b").css("font-weight", "bold");
        jQuery("#news_item_" + id + " .printhead a.tree").css("font-weight", "bold");
      });
  },

  close: function (id) {
    jQuery("#news_item_" + id + "_content").slideUp(400);
    jQuery("#news_item_" + id + " .printhead3 img")
        .attr('src', STUDIP.ASSETS_URL + "images/forumgrau2.png");
    jQuery("#news_item_" + id + " .printhead3")
        .removeClass("printhead3")
        .addClass("printhead2");
    jQuery("#news_item_" + id + " .printhead b").css("font-weight", "normal");
    jQuery("#news_item_" + id + " .printhead a.tree").css("font-weight", "normal");
  }
};

/* ------------------------------------------------------------------------
 * ajax_loader
 * ------------------------------------------------------------------------ */
jQuery('[data-behaviour="\'ajaxContent\'"]').live('click', function () {
  var parameters = jQuery(this).metadata(),
    indicator = ("indicator" in parameters) ? parameters.indicator : this,
    target    = ("target" in parameters) ? parameters.target : jQuery(this).next(),
    url       = ("url" in parameters) ? parameters.url : jQuery(this).attr('href');

  jQuery(indicator).showAjaxNotification('right');
  jQuery(target).load(url, function () {
    jQuery(indicator).hideAjaxNotification();
  });
  return false;
});

/* ------------------------------------------------------------------------
 * messages boxes
 * ------------------------------------------------------------------------ */

jQuery('.messagebox .messagebox_buttons a').live('click', function () {
  if (jQuery(this).is('.details')) {
    jQuery(this).closest('.messagebox').toggleClass('details_hidden');
  } else if (jQuery(this).is('.close')) {
    jQuery(this).closest('.messagebox').fadeOut(function () {
      jQuery(this).remove();
    });
  }
  return false;
}).live('focus', function () {
  jQuery(this).blur(); // Get rid of the ugly "clicked border" due to the text-indent
});

/* ------------------------------------------------------------------------
 * QuickSearch inputs
 * ------------------------------------------------------------------------ */

STUDIP.QuickSearch = {
  /**
   * a helper-function to generate a JS-object filled with the variables of a form
   * like "{ input1_name : input1_value, input2_name: input2_value }"
   * @param selector string: ID of an input in a form-tag
   * @return: JSON-object (not as a string)
   */
  formToJSON: function (selector) {
    selector = jQuery(selector).parents("form");
    var form = {};   //the basic JSON-object that will be returned later
    jQuery(selector).find(':input[name]').each(function () {
      var name = jQuery(this).attr('name');   //name of the input
      if (form[name]) {
        //for double-variables (not arrays):
        form[name] = form[name] + ',' + jQuery(this).val();
      } else {
        form[name] = jQuery(this).val();
      }
    });
    return form;
  },
  /**
   * the function to be called from the QuickSearch class template
   * @param name string: ID of input
   * @param url string: URL of AJAX-response
   * @param func string: name of a possible function executed
   *        when user has selected something
   * @return: void
   */
  autocomplete: function (name, url, func, title, disabled) {
    if (typeof disabled === "undefined" || disabled !== true) {
      jQuery('#' + name).autocomplete({
        minLength: 3,
        source: function (input, add) {
          //get the variables that should be sent:
          var send_vars = {
            form_data: STUDIP.QuickSearch.formToJSON('#' + name),
            request: input.term
          };
          jQuery.ajax({
            url: url,
            type: "post",
            dataType: "json",
            data: send_vars,
            success: function (data) {
              var stripTags = /<\w+(\s+("[^"]*"|'[^']*'|[^>])+)?>|<\/\w+>/gi;
              var suggestions = [];  //an array of possible selections
              jQuery.each(data, function (i, val) {
                //adding a label and a hidden item_id - don't use "value":
                suggestions.push({
                  label: val.item_name,                       //what is displayed in the drobdown-boc
                  item_id: val.item_id,                       //the hidden ID of the item
                  value: val.item_search_name !== null ? val.item_search_name : jQuery("<div/>").html(val.item_name.replace(stripTags, "")).text()  //what is inserted in the visible input-box
                });
              });
              //pass it to the function of UI-widget:
              add(suggestions);
            }
          });
        },
        select: function (event, ui) {
          //inserts the ID of the selected item in the hidden input:
          jQuery('#' + name + "_realvalue").attr("value", ui.item.item_id);
          //and execute a special function defined before by the programmer:
          if (func) {
            func(ui.item.item_id, ui.item.label);
          }
        }
      });
    }
    jQuery('#' + name).placehold();
  }
};

//must be overridden to display html in autocomplete like avatars:
(function () {
  var method_name = "_renderItem";
  jQuery.ui.autocomplete.prototype[method_name] = function (ul, item) {
    return jQuery("<li></li>")
      .data("item.autocomplete", item)
      .append(jQuery("<a></a>").html(item.label))
      .appendTo(ul);
  };
}());

/* ------------------------------------------------------------------------
 * Multiselect
 * ------------------------------------------------------------------------ */

/**
 * Turns a select-box into an easy to use multiple select-box
 */
STUDIP.MultiSelect = {
  /**
   * @param id string:
   */
  create: function (id, itemName) {
    if (!jQuery(id).attr('multiple')) {
      jQuery(id).attr('multiple', 'multiple');
      jQuery(id).css('height', '120px');
    }
    jQuery(id).multiselect({
      sortable: true,
      itemName: itemName,
      draggable: true
    });
  }
};
jQuery(function () {
  jQuery.extend(jQuery.ui.multiselect, {
    locale: {
      addAll: "Alle hinzuf�gen".toLocaleString(),
      removeAll: "Alle entfernen".toLocaleString(),
      itemsCount: "ausgew�hlt".toLocaleString()
    }
  });
});


/* ------------------------------------------------------------------------
 * browse.php
 * ------------------------------------------------------------------------ */

STUDIP.Browse = {
  selectUser: function (username, name) {
    location.href = STUDIP.URLHelper.getURL("about.php", {"username": username});
  }
};

/* ------------------------------------------------------------------------
 * application wide setup
 * ------------------------------------------------------------------------ */

jQuery(function () {
  // AJAX Indicator
  STUDIP.ajax_indicator = true;

  jQuery('.add_toolbar').addToolbar(STUDIP.Markup.buttonSet);

  // compress tabs
  STUDIP.Tabs.initialize();
  STUDIP.Title.initialize();

  STUDIP.study_area_selection.initialize();

  // validate forms
  STUDIP.Forms.initialize();

  // autofocus for all browsers
  if (!("autofocus" in document.createElement("input"))) {
    jQuery('[autofocus]').first().focus();
  }

  jQuery('textarea.resizable').resizable({
    handles: 's',
    minHeight: 50
  });


});



/* ------------------------------------------------------------------------
 * application collapsable tablerows
 * ------------------------------------------------------------------------ */
jQuery(function ($) {

  $('table.collapsable .toggler').focus(function () {
    $(this).blur();
  }).click(function () {
    $(this).closest('tbody').toggleClass('collapsed');
    return false;
  });

  $('a.load-in-new-row').live('click', function () {
    if ($(this).closest('tr').next().hasClass('loaded-details')) {
      $(this).closest('tr').next().remove();
      return false;
    }
    $(this).showAjaxNotification();

    var that = this;
    $.get($(this).attr('href'), function (response) {
      var row = $('<tr />').addClass('loaded-details'),
        cell = $('<td />')
          .attr('colspan', $(that).closest('td').siblings().length + 1)
          .html(response)
          .appendTo(row);
      $(that).hideAjaxNotification()
        .closest('tr').after(row);
    });

    return false;
  });

  $('.loaded-details a.cancel').live('click', function () {
    $(this).closest('.loaded-details').prev().find('a.load-in-new-row').click();
    return false;
  });

});



/* ------------------------------------------------------------------------
 * only numbers in the input field
 * ------------------------------------------------------------------------ */
jQuery('input.allow-only-numbers').live('keyup', function () {
  jQuery(this).val(jQuery(this).val().replace(/\D/, ''));
});

/* ------------------------------------------------------------------------
 * additional jQuery (UI) settings for Stud.IP
 * ------------------------------------------------------------------------ */

jQuery.ui.accordion.prototype.options.icons = {
  header: 'arrow_right',
  headerSelected: 'arrow_down'
};

/* ------------------------------------------------------------------------
 * calendar gui
 * ------------------------------------------------------------------------ */
STUDIP.Calendar = {
  cell_height: 20,
  the_entry_content: null,
  entry: null,

  day_names: [
    "Montag",
    "Dienstag",
    "Mittwoch",
    "Donnerstag",
    "Freitag",
    "Samstag",
    "Sonntag"
  ],

  /**
   * this function is called, whenever an existing entry in the
   * calendar is clicked. It calls the passed function with the
   * calculcate id of the clicked element
   *
   * @param  object  a function or a reference to a function
   * @param  object  the element in the dom, that has been clicked
   * @param  object  the click-event itself
   */
  clickEngine: function (func, target, event) {
    event.cancelBubble = true;
    var id = jQuery(target).parent()[0].id;
    id = id.substr(id.lastIndexOf("_") + 1);
    func(id);
  },


  /**
   * check, that the submited input-field cotains of a valid hour
   *
   * @param  object  the input-element to check
   */
  validateHour: function (element) {
    var hour = parseInt(jQuery(element).val(), 10);

    if (hour > 23) {
      hour = 23;
    }
    if (hour < 0 || isNaN(hour)) {
      hour = 0;
    }

    jQuery(element).val(hour);
  },

  /**
   * check, that the submited input-field cotains of a valid minute
   *
   * @param  object  the input-element to check
   */
  validateMinute: function (element) {
    var minute = parseInt(jQuery(element).val(), 10);

    if (minute > 59) {
      minute = 59;
    }
    if (minute < 0 || isNaN(minute)) {
      minute = 0;
    }

    jQuery(element).val(minute);
  },

  /**
   * check, that the submitted input-fields contain a valid time-range
   *
   * @param  object  the input-element to check (start-hour)
   * @param  object  the input-element to check (start-minute)
   * @param  object  the input-element to check (end-hour)
   * @param  object  the input-element to check (end-minute)
   *
   * @return: bool true if valid time-range, false otherwise
   */
  checkTimeslot: function (start_hour, start_minute, end_hour, end_minute) {
    if ((parseInt(start_hour.val(), 10) * 100) + parseInt(start_minute.val(), 10) >=
        (parseInt(end_hour.val(), 10) * 100) + parseInt(end_minute.val(), 10)) {
      return false;
    }

    return true;
  }
};

STUDIP.Schedule = {

  inst_changed : false,

  /**
   * this function is called, when an entry shall be created in the calendar
   *
   * @param  object  the empty entry in the calendar
   * @param  int     the day that has been clicked
   * @param  int     the start-hour that has been clicked
   */
  newEntry: function (entry, day, hour) {
    // do not allow creation of new entry, if one of the following popups is visible!
    if (jQuery('#edit_sem_entry').is(':visible') ||
      jQuery('#edit_entry').is(':visible') ||
      jQuery('#edit_inst_entry').is(':visible')) {
      jQuery(entry).remove();
      return;
    }

    // if there is already an entry set, kick him first before showing a new one
    if (this.entry) {
      jQuery(this.entry).fadeOut('fast');
      jQuery(this.entry).remove();
    }

    this.entry = entry;

    // fill values of overlay
    jQuery('#entry_hour_start').text(hour);
    jQuery('#entry_hour_end').text(hour + 1);
    jQuery('#entry_day').text(STUDIP.Calendar.day_names[day].toLocaleString());

    jQuery('#new_entry_hour').val(hour);
    jQuery('#new_entry_day').val(day);

    // show the overlay
    jQuery('#schedule_new_entry').show();

    // set the position of the overlay
    jQuery('#schedule_new_entry').css({
      top: Math.floor(entry.offset().top - jQuery('#schedule_new_entry').height() - 20),
      left: Math.floor(entry.offset().left)
    });

    if (jQuery('#schedule_new_entry').offset().top < 0) {
      jQuery('#schedule_new_entry').css({
        top:  Math.floor(entry.offset().top + entry.height() + 20)
      });
    }
  },

  /**
   * cancel adding of a new entry and fade out/remove all faded in/added boxes
   *
   * @param bool fade: if fade is true, fade out all boxes, otherwise just hide them
   *
   * @return: void
   */
  cancelNewEntry: function () {
    if (jQuery(this.entry).is(':visible')) {
      jQuery('#schedule_new_entry').fadeOut('fast');
      jQuery(this.entry).fadeOut('fast').remove();
    }

    jQuery('#edit_entry').fadeOut('fast');
    jQuery('#edit_inst_entry').fadeOut('fast');
  },

  /**
   * this function morphs from the quick-add box for adding a new entry to the schedule
   * to the larger box with more details to edit
   *
   * @return: void
   */
  showDetails: function () {

    // set the values for detailed view
    jQuery('select[name=entry_day]').val(Number(jQuery('#new_entry_day').val()) + 1);
    jQuery('input[name=entry_start_hour]').val(jQuery('#new_entry_hour').val());
    jQuery('input[name=entry_start_minute]').val('00');
    jQuery('input[name=entry_end_hour]').val(parseInt(jQuery('#new_entry_hour').val(), 10) + 1);
    jQuery('input[name=entry_end_minute]').val('00');

    jQuery('input[name=entry_title]').val(jQuery('#entry_title').val());
    jQuery('textarea[name=entry_content]').val(jQuery('#entry_content').val());

    jQuery('#edit_entry_drag').html(jQuery('#new_entry_drag').html());

    // morph to the detailed view
    jQuery('#schedule_new_entry').animate({
      left: Math.floor(jQuery(window).width() / 4),  // for safari
      width: '50%',
      top: '180px'
    }, 500, function () {
      jQuery('#edit_entry').fadeIn(400, function () {
        // reset the box
        jQuery('#schedule_new_entry').css({
          display: 'none',
          left: 0,
          width: '400px',
          top: 0,
          height: '230px',
          'margin-left': 0
        });
      });
    });
  },

  /**
   * show a popup conatining the details of the passed seminar
   * at the passed cycle
   *
   * @param  string  the seminar to be shown
   * @param  string  the cycle-id of the regular time-entry to be shown
   *                 (a seminar can have multiple of these
   */
  showSeminarDetails: function (seminar_id, cycle_id) {
    STUDIP.Schedule.cancelNewEntry();
    jQuery('#edit_sem_entry').fadeOut('fast');
    jQuery.get(STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/calendar/schedule/entryajax/' + seminar_id + '/' + cycle_id, function (data) {
      jQuery('#edit_sem_entry').remove();
      jQuery('body').append(data);
    });
  },

  /**
   * show a popup with the details of a regular schedule entry with passed id
   *
   * @param  string  the id of the schedule-entry
   */
  showScheduleDetails: function (id) {
    STUDIP.Schedule.cancelNewEntry();
    jQuery('#edit_entry').fadeOut('fast');
    jQuery.get(STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/calendar/schedule/entryajax/' + id, function (data) {
      jQuery('#edit_entry').remove();
      jQuery('body').append(data);
    });

  },

  /**
   * show a popup with the details of a group entry, containing several seminars
   *
   * @param  string  the id of the grouped entry to be displayed
   */
  showInstituteDetails: function (id) {
    STUDIP.Schedule.cancelNewEntry();
    jQuery('#edit_inst_entry').fadeOut('fast');
    jQuery.get(STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/calendar/schedule/groupedentry/' + id + '/true', function (data) {
      jQuery('#edit_inst_entry').remove();
      jQuery('body').append(data);
    });

    return false;
  },

  /**
   * hide a seminar-entry in the schedule (admin-version)
   *
   * @param  string  the seminar to be shown
   * @param  string  the cycle-id of the regular time-entry to be shown
   *                 (a seminar can have multiple of these
   */
  instSemUnbind : function (seminar_id, cycle_id) {
    STUDIP.Schedule.inst_changed = true;
    jQuery.ajax({
      type: 'GET',
      url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/calendar/schedule/adminbind/' + seminar_id + '/' + cycle_id + '/0/true'
    });

    jQuery('#' + seminar_id + '_' + cycle_id + '_hide').fadeOut('fast', function () {
      jQuery('#' + seminar_id + '_' + cycle_id + '_show').fadeIn('fast');
    });
  },

  /**
   * make a hidden seminar-entry visible in the schedule again
   *
   * @param  string  the seminar to be shown
   * @param  string  the cycle-id of the regular time-entry to be shown
   *                 (a seminar can have multiple of these
   */
  instSemBind : function (seminar_id, cycle_id) {
    STUDIP.Schedule.inst_changed = true;
    jQuery.ajax({
      type: 'GET',
      url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/calendar/schedule/adminbind/' + seminar_id + '/' + cycle_id + '/1/true'
    });

    jQuery('#' + seminar_id + '_' + cycle_id + '_show').fadeOut('fast', function () {
      jQuery('#' + seminar_id + '_' + cycle_id + '_hide').fadeIn('fast');
    });
  },

  /**
   * hide the popup of grouped-entry, containing a list of seminars.
   * returns true if the visiblity of one of the entries has been changed,
   * false otherwise
   *
   * @param  object  the element to be hidden
   *
   * @return  bool  true if the visibility of one seminar hase changed, false otherwise
   */
  hideInstOverlay: function (element) {
    if (STUDIP.Schedule.inst_changed) {
      return true;
    }
    jQuery(element).fadeOut('fast');
    return false;
  },

  /**
   * hide a seminar-entry in the schedule an remove it from display immediately
   *
   * @param  string  the id of the entry in the schedule
   * @param  string  the seminar to be shown
   * @param  string  the cycle-id of the regular time-entry to be shown
   *                 (a seminar can have multiple of these
   */
  hideEntry: function (id, seminar_id, cycle_id) {
    STUDIP.Schedule.cancelNewEntry();
    jQuery.ajax({
      type: 'GET',
      url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/calendar/schedule/unbind/' + seminar_id + '/' + cycle_id + '/true'
    });
    jQuery('#schedule_entry_' + id + '_' + seminar_id + '-' + cycle_id).fadeOut('fast').remove();
  },

  /**
   * calls STUDIP.Calendar.checkTimeslot to check that the time is valid
   *
   * @param  bool  returns true if the time is valid, false otherwise
   */
  checkFormFields: function () {
    if (!STUDIP.Calendar.checkTimeslot(jQuery('#schedule_entry_hours > input[name=entry_start_hour]'),
      jQuery('#schedule_entry_hours > input[name=entry_start_minute]'),
      jQuery('#schedule_entry_hours > input[name=entry_end_hour]'),
      jQuery('#schedule_entry_hours > input[name=entry_end_minute]'))) {

      jQuery('#schedule_entry_hours').addClass('invalid');
      jQuery('#schedule_entry_hours > span[class=invalid_message]').show();
      return false;
    }

    return true;
  }
};

STUDIP.Instschedule = {
  /**
   * show the details of a grouped-entry in the isntitute-calendar, containing several seminars
   *
   * @param  string  the id of the grouped-entry to be displayed
   */
  showInstituteDetails: function (id) {
    STUDIP.Schedule.cancelNewEntry();
    jQuery('#edit_inst_entry').fadeOut('fast');
    jQuery.get(STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/calendar/instschedule/groupedentry/' + id + '/true', function (data) {
      jQuery('#edit_inst_entry').remove();
      jQuery('body').append(data);
    });

    return false;
  }
};

/* ------------------------------------------------------------------------
 * jQuery datepicker
 * ------------------------------------------------------------------------ */
jQuery(function ($) {
  $.datepicker.regional.de = {
    closeText: 'schlie�en',
    prevText: '&#x3c;zur�ck',
    nextText: 'Vor&#x3e;',
    currentText: 'heute',
    monthNames: ['Januar', 'Februar', 'M�rz', 'April', 'Mai', 'Juni',
    'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
    monthNamesShort: ['Jan', 'Feb', 'M�r', 'Apr', 'Mai', 'Jun',
    'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
    dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
    dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
    dayNamesMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
    weekHeader: 'Wo',
    dateFormat: 'dd.mm.yy',
    firstDay: 1,
    isRTL: false,
    showMonthAfterYear: false,
    yearSuffix: ''
  };
  $.datepicker.setDefaults($.datepicker.regional.de);
});

STUDIP.SkipLinks = {
  activeElement : null,
  navigationStatus : 0,

  /**
   * Displays the skip link navigation after first hitting the tab-key
   * @param event: event-object of type keyup
   */
  showSkipLinkNavigation: function (event) {
    if (event.keyCode === 9) { //tab-key
      STUDIP.SkipLinks.moveSkipLinkNavigationIn();
      jQuery('.focus_box').removeClass('focus_box');
    }
    return false;
  },

  /**
   * shows the skiplink-navigation window by moving it from the left
   */
  moveSkipLinkNavigationIn: function () {
    if (STUDIP.SkipLinks.navigationStatus === 0) {
      var VpWidth = jQuery(window).width();
      jQuery('#skip_link_navigation li:first a').focus();
      jQuery('#skip_link_navigation').show().css({left: VpWidth / 2, opacity: 0});
      jQuery('#skip_link_navigation').animate({opacity: 1.0}, 500);
      STUDIP.SkipLinks.navigationStatus = 1;
    }
  },

  /**
   * removes the skiplink-navigation window by moving it out of viewport
   */
  moveSkipLinkNavigationOut: function () {
    if (STUDIP.SkipLinks.navigationStatus === 1) {
      jQuery(STUDIP.SkipLinks.box).hide();
      jQuery('#skip_link_navigation').animate({opacity: 0}, 500, function () {
        jQuery(this).css('left', '-600px');
      });
    }
    STUDIP.SkipLinks.navigationStatus = 2;
  },

  getFragment: function () {
    var fragmentStart = document.location.hash.indexOf('#');
    if (fragmentStart < 0) {
      return '';
    }
    return document.location.hash.substring(fragmentStart);
  },

  /**
   * Inserts the list with skip links
   */
  insertSkipLinks: function () {
    jQuery('#skip_link_navigation').prepend(jQuery('#skiplink_list'));
    jQuery('#skiplink_list').show();
    jQuery('#skip_link_navigation').attr('aria-busy', 'false');
    jQuery('#skip_link_navigation').attr('tabindex', '-1');
    STUDIP.SkipLinks.insertHeadLines();
    return false;
  },

  /**
   * sets the area (of the id) as the current area for tab-navigation
   * and highlights it
   */
  setActiveTarget: function (id) {
    var fragment = null;
    // set active area only if skip links are activated
    if (!jQuery('*').is('#skip_link_navigation')) {
        return false;
    }
    if (id) {
      fragment = id;
    } else {
      fragment = STUDIP.SkipLinks.getFragment();
    }
    if (jQuery('*').is(fragment) && fragment.length > 0 && fragment !== STUDIP.SkipLinks.activeElement) {
      STUDIP.SkipLinks.moveSkipLinkNavigationOut();
      jQuery(fragment).attr('tabindex', '-1').click().focus();
      STUDIP.SkipLinks.activeElement = fragment;
      return true;
    } else {
      jQuery('#skip_link_navigation li a').first().focus();
    }
    return false;
  },

  injectAriaRoles: function () {
    jQuery('#main_content').attr({
      role: 'main',
      'aria-labelledby': 'main_content_landmark_label'
    });
    jQuery('#layout_content').attr({
      role: 'main',
      'aria-labelledby': 'layout_content_landmark_label'
    });
    jQuery('#layout_infobox').attr({
      role: 'complementary',
      'aria-labelledby': 'layout_infobox_landmark_label'
    });
  },

  insertHeadLines: function () {
    var target = null;
    jQuery('#skip_link_navigation a').each(function () {
      target = jQuery(this).attr('href');
      if (jQuery(target).is('li,td')) {
        jQuery(target)
          .prepend('<h2 id="' + jQuery(target).attr('id') + '_landmark_label" class="skip_target">' + jQuery(this).text() + '</h2>');
      } else {
        jQuery(target)
          .before('<h2 id="' + jQuery(target).attr('id') + '_landmark_label" class="skip_target">' + jQuery(this).text() + '</h2>');
      }
      jQuery(target).attr('aria-labelledby', jQuery(target).attr('id') + '_landmark_label');
    });
  },

  initialize: function () {
    STUDIP.SkipLinks.insertSkipLinks();
    STUDIP.SkipLinks.injectAriaRoles();
    STUDIP.SkipLinks.setActiveTarget();
  }

};

jQuery(window.document).bind('keyup', STUDIP.SkipLinks.showSkipLinkNavigation);
jQuery(window.document).bind('ready', STUDIP.SkipLinks.initialize);
jQuery(window.document).bind('click', function (event) {
  if (!jQuery(event.target).is('#skip_link_navigation a')) {
    STUDIP.SkipLinks.moveSkipLinkNavigationOut();
  }
});

/* ------------------------------------------------------------------------
 * Forms
 * ------------------------------------------------------------------------ */

STUDIP.Forms = {
  initialize : function () {
    jQuery("input,textarea").each(function () {
      if (jQuery(this).attr('required') !== undefined) {
        jQuery(this).attr('aria-required', true);
      }
      if (jQuery(this).attr('pattern') && jQuery(this).attr('title')) {
        jQuery(this).attr('data-message', jQuery(this).attr('title'));
      }
    });

    //localized messages
    jQuery.tools.validator.localize('de', {
      '*'          : 'Bitte �ndern Sie ihre Eingabe'.toLocaleString(),
      ':email'     : 'Bitte geben Sie g�ltige E-Mail-Adresse ein'.toLocaleString(),
      ':number'    : 'Bitte geben Sie eine Zahl ein'.toLocaleString(),
      ':url'       : 'Bitte geben Sie eine g�ltige Web-Adresse ein'.toLocaleString(),
      '[max]'      : 'Bitte geben Sie maximal $1 Zeichen ein'.toLocaleString(),
      '[min]'      : 'Bitte geben Sie mindestens $1 Zeichen ein'.toLocaleString(),
      '[required]' : 'Dies ist ein erforderliches Feld'.toLocaleString()
    });

    jQuery('form').validator({
      position   : 'bottom left',
      offset     : [8, 0],
      message    : '<div><div class="arrow"/></div>',
      lang       : 'de',
      inputEvent : 'change'
    });

    jQuery('form').bind("onBeforeValidate", function () {
      jQuery("input").each(function () {
        jQuery(this).removeAttr('aria-invalid');
      });
    });

    jQuery('form').bind("onFail", function (e, errors) {
      jQuery.each(errors, function () {
        this.input.attr('aria-invalid', 'true');
      });
    });
  }
};


STUDIP.Messaging = {
  addToAdressees: function (username, name) {
    if (!jQuery("select#del_receiver").length) {
      jQuery("form[name=upload_form]")
        .attr("action", STUDIP.URLHelper.getURL("?", {
          "add_receiver[]": username,
          "add_receiver_button_x": true
        }))
        [0].submit();
      return;
    }
    if (!jQuery("select#del_receiver [value=" + username + "]").length) {
      jQuery("select#del_receiver")
        .append($('<option value="' + username + '">' + name + '</option>'))
        .attr("size", $(this).attr("size") + 1);
      jQuery.ajax({
        url: "?",
        data: {
          "add_receiver_button_x": true,
          "add_receiver": [username]
        }
      });
      window.setTimeout("$('input[name=adressee_parameter]').val('');", 10);
    }
  }
};
