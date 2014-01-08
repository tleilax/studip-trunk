CKEDITOR.plugins.add('studip-wiki', {
    icons: 'wikilink',
    init: function (editor) {
        // utilities
        function isWikiLink(element) {
            var link = element.getAscendant('a', true);
            var wiki = STUDIP.URLHelper.getURL('wiki.php');
            return link && link.getAttribute('href').indexOf(wiki) == 0;
        }

        function cursorInWikiLink(element) {
            var selection = editor.getSelection();
            console.log('cursorInWikiLink: ' + selection.getSelectedText() + '.');

            // find link's start and end position
            var range = selection.getRanges()[0],
                offset = range.startOffset,
                text = range.startContainer.getText(),
                linkStart = text.lastIndexOf('[[', offset + 2),
                linkEnd = text.indexOf(']]', offset - 2);
                // NOTE linkEnd doesn't work correctly if user edited the text,
                // since CKEditor then splits the current tag into multiple
                // ranges and the ']]' is no longer in our startRange
                // This produces the following output with the current
                // console.log-calls (first call: contextmenu without edit,
                // second call contextmenu after deleting | from [[WikiSeite|]]:
                // cursorInWikiLink: WikiSeite.
                // text "[[WikiSeite]] und KeineWikiSeite" offset 2, 0, 11, false
                // cursorInWikiLink: WikiSeite.
                // text "[[Wiki" offset 2, 0, -1, false

            // check that link doesn't overlap with another link
            var overlapEnd = text.lastIndexOf(']]', offset + 2),
                overlapStart = text.indexOf('[[', offset - 2),
                overlaps = (overlapEnd > linkStart && overlapEnd < linkEnd)
                    || (overlapStart < linkEnd && overlapStart > linkStart);

            // NOTE since real selections are ignored (only start position is
            // used) the only way to find an overlap is: ]]_[[ therefore switch
            // to insert mode in the overlap case

            console.log('text "' + text + '" offset ' + offset + ', ' + linkStart + ', ' + linkEnd + ', ' + (overlaps ? 'true' : 'false'));

            return !overlaps && linkStart >= 0 && linkEnd >= 0;
        }

        // add toolbar button and dialog for editing Stud.IP wiki links
        editor.addCommand('wikiDialog', new CKEDITOR.dialogCommand('wikiDialog'));
        editor.ui.addButton('wikilink', {
            label: 'Stud.IP-Wiki Link einf&uuml;gen',
            command: 'wikiDialog',
            toolbar: 'insert,70'
        });
        CKEDITOR.dialog.add('wikiDialog', this.path + 'dialogs/wikilink.js.php' );

//        editor.on('selectionChange', function(event){
//            if (editor.getSelection().getSelectedText()) {
//                console.log(editor.getSelection().getSelectedText());
//                editor.commands.wikiDialog.disable();
//            } else {
//                console.log('enable');
//                editor.commands.wikiDialog.enable();
//            }
//        });

        // add context menu for existing Stud.IP wiki links
        if (editor.contextMenu) {
            editor.addMenuGroup('studipGroup');
            editor.addMenuItem('wikilinkItem', {
                label: 'Stud.IP-Wiki Link bearbeiten',
                icon: this.path + 'icons/wikilink.png', // same as plugin icon
                command: 'wikiDialog',
                group: 'studipGroup'
            });
            editor.contextMenu.addListener(function(element) {
                if (isWikiLink(element)) {
                    return {
                        wikilinkItem: CKEDITOR.TRISTATE_OFF
                    };
                }
                if (cursorInWikiLink(element)) {
                    return {
                        wikilinkItem: CKEDITOR.TRISTATE_OFF
                    };
                }
            });
        }
//
//        // open dialog when double-clicking link
//        editor.on('doubleclick', function(event) {
//            var element = CKEDITOR.plugins.link.getSelectedLink(editor)
//                          || event.data.element;
//
//            if (isWikiLink(element)) {
//                event.data.dialog = 'wikiDialog';
//            }
//        });
    }
});
