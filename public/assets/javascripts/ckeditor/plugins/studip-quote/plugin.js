// widget for handling studip-style quoting with author name
(function studipQuotePlugin(CKEDITOR) {

    CKEDITOR.plugins.add('studip-quote', {
        //require: 'widget',
        icons: 'blockquote',
        hidpi: true,
        init: initPlugin
    });

    function initPlugin(editor) {
        editor.addCommand('insertStudipQuote', {
            exec: function (editor) {
                editor.insertHtml(STUDIP.format.applyQuote('Quote', 'Author'));
            }
        });
        editor.ui.addButton('blockquote', {
            label: 'Insert Quotation',
            command: 'insertStudipQuote',
            toolbar: 'insert'
        });
    }

    // TODO this code has a number of problems, remove before
    // merging to main, if no solution is found!
    function initPlugin_Widget(editor) {
        editor.widgets.add('studip-quote', {
            // TODO place button label in editor.lang['studip-quote'].* property
            button: 'Quote some text',
            template:
                '<blockquote>' +
                // TODO doesn't work as expected: CKEditor always
                // surronds <div>-contents in a <p>-block
                '  <div class="author">Author</div>' +
                '  <div class="content">Content</div>' +
                '</blockquote>',
            editables: {
                author: {
                    selector: 'div.author',
                    allowedContent: 'text'
                },
                content: {
                    selector: 'blockquote'
                }
            },
            allowedContent: 'blockquote; div(!author,!content)',
            requiredContent: 'blockquote; div(!author,!content)',
            upcast: function (element) {
                return element.name === 'blockquote';
            }
        });

//         editor.ui.addButton( 'Blockquote', {
//            label: editor.lang.blockquote.toolbar,
//            command: 'blockquote',
//            toolbar: 'blocks,10'
//        });
    }
})(CKEDITOR);
