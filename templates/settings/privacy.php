<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center">
    <tr>
        <td class="blank" width="100%" colspan="2" align="center">
            <p class="info">
                <b><?= _("Hier k�nnen Sie Ihre Sichtbarkeit im System einstellen."); ?></b>
            </p>
            <form method="post" action="<?= URLHelper::getLink('edit_about.php', array('cmd' => 'change_global_visibility', 'studipticket' => get_ticket(), 'username' => Request::get('username'))); ?>">
                <table width="70%" align="center"cellpadding="8" cellspacing="0" border="0">
                    <tr>
                        <th width="50%"><?= _("Option"); ?></th>
                        <th width="50%"><?= _("Auswahl"); ?></th>
                    </tr>
                    <tr>
                        <td colspan="2" class="steelgraulight" style="border-bottom: 1px dotted black; border-top: 1px dotted black;" align="center">
                            <b><?= _('globale Einstellungen'); ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td width="50%" align="right" class="blank" style="border-bottom:1px dotted black;" width="66%">
                            <font size="-1"><?print _("globale Sichtbarkeit");?></font><br>
                            <br><div align="left"><font size="-1">
                            <?= _("Sie k�nnen w�hlen, ob Sie f�r andere NutzerInnen sichtbar sein und alle Kommunikationsfunktionen von Stud.IP nutzen k�nnen wollen, oder ob Sie unsichtbar sein m�chten und dann nur eingeschr�nkte Kommunikationsfunktionen nutzen k�nnen.");?>
                            </font></div>
                        </td>
                        <td width="50%" class="<?=TextHelper::cycle('steel1', 'steelgraulight')?>" width="34%">
                            <?php
                            if ($global_visibility != 'always' && $global_visibility != 'never' &&
                                ($user_perm != 'dozent' || !get_config('DOZENT_ALWAYS_VISIBLE'))) {
                                // only show selection if visibility can be changed
                                ?>
                            <select name="global_visibility">
                            <?php
                                if (count($user_domains)) {
                                    printf ("<option %s value=\"global\">"._("sichtbar f�r alle Nutzer")."</option>", $global_visibility=='global' ? 'selected="selected"' : '');
                                    $visible_text = _('sichtbar f�r eigene Nutzerdom�ne');
                                } else {
                                    $visible_text = _('sichtbar');
                                }
                                printf ("<option %s value=\"yes\">".$visible_text."</option>", ($global_visibility=='yes' || ($global_visibility=='unknown' && get_config('USER_VISIBILITY_UNKNOWN'))) ? 'selected="selected"' : '');
                                printf ("<option %s value=\"no\">"._("unsichtbar")."</option>", ($global_visibility=='no' || ($global_visibility=='unknown' && !get_config('USER_VISIBILITY_UNKNOWN'))) ? 'selected="selected"' : '');
                            ?>
                            </select>
                            <?php
                            } else {
                                if ($global_visibility == 'never') {
                                    echo "<i>"._('Ihre Kennung wurde von einem Administrator unsichtbar geschaltet.')."</i>";
                                } else if ($user_perm == 'dozent' && get_config('DOZENT_ALWAYS_VISIBLE')) {
                                    echo "<i>"._('Sie haben Dozentenrechte und sind daher immer global sichtbar.')."</i>";
                                } else {
                                    echo "<i>"._('Sie sind immer global sichtbar.')."</i>";
                                }
                                echo '<input type="hidden" name="global_visibility" value="'.$global_visibility.'">';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                    if (($global_visibility == 'yes' || $global_visibility == 'global' ||
                        ($global_visibility == 'unknown' && get_config('USER_VISIBILITY_UNKNOWN')) ||
                        ($user_perm == 'dozent' && get_config('DOZENT_ALWAYS_VISIBLE'))) &&
                        (!$NOT_HIDEABLE_FIELDS[$user_perm]['online'] ||
                        !$NOT_HIDEABLE_FIELDS[$user_perm]['chat'] ||
                        !$NOT_HIDEABLE_FIELDS[$user_perm]['search'] ||
                        !$NOT_HIDEABLE_FIELDS[$user_perm]['email'])) {
                    ?>
                    <tr>
                        <td align="right" class="blank" style="border-bottom:1px dotted black;">
                            <font size="-1"><?print _("erweiterte Einstellungen");?></font><br>
                            <br>
                            <div align="left">
                            <font size="-1">
                            <?= _("Stellen Sie hier ein, in welchen Bereichen des Systems Sie erscheinen wollen."); ?>
                            <?php
                            if (!$NOT_HIDEABLE_FIELDS[$user_perm]['email']) {
                                echo '<br>';
                                echo _("Wenn Sie hier Ihre E-Mail-Adresse verstecken, wird stattdessen die E-Mailadresse Ihrer (Standard-)Einrichtung angezeigt.");
                            }
                            ?>
                            </font>
                            </div>
                        </td>
                        <td class="<?=TextHelper::cycle('steel1', 'steelgraulight')?>">
                            <?php if (!$NOT_HIDEABLE_FIELDS[$user_perm]['online']) {?>
                            <input type="checkbox" name="online"<?= $online_visibility ? ' checked="checked"' : '' ?>>
                            <?= _('sichtbar in "Wer ist online"'); ?>
                            <br>
                            <?php } ?>
                            <?php if (!$NOT_HIDEABLE_FIELDS[$user_perm]['chat'] && get_config('CHAT_ENABLE')) {?>
                            <input type="checkbox" name="chat"<?= $chat_visibility ? ' checked="checked"' : '' ?>>
                            <?= _('eigener Chatraum sichtbar'); ?>
                            <br>
                            <?php } ?>
                            <?php if (!$NOT_HIDEABLE_FIELDS[$user_perm]['search']) {?>
                            <input type="checkbox" name="search"<?= $search_visibility ? ' checked="checked"' : '' ?>>
                            <?= _('auffindbar �ber die Personensuche'); ?>
                            <br>
                            <?php }�?>
                            <?php if (!$NOT_HIDEABLE_FIELDS[$user_perm]['email']) {?>
                            <input type="checkbox" name="email"<?= $email_visibility ? ' checked="checked"' : '' ?>>
                            <?= _('eigene E-Mail-Adresse sichtbar'); ?>
                            <br>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td class="<?=TextHelper::cycle('steel1', 'steelgraulight')?>" colspan="2">
                            <input type="hidden" name="view" value="privacy">
                            <?= makeButton('uebernehmen', 'input', _('�nderungen speichern'), 'change_global_visibility'); ?>
                        </td>
                    </tr>
                </table>
            </form>
            <br/>
            <form method="post" action="<?= URLHelper::getLink('edit_about.php', array('cmd' => 'change_homepage_visibility', 'studipticket' => get_ticket(), 'username' => Request::get('username'))); ?>">
                <table width="70%" align="center"cellpadding="8" cellspacing="0" border="0">
                    <tr>
                        <td colspan="<?= $user_domains ? 6 : 5; ?>" class="steelgraulight" style="border-bottom: 1px dotted black; border-top: 1px dotted black;" align="center">
                            <b><?= _('eigenes Profil'); ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="steel1" colspan="<?= $user_domains ? 3 : 2; ?>">
                            <?= _('neu hinzugef�gte Profil-Elemente sind standardm��ig sichtbar f�r'); ?>
                            <select name="default_homepage_visibility">
                                <option value="">-- <?= _("bitte w�hlen"); ?> --</option>
                                <option value="<?= VISIBILITY_ME; ?>"<?php echo ($default_homepage_visibility == VISIBILITY_ME) ? ' selected="selected"' : '' ?>><?= _("nur mich selbst") ?></option>
                                <option value="<?= VISIBILITY_BUDDIES; ?>"<?php echo ($default_homepage_visibility == VISIBILITY_BUDDIES) ? ' selected="selected"' : '' ?>><?= _("Buddies") ?></option>
                                <?php if ($user_domains) { ?>
                                <option value="<?= VISIBILITY_DOMAIN; ?>"<?php echo ($default_homepage_visibility == VISIBILITY_DOMAIN) ? ' selected="selected"' : '' ?>><?= _("meine Nutzerdom�ne") ?></option>
                                <?php } ?>
                                <option value="<?= VISIBILITY_STUDIP; ?>"<?php echo ($default_homepage_visibility == VISIBILITY_STUDIP) ? ' selected="selected"' : '' ?>><?= _("Stud.IP-intern") ?></option>
                                <option value="<?= VISIBILITY_EXTERN; ?>"<?php echo ($default_homepage_visibility == VISIBILITY_EXTERN) ? ' selected="selected"' : '' ?>><?= _("externe Seiten") ?></option>
                            </select>
                        </td>
                        <td class="steel1" colspan="3">
                            <?= makeButton('uebernehmen', 'input', _('�nderungen speichern'), 'set_default_homepage_visibility'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="steel1" colspan="<?= $user_domains ? 3 : 2; ?>">
                            <?= _('alle Sichtbarkeiten setzen auf'); ?>
                            <select name="all_homepage_visibility">
                                <option value="">-- <?= _("bitte w�hlen"); ?> --</option>
                                <option value="<?= VISIBILITY_ME; ?>"><?= _("nur mich selbst") ?></option>
                                <option value="<?= VISIBILITY_BUDDIES; ?>"><?= _("Buddies") ?></option>
                                <?php if ($user_domains) { ?>
                                <option value="<?= VISIBILITY_DOMAIN; ?>"><?= _("meine Nutzerdom�ne") ?></option>
                                <?php } ?>
                                <option value="<?= VISIBILITY_STUDIP; ?>"><?= _("Stud.IP-intern") ?></option>
                                <option value="<?= VISIBILITY_EXTERN; ?>"><?= _("externe Seiten") ?></option>
                            </select>
                        </td>
                        <td class="steel1" colspan="3">
                            <?= makeButton('uebernehmen', 'input', _('�nderungen speichern'), 'set_all_homepage_visibility'); ?>
                        </td>
                    </tr>
                    <tr>
                        <th width="'40%'"><?= _('Profil-Element'); ?></th>
                        <th colspan="<?= $user_domains ? 5 : 4; ?>" align="center"><?= _('sichtbar f�r'); ?></th>
                    </tr>
                    <tr class="steelgraulight">
                        <td width="40%">&nbsp;</td>
                        <td align="center" width="<?= $user_domains ? '12%' : '15%'; ?>"><i><?= _('nur mich selbst'); ?></i></td>
                        <td align="center" width="<?= $user_domains ? '12%' : '15%'; ?>"><i><?= _('Buddies'); ?></i></td>
                        <?php if ($user_domains) { ?>
                        <td align="center" width="12%"><i><?= _('Nutzerdom�ne'); ?></i></td>
                        <?php } ?>
                        <td align="center" width="<?= $user_domains ? '12%' : '15%'; ?>"><i><?= _('Stud.IP-intern'); ?></i></td>
                        <td align="center" width="<?= $user_domains ? '12%' : '15%'; ?>"><i><?= _('externe Seiten'); ?></i></td>
                    </tr>
                    <?php foreach ($homepage_elements as $category => $elements) { ?>
                    <tr class="blue_gradient">
                        <td colspan="<?= $user_domains ? 6 : 5; ?>">
                            <?= $category; ?>
                        </td>
                    </tr>
                    <?php foreach ($elements as $key => $element) { ?>
                    <tr class="<?=TextHelper::cycle('steelgraulight', 'steel1')?>">
                        <td><?= $element['name']; ?></td>
                        <td align="center">
                            <input type="radio" name="<?= $key; ?>" value="<?= VISIBILITY_ME; ?>"<?= ($element['visibility'] == VISIBILITY_ME) ? ' checked="checked"' : ''; ?>>
                        </td>
                        <td align="center">
                            <input type="radio" name="<?= $key; ?>" value="<?= VISIBILITY_BUDDIES; ?>"<?= ($element['visibility'] == VISIBILITY_BUDDIES) ? ' checked="checked"' : ''; ?>>
                        </td>
                        <?php if ($user_domains) { ?>
                        <td align="center">
                            <input type="radio" name="<?= $key; ?>" value="<?= VISIBILITY_DOMAIN; ?>"<?= ($element['visibility'] == VISIBILITY_DOMAIN) ? ' checked="checked"' : ''; ?>>
                        </td>
                        <?php } ?>
                        <td align="center">
                            <input type="radio" name="<?= $key; ?>" value="<?= VISIBILITY_STUDIP; ?>"<?= ($element['visibility'] == VISIBILITY_STUDIP) ? ' checked="checked"' : ''; ?>>
                        </td>
                        <td align="center">
                            <input type="radio" name="<?= $key; ?>" value="<?= VISIBILITY_EXTERN; ?>"<?= ($element['visibility'] == VISIBILITY_EXTERN) ? ' checked="checked"' : ''; ?>>
                        </td>
                    </tr>
                    <?php
                            }
                        }
                    ?>
                    <tr class="<?=TextHelper::cycle('steelgraulight', 'steel1')?>">
                        <td colspan="<?= $user_domains ? 6 : 5; ?>">
                            <input type="hidden" name="view" value="privacy">
                            <?= makeButton('uebernehmen', 'input', _('�nderungen speichern'), 'change_homepage_visibility'); ?>
                        </td>
                    </tr>
                </table>
            </form>
            <br><br>
        </td>
    </tr>
</table>
