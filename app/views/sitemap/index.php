<table class="default">
    <tr>
        <th width="50%"><?= _('Hauptnavigation') ?></th>
        <th width="50%"><?= _('Zusatznavigation') ?></th>
    </tr>
    <tr class="steel1">
        <td valign="top">
            <?= $this->render_partial('sitemap/navigation',
                    array('navigation' => $navigation, 'needs_image' => true, 'style' => 'bold')) ?>
        </td>
        <td valign="top">
            <?= $this->render_partial('sitemap/navigation',
                    array('navigation' => $quicklinks, 'needs_image' => false, 'style' => 'bold')) ?>
        </td>
    </tr>
</table>

<?
$infobox_content = array(
    array(
        'kategorie' => _('Hinweise:'),
        'eintrag'   => array(
            array(
                'icon' => 'info.gif',
                'text' => _('Auf dieser Seite finden Sie eine �bersicht �ber alle verf�gbaren Seiten.')
            )
        )
    )
);

$infobox = array('picture' => 'infobox/administration.jpg', 'content' => $infobox_content);
?>
