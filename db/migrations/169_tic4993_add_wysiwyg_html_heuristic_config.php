<?php
class Tic4993AddWysiwygHtmlHeuristicConfig extends Migration
{
    public function description()
    {
        return 'Adds the config entry "WYSIWYG_HTML_HEURISTIC_FALLBACK" that indicates '
             . 'whether the HTML heuristic should be used to detect mixed content'
             . '(Stud.IP Markup and HTML).';
    }

    public function up()
    {
        /* 
         * determine whether the heuristic should be enabled in this Stud.IP installation:
         * - if WYSIWYG was enabled before: there is maybe mixed content available: enable heuristic
         * - else: there is not any mixed content available: disable heuristic.
         */
        if (Config::get()->WYSIWYG) {
            $current_value = 1;
        } else {
            $current_value = 0;
        }
        
        Config::get()->create('WYSIWYG_HTML_HEURISTIC_FALLBACK', array(
            'value'       => $current_value,
            'is_default'  => '0',
            'type'        => 'boolean',
            'range'       => 'global',
            'section'     => 'global',
            'description' => 'Aktiviert die Heuristik um automatisch vermischte Inhalte (Stud.IP '
                           . 'Markup und HTML) zu erkennen. Diese Option sollte nur bei '
                           . 'Installationen aktiviert werden, die den WYSIWYG-Editor bereits vor '
                           . 'Stud.IP Version 3.3 aktiviert haben.',
        ));
    }

    public function down()
    {
        Config::get()->delete('WYSIWYG_HTML_HEURISTIC_FALLBACK');
    }
}
