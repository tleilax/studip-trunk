<?php
/*
 * text_format_test.php - unit tests for the TextFormat class
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once 'lib/classes/TextFormat.php';

function markupLine($markup, $matches)
{
    return '<hr>';
}

function markupHeading($markup, $matches)
{
    $level = max(1, 5 - strlen($matches[1]));

    return sprintf('<h%d class="content">%s</h%d>', $level, $markup->format($matches[2]), $level);
}

function markupText($markup, $matches, $contents)
{
    static $tag = array(
        '**' => 'b',
        '%%' => 'i',
        '__' => 'u',
        '##' => 'tt',
        '++' => 'big',
        '--' => 'small',
        '>>' => 'sup',
        '<<' => 'sub',
        '{-' => 'strike'
    );

    $key = $matches[0];

    return sprintf('<%s>%s</%s>', $tag[$key], $contents, $tag[$key]);
}

function markupSimple($markup, $matches)
{
    static $tag = array(
        '*' => 'b',
        '%' => 'i',
        '_' => 'u',
        '#' => 'tt',
        '+' => 'big',
        '-' => 'small',
        '>' => 'sup',
        '<' => 'sub'
    );

    $key = $matches[0][0];
    $text = str_replace($key, ' ', $matches[1]);

    return sprintf('<%s>%s</%s>', $tag[$key], $markup->quote($text), $tag[$key]);
}

function markupImage($markup, $matches)
{
    if (strlen($matches[1]) > 1) {
        $title = $markup->format(substr($matches[1], 1));
    } else {
        $title = '';
    }

    return sprintf('<img src="%s" title="%s">', $markup->quote($matches[2]), $title);
}

function markupTable($markup, $matches)
{
    $rows = explode("\n", rtrim($matches[0]));
    $result = '<table class="content">';

    foreach ($rows as $row) {
        $cells = explode('|', trim(trim($row), '|'));
        $result .= '<tr>';

        foreach ($cells as $cell) {
            $result .= '<td>';
            $result .= $markup->format($cell);
            $result .= '</td>';
        }

        $result .= '</tr>';
    }

    $result .= '</table>';

    return $result;
}

function markupList($markup, $matches)
{
    $rows = explode("\n", rtrim($matches[0]));
    $indent = 0;

    foreach ($rows as $row) {
        list($level, $text) = explode(' ', $row, 2);
        $level = strlen($level);

        if ($indent < $level) {
            for (; $indent < $level; ++$indent) {
                $type = $row[$indent] == '=' ? 'ol' : 'ul';
                $result .= sprintf('<%s><li>', $type);
                $types[] = $type;
            }
        } else {
            for (; $indent > $level; --$indent) {
                $result .= sprintf('</li></%s>', array_pop($types));
            }

            $result .= '</li><li>';
        }

        $result .= $markup->format($text);
    }

    for (; $indent > 0; --$indent) {
        $result .= sprintf('</li></%s>', array_pop($types));
    }

    return $result;
}

function markupIndent($markup, $matches)
{
    $text = preg_replace('/^  /m', '', $matches[0]);

    return sprintf('<p class="indent">%s</p>', $markup->format($text));
}

function markupNop($markup, $matches)
{
    return $markup->quote($matches[1]);
}

function markupPre($markup, $matches, $contents)
{
    return sprintf('<pre>%s</pre>', $contents);
}

function markupCode($markup, $matches)
{
    return highlight_string($matches[1], true);
}

function markupQuote($markup, $matches, $contents)
{
    if (strlen($matches[1]) > 1) {
        $title = sprintf(_('%s hat geschrieben:'), $markup->format(substr($matches[1], 1)));
    } else {
        $title = _('Zitat:');
    }

    return sprintf('<blockquote class="quote"><b>%s</b><hr>%s</blockquote>', $title, $contents);
}

function markupLink($markup, $matches)
{
    if (strlen($matches[1]) > 1) {
        $text = $markup->format(substr($matches[1], 1, -1));
    } else {
        $text = $markup->quote($matches[2]);
    }

    return sprintf('<a href="%s">%s</a>', $markup->quote($matches[2]), $text);
}

function markupMail($markup, $matches)
{
    if (strlen($matches[1]) > 1) {
        $text = $markup->format(substr($matches[1], 1, -1));
    } else {
        $text = $markup->quote($matches[2]);
    }

    return sprintf('<a href="mailto:%s">%s</a>', $markup->quote($matches[2]), $text);
}

class TextFormatTest extends UnitTestCase
{
    public function setUp()
    {
        $markup = new TextFormat();

        $markup->addMarkup('line', '^--+$', NULL, 'markupLine');
        $markup->addMarkup('heading', '^(!{1,4})([^\n]+)', NULL, 'markupHeading');

        $markup->addMarkup('bold', '\*\*', '\*\*', 'markupText');
        $markup->addMarkup('italics', '%%', '%%', 'markupText');
        $markup->addMarkup('underline', '__', '__', 'markupText');
        $markup->addMarkup('verb', '##', '##', 'markupText');
        $markup->addMarkup('big', '\+\+', '\+\+', 'markupText');
        $markup->addMarkup('small', '--', '--', 'markupText');
        $markup->addMarkup('super', '>>', '>>', 'markupText');
        $markup->addMarkup('sub', '<<', '<<', 'markupText');
        $markup->addMarkup('strike', '\{-', '-\}', 'markupText');

        $markup->addMarkup('simple_bold', '(?<=\s|^)\*(\S+)\*(?=\s|$)', NULL, 'markupSimple');
        $markup->addMarkup('simple_italics', '(?<=\s|^)%(\S+)%(?=\s|$)', NULL, 'markupSimple');
        $markup->addMarkup('simple_underline', '(?<=\s|^)_(\S+)_(?=\s|$)', NULL, 'markupSimple');
        $markup->addMarkup('simple_verb', '(?<=\s|^)#(\S+)#(?=\s|$)', NULL, 'markupSimple');
        $markup->addMarkup('simple_big', '(?<=\s|^)\+(\S+)\+(?=\s|$)', NULL, 'markupSimple');
        $markup->addMarkup('simple_small', '(?<=\s|^)-(\S+)-(?=\s|$)', NULL, 'markupSimple');
        $markup->addMarkup('simple_super', '(?<=\s|^)>(\S+)>(?=\s|$)', NULL, 'markupSimple');
        $markup->addMarkup('simple_sub', '(?<=\s|^)<(\S+)<(?=\s|$)', NULL, 'markupSimple');

        $markup->addMarkup('image', '\[img(=.*?)?\](\S+)', NULL, 'markupImage');
        $markup->addMarkup('table', '(^\|[^\n]*\|[^\n]*\n)+', NULL, 'markupTable');
        $markup->addMarkup('list', '(^[=-]+ [^\n]+\n)+', NULL, 'markupList');
        $markup->addMarkup('indent', '(^  [^\n]+\n)+', NULL, 'markupIndent');

        $markup->addMarkup('nop', '\[nop\](.*?)\[\/nop\]', NULL, 'markupNop');
        $markup->addMarkup('pre', '\[pre\]', '\[\/pre\]', 'markupPre');
        $markup->addMarkup('code', '\[code\](.*?)\[\/code\]', NULL, 'markupCode');
        $markup->addMarkup('quote', '\[quote(=.*?)?\]', '\[\/quote\]', 'markupQuote');

        $markup->addMarkup('link', '(\[.*?\])?\b(https?:\/\/\S+)', NULL, 'markupLink');
        $markup->addMarkup('mail', '(\[.*?\])?\b([\w!#%+.-]+@[[:alnum:].-]+)', NULL, 'markupMail');

        $this->markup = $markup;
    }

    public function testLine()
    {
        $input = "Test\n--\nTest";
        $expected = "Test\n<hr>\nTest";
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testHeading()
    {
        $input = '!!%%Überschrift%%';
        $expected = '<h3 class="content"><i>Überschrift</i></h3>';
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testBoldItalics()
    {
        $input = '**some %%code%%**';
        $expected = '<b>some <i>code</i></b>';
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testBigSmall()
    {
        $input = '++some --code--++';
        $expected = '<big>some <small>code</small></big>';
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testSimpleBoldItalics()
    {
        $input = '*bold*text* %some%italics%';
        $expected = '<b>bold text</b> <i>some italics</i>';
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testMissingClose()
    {
        $input = '**missing %%close';
        $expected = $input;
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testCloseBeforeOpen()
    {
        $input = 'there is -}no markup{- here';
        $expected = $input;
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testIncorrectNesting()
    {
        $input = '** test %% test ** test %%';
        $expected = '** test <i> test ** test </i>';
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testImage()
    {
        $input = '[img=Stud.IP-Logo]http://www.studip.de/logo.png';
        $expected = '<img src="http://www.studip.de/logo.png" title="Stud.IP-Logo">';
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testTable()
    {
        $input = "|Name|Matrikelnummer|Studiengang|\n|Max Mustermann|55555|Mathe Diplom|\n";
        $expected = '<table class="content">'
                   .'<tr>'
                   .'<td>Name</td>'
                   .'<td>Matrikelnummer</td>'
                   .'<td>Studiengang</td>'
                   .'</tr>'
                   .'<tr>'
                   .'<td>Max Mustermann</td>'
                   .'<td>55555</td>'
                   .'<td>Mathe Diplom</td>'
                   .'</tr>'
                   .'</table>';
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testList()
    {
        $input = "- Einführung\n- Hauptteil\n-= Argument 1\n-= Argument 2\n- Schluss\n";
        $expected = '<ul>'
                   .'<li>Einführung</li>'
                   .'<li>Hauptteil<ol>'
                   .'<li>Argument 1</li>'
                   .'<li>Argument 2</li>'
                   .'</ol></li>'
                   .'<li>Schluss</li>'
                   .'</ul>';
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testIndent()
    {
        $input = "  Ebene 1\n    Ebene 2\n    Ebene 2\n  Ebene 1\n";
        $expected = '<p class="indent">'
                   ."Ebene 1\n"
                   .'<p class="indent">'
                   ."Ebene 2\n"
                   ."Ebene 2\n"
                   .'</p>'
                   ."Ebene 1\n"
                   .'</p>';
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testNop()
    {
        $input = '[nop]**A**[quote]B[/quote]{-C-}[/nop]';
        $expected = '**A**[quote]B[/quote]{-C-}';
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testPre()
    {
        $input = '[pre]**A**{-C-}[/pre]';
        $expected = '<pre><b>A</b><strike>C</strike></pre>';
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testQuote()
    {
        $input = '[quote=_Anonymous_]some text[/quote]';
        $expected = '<blockquote class="quote">'
                   .'<b><u>Anonymous</u> hat geschrieben:</b><hr>some text'
                   .'</blockquote>';
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testLink()
    {
        $input = '[Testlink]https://www.studip.de/';
        $expected = '<a href="https://www.studip.de/">Testlink</a>';
        $this->assertEqual($this->markup->format($input), $expected);
    }

    public function testMail()
    {
        $input = '[Mail]some.user@example.com';
        $expected = '<a href="mailto:some.user@example.com">Mail</a>';
        $this->assertEqual($this->markup->format($input), $expected);
    }
}
