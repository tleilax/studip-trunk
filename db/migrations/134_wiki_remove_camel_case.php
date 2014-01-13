<?php
/**
 * 134_wiki_remove_camel_case.php - Enclose wiki links in square brackets.
 *
 **
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Stud.IP
 * @copyright   (c) 2014 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.0
 * @author      Robert Costa <rcosta@uos.de>
 */

class WikiRemoveCamelCase extends Migration {

    function description() {
        return 'Enclose camel-case wiki links in double square brackets.';
    }

    function test() {
        $tests = array(
            // invalid links stay unchanged
            array('[[]]', '[[]]'),
            array('[[|]]', '[[|]]'),

            // valid links stay unchanged
            array('[[a]]', '[[a]]'),
            array('[[a|b]]', '[[a|b]]'),
            array('c[[a|b]]d', 'c[[a|b]]d'),
            array('[[c[[a|b]]d]]', '[[c[[a|b]]d]]'),
		    array('Ein [[WikiLink]].',  'Ein [[WikiLink]].'),
            array('Ein [[Wikilink]] testet', 'Ein [[Wikilink]] testet'),
            array('Ein [[[Wikilink]] testet', 'Ein [[[Wikilink]] testet'),
            array('Ein [[Wikilink]]] testet', 'Ein [[Wikilink]]] testet'),
            array('Ein [[[Wikilink]]] testet', 'Ein [[[Wikilink]]] testet'),
            array('Ein [[[WikiLink|irgendwas]]] testet', 'Ein [[[WikiLink|irgendwas]]] testet'),

            // camel-case links get enclosed in brackets
            array('Ein WikiLink.',      'Ein [[WikiLink]].'),
		    array('Ein [[WikiLink.',    'Ein [[[[WikiLink]].'),
		    array('Ein [[[WikiLink.',   'Ein [[[[[WikiLink]].'),
		    array('Ein [WikiLink].',    'Ein [[[WikiLink]]].'),
			array('EinWiki-Link',       '[[EinWiki]]-Link'),
			//array('ÄlteresWiki',        '[[ÄlteresWiki]]'),     // TODO
            //array('&Auml;lteresWiki',   '[[&Auml;lteresWiki]]'),
            array('B&auml;&Auml;b',     '[[B&auml;&Auml;b]]'),
        );

        $errors = 0;
        foreach ($tests as $t) {
            $fixed = $this->fixWikiLinks($t[0]);
		    if ($fixed != $t[1]) {
                echo "<p>Error: $t[0] => $fixed instead $t[1].</p>";
                $errors++;
		    }
        }
        echo '<p>' . count($tests) . ' tests.</p>';
        if ($errors) {
            echo "<p>$errors errors.</p>";
        }
        exit();
    }

    function fixWikiLinks($f, $body) {
        fputs($f, "<p style=\"color:red\">$body</p>");
        // $camel_case = wiki-links-short in WikiFormat.php before migration
        $camel_case = '\b('
            . '(?:[A-ZÄÖÜ]|&[AOU]uml;)'              // upper-case letter
            . '(?:[a-z\däöüß]|&[aou]uml;|&szlig;)+'  // lower-case letter, or digit
            . '(?:[A-ZÄÖÜ]|&[AOU]uml;)'
            . '(?:[\w\däöüß]|&[aou]uml;|&szlig;)*'   // underscore, digit, lower-case letter
            . ')';
        $open_tag = '(?:\[\[)';
        $close_tag = '(?:(?:\|(?:.*?))?\]\])'; // includes optional text
        $wiki_link = "/($open_tag)?$camel_case($close_tag)?/";
        $body = preg_replace_callback($wiki_link, function($m) use ($open_tag, $close_tag) {
            $has_open = preg_match('/' . $open_tag . '/', $m[1]);
            $has_close = preg_match('/' . $close_tag . '/', $m[3]);
            $is_enclosed = $has_open && $has_close;
            return $is_enclosed ? $m[0] : $m[1] . '[[' . $m[2] . ']]' . $m[3];
        }, $body);
        fputs($f, "<p>$body</p>");
        fflush($f);

        return $body;
    }

    function up() {
        //$this->test(); // TODO remove debug code (test calls exit())

        // fetch all wiki versions
        $f = fopen('/tmp/log.txt', 'w');

        $stmt = DBManager::get()->prepare('SELECT * FROM wiki');
        $stmt->execute();
        while ($wiki_page = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputs($f, '<p>r: '.$wiki_page['range_id'].' k:'.$wiki_page['keyword'].' v:'.$wiki_page['version'].'</p>');
            DBManager::get()->prepare(
                'UPDATE wiki SET body=?'
                . ' WHERE range_id=? AND keyword=? AND version=?'
            )->execute(array(
                $this->fixWikiLinks($f, $wiki_page['body']),
                $wiki_page['range_id'],
                $wiki_page['keyword'],
                $wiki_page['version']
            ));
        }
    }

    function down() {
    }
}
