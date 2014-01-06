CKEDITOR.dialog.add('wikiDialog', function (editor) {
    // studip wiki link specification
    // * allowed characters: a-z.-:()_§/@# äöüß
    // * enclose in double-brackets: [[wiki link]]
    // * leading or trailing whitespace is allowed!!
    // * extended: [[wiki link| displayed text]]
    // * displayed text characters can be anything but ]

    // utilities

    function getParameterByName(name, query) {
        query = typeof query === 'undefined' ? location.search : query;
        // http://stackoverflow.com/a/901144/641481
        name = name.replace(/[\[]/, '\\\[').replace(/[\]]/, '\\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)'),
            results = regex.exec(query);
        return results == null ? '' : decodeURIComponent(
            results[1].replace(/\+/g, ' '));
    }

    function getQueryString(href) {
        return href ? ((href.split('?')[1] || '').split('#')[0] || '') : '';
    }

    function array_flip(trans) {
        // http://phpjs.org/functions/array_flip/
        // http://kevin.vanzonneveld.net
        // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +      improved by: Pier Paolo Ramon (http://www.mastersoup.com/)
        // +      improved by: Brett Zamir (http://brett-zamir.me)
        // *     example 1: array_flip( {a: 1, b: 1, c: 2} );
        // *     returns 1: {1: 'b', 2: 'c'}
        // *     example 2: ini_set('phpjs.return_phpjs_arrays', 'on');
        // *     example 2: array_flip(array({a: 0}, {b: 1}, {c: 2}))[1];
        // *     returns 2: 'b'

        // duck-type check for our own array()-created PHPJS_Array
        if (trans && typeof trans === 'object' && trans.change_key_case) {
            return trans.flip();
        }

        var tmp_ar = {};
        for (var key in trans) {
            if (trans.hasOwnProperty(key)) {
                tmp_ar[trans[key]] = key;
            }
        }
        return tmp_ar;
    }

    var translation = {
        ' ': '%20', '#': '%23', '(': '%28', ')': '%29',
        '/': '%2F', ':': '%3A', '@': '%40', '§': '%A7',
        'Ä': '%C4', 'Ö': '%D6', 'Ü': '%DC', 'ß': '%DF',
        'ä': '%E4', 'ö': '%F6', 'ü': '%FC'
    };
    var backtrans = array_flip(translation);

    function toWindows1252(text) {
        // replace special chars with windows 1252 encoding
        // test string: azAZ09_-. #()/:@§ÄÖÜßäöü
        // TODO create regexp from translation keys
        return text.replace(/[ #()/:@§ÄÖÜßäöü]/g, function(match) {
            return translation[match];
      });
    }

    function fromWindows1252(text) {
        // TODO create regexp from backtrans keys
        // don't replace # === 23!!
        return text.replace(/%(20|28|29|2F|3A|40|A7|C4|D6|DC|DF|E4|F6|FC)/g,
                            function(match) {
            return backtrans[match];
        });
    }

    function getWikiPage(href) {
        return getParameterByName(
            'keyword', '?' + fromWindows1252(getQueryString(href)));
    }

    function getWikiLink(wikipage) {
        return STUDIP.URLHelper.getURL('wiki.php', {
            cid: getParameterByName('cid')
        }) + '&keyword=' + toWindows1252(wikipage);
    }

    // dialog
    return {
        title: "Stud.IP-Wiki Link",
        minWidth: 400,
        minHeight: 200,
        contents: [{
            id: 'tab-link',
            label: "Stud.IP-Wiki Link",
            elements: [{
                type: 'text',
                id: 'wikipage',
                label: "Titel der Wiki-Seite",
                validate: CKEDITOR.dialog.validate.regex(
                    /^[\w\.\-\:\(\)§\/@# ÄÖÜäöüß]+$/i,
                    "Der Seitenname muss aus mindestens einem Zeichen bestehen"
                    + " und darf nur folgende Zeichen enthalten:"
                    + " a-z A-Z ÄÖÜ äöü ß 0-9 -_:.( )/@#§ und das Leerzeichen."),
                setup: function(link) {
//                    this.setValue(getWikiPage(link.getAttribute('href')));
                },
                commit: function(link) {
                    var wikiLink = '[[' + this.getValue() + ']]';
//                    var href = getWikiLink(this.getValue());
//                    link.setAttribute('href', href);
//                    link.setAttribute('data-cke-saved-href', href);
//                    link.setAttribute('class', 'wiki-link');
                }
            }, {
                type: 'text',
                id: 'usertext',
                label: "Angezeigter Text (optional)",
                validate: CKEDITOR.dialog.validate.regex(
                    /^[^\]]*$/i,
                    "Die schließende eckige Klammer, ], ist im angezeigten"
                    + " Text leider nicht erlaubt."),
                setup: function(link) {
//                    var usertext = link.getText(),
//                        wikipage = getWikiPage(link.getAttribute('href'));
//                    this.setValue(usertext === wikipage ? '' : usertext);
                },
                commit: function(link) {
//                    var usertext = this.getValue(),
//                        wikipage = this._.dialog.getValueOf('tab-link', 'wikipage');
//                    link.setText(usertext || wikipage);
                }
            }]
        }],
        onShow: function() {
            // selection empty (_ = cursor position)
            // _                    empty text       ==> insert mode
            // ab_cd                not in wiki link ==> insert mode
            // [[ab_cd]]            in wiki link     ==> extend selection
            // [_[abcd]]            in wiki link     ==> extend selection
            // [[abcd]_]            in wiki link     ==> extend selection
            // _[[abcd]]            at wiki link     ==> extend selection
            // [[abcd]]_            at wiki link     ==> extend selection
            //
            // selection empty, link empty ==> extend selection
            // _[[]]
            // [_[]]
            // [[_]]
            // [[]_]
            // [[]]_
            //
            // selection real (_ = selection start/end)
            // a_bcd_e              not in wiki link ==> text -> link
            // [[a_bc_d]]           in wiki link     ==> extend selection
            // [_[abc]_]            in wiki link     ==> extend selection
            // _[[abd]]_            enclosing wiki link ==> shrink selection
            // _a[[bc]]d_           enclosing wiki link ==> shring selection
            // a_b[[c_d]]           enclosing wiki link ==> extend/shrink
            // [[a_b]]c_d           enclosing wiki link ==> extend/shrink
            // [[a_b]][[c_d]]       invalid selection ==> deactivate button
            // 
            // simplification: set end to start for real selections

            // get selection
            var selection = editor.getSelection();
            if (selection.getSelectedText().length) {
                console.log('!selectionEmpty');
            }

            // find link's start and end position
            var range = selection.getRanges()[0],
                offset = range.startOffset,
                text = range.startContainer.getText(),
                linkStart = text.lastIndexOf('[[', offset + 2),
                linkEnd = text.indexOf(']]', offset - 2);

            // check that link doesn't overlap with another link
            var overlapEnd = text.lastIndexOf(']]', offset + 2),
                overlapStart = text.indexOf('[[', offset - 2),
                overlaps = (overlapEnd > linkStart && overlapEnd < linkEnd)
                    || (overlapStart < linkEnd && overlapStart > linkStart);

            // NOTE since real selections are ignored (only start position is
            // used) the only way to find an overlap is: ]]_[[ therefore switch
            // to insert mode in the overlap case

            // select complete link
            if (overlaps || linkStart < 0 || linkEnd < 0) {
                linkStart = linkEnd = offset;
            } else {
                linkEnd += 2;
            }
            range.setStart(range.startContainer, linkStart);
            range.setEnd(range.startContainer, linkEnd);
            selection.selectRanges([range]);
            console.log(selection.getSelectedText());

            // regexp matches [[page]] and [[page|text]]
            var existingLink = /^\[\[([^\[\|]*)(\|([^\]]*))?\]\]/
                    .exec(selection.getSelectedText());

            if (existingLink && existingLink.length > 1) {
                page = existingLink[1];
                text = existingLink.length > 3 ? existingLink[3] : '';
                this.setValueOf('tab-link', 'wikipage', page);
                this.setValueOf('tab-link', 'usertext', text);
            }
            
//            var element = editor.getSelection().getStartElement();
//            if (element) {
//                element = element.getAscendant('a', true);
//            }
//
//            // if no link is selected, insert a new one
//            this.insertMode = !element || element.getName() != 'a';
//            if (this.insertMode) {
//                element = editor.document.createElement('a');
//                var text = editor.getSelection().getSelectedText();
//                if (text) {
//                    this.setValueOf('tab-link', 'wikipage', text);
//                }
//            } else {
//                this.setupContent(element);
//            }
//
//            this.link = element;
        },
        onOk: function() {
            var page = this.getValueOf('tab-link', 'wikipage'),
                text = this.getValueOf('tab-link', 'usertext'),
                link = '[[' + page + '|' + text + ']]';
            //selection.insertText(link);
            editor.insertText(link);
//            this.commitContent(this.link);
//            if (this.insertMode) {
//                editor.insertElement(this.link);
//            }
        }
    };
});
