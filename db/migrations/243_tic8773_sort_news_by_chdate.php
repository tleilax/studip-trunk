<?php


//@author Moritz Strohm <strohm@data-quest.de>


class Tic8773SortNewsByChdate extends Migration
{
    public function up()
    {
        Config::get()->create(
            'SORT_NEWS_BY_CHDATE',
            [
                'type' => 'boolean',
                'value' => false,
                'section' => 'view',
                'range' => 'global',
                'description' => 'Wenn diese Einstellung gesetzt ist werden Ankündigungen nach ihrem letzten Änderungsdatum statt ihrem Erstellungsdatum sortiert angezeigt.'
            ]
        );
    }

    public function down()
    {
        Config::get()->delete('SORT_NEWS_BY_CHDATE');
    }
}
