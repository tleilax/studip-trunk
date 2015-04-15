<?php
/**
 * js-format.php - 
 * Include this file in HTML-files after Stud.IP's JS library is loaded.
 */
use \StudipFormat;
?>
<script type="text/javascript">
    STUDIP.format = {
        templates: <?= json_encode(StudipFormat::getTemplates() ?: new stdClass) ?>,
        applyQuote: function applyQuote(text, name) {
            var quoteTemplates = STUDIP.format.templates['quote'],
                template = name ? 'withName' : 'withoutName';

            var result = quoteTemplates[template]
                    .replace('{{text}}', text)
                    .replace('{{name}}', name);

            return result;
        }
    };
</script>

