<?php

require '../lib/bootstrap.php';

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("tutor");

include ("lib/seminar_open.php"); // initialise Stud.IP-Session

PageLayout::setTitle(_('Beispiel-Tabelle'));

ob_start();
?>
<table class="default">
    <caption>Caption</caption>
    <colgroup>
        <col width="1%">
        <col width="20%">
        <col>
        <col width="20%">
    </colgroup>
    <thead>
        <tr>
            <th>#1</th>
            <th>Header #2</th>
            <th>Header #3</th>
            <th>Header #4</th>
        </tr>
    </thead>
    <tbody class="togglable has-actions">
        <tr>
            <th colspan="4">Content Header #1</th>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="foo[]">
            </td>
            <td>Content Row #2-2</td>
            <td>Content Row #2-3</td>
            <td class="actions">
                <?= Assets::img('icons/16/blue/edit') ?>
                <?= Assets::img('icons/16/blue/trash') ?>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="foo[]">
            </td>
            <td>Content Row #2</td>
            <td>Content Row #3</td>
            <td class="actions">
                <?= Assets::img('icons/16/blue/edit') ?>
                <?= Assets::img('icons/16/blue/trash') ?>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="foo[]">
            </td>
            <td>Content Row #2</td>
            <td>Content Row #3</td>
            <td class="actions">
                <?= Assets::img('icons/16/blue/edit') ?>
                <?= Assets::img('icons/16/blue/trash') ?>
            </td>
        </tr>
    </tbody>
    <tbody class="togglable has-actions">
        <tr>
            <th colspan="4">Content Header #2</th>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="foo[]">
            </td>
            <td>Content Row #2-2</td>
            <td>Content Row #2-3</td>
            <td class="actions">
                <?= Assets::img('icons/16/blue/edit') ?>
                <?= Assets::img('icons/16/blue/trash') ?>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="foo[]">
            </td>
            <td>Content Row #2</td>
            <td>Content Row #3</td>
            <td class="actions">
                <?= Assets::img('icons/16/blue/edit') ?>
                <?= Assets::img('icons/16/blue/trash') ?>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="foo[]">
            </td>
            <td>Content Row #2</td>
            <td>Content Row #3</td>
            <td class="actions">
                <?= Assets::img('icons/16/blue/edit') ?>
                <?= Assets::img('icons/16/blue/trash') ?>
            </td>
        </tr>
    </tbody>
    <tbody class="togglable">
        <tr>
            <th colspan="4">Content Header #3</th>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="foo[]">
            </td>
            <td>Content Row #2-2</td>
            <td>Content Row #2-3</td>
            <td class="actions">
                <?= Assets::img('icons/16/blue/edit') ?>
                <?= Assets::img('icons/16/blue/trash') ?>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="foo[]">
            </td>
            <td>Content Row #2</td>
            <td>Content Row #3</td>
            <td class="actions">
                <?= Assets::img('icons/16/blue/edit') ?>
                <?= Assets::img('icons/16/blue/trash') ?>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="foo[]">
            </td>
            <td>Content Row #2</td>
            <td>Content Row #3</td>
            <td class="actions">
                <?= Assets::img('icons/16/blue/edit') ?>
                <?= Assets::img('icons/16/blue/trash') ?>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">
                <span class="actions">
                  Seite 1 | 2 | ... | 10
                </span>
              
                <input type="checkbox" data-proxyfor="tbody :checkbox">
                Alle auswählen
                
                <select>
                    <option>Aktion...</option>
                </select>
            </td>
        </tr>
    </tfoot>
</table>
<?php
echo $GLOBALS['template_factory']->render('layouts/base', array('content_for_layout' => ob_get_clean()));

page_close();