<?
# Lifter002: TODO - There's too much logic in this template, get rid of this when converting to trails app
# Lifter010: TODO

global $perm;

// Prepare curried function to check whether a field is editable
$lockCheck = function ($field, $return = true) use ($institute) {
    $check = LockRules::Check($institute['Institut_id'], $field);
    return $check ? $return : false;
};

// Indicates whether the current user is allowed to delete the institute
$may_delete = $i_view != 'new' && !($institute['number'] || $num_institute)
            && ($GLOBALS['perm']->have_perm('root')
                || ($GLOBALS['perm']->is_fak_admin() && get_config('INST_FAK_ADMIN_PERMS') == 'all'));

// Indicates whether the current user is allowed to change the faculty
$may_edit_faculty = $perm->is_fak_admin()
                  && !$lockCheck('fakultaets_id')
                  && ($perm->have_studip_perm('admin', $institute['fakultaets_id']) || $i_view == 'new');

// Read faculties if neccessary
if ($may_edit_faculty && !$num_institute) {
    if ($perm->have_perm('root')) {
        $query = "SELECT Institut_id, Name
                  FROM Institute
                  WHERE Institut_id = fakultaets_id AND fakultaets_id != ?
                  ORDER BY Name";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($institute['Institut_id'] ?: ''));
    } else {
        $query = "SELECT a.Institut_id, Name
                  FROM user_inst AS a
                  LEFT JOIN Institute USING (Institut_id)
                  WHERE user_id = ? AND inst_perms = 'admin' AND fakultaets_id = ?
                  ORDER BY Name";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($user->id, $institute['Institut_id'] ?: ''));
    }
    $faculties = $statement->fetchGrouped(PDO::FETCH_COLUMN);
}

?>
<form method="POST" name="edit" action="<?= UrlHelper::getLink() ?>">
    <?= CSRFProtection::tokenTag() ?>

    <table class="default admin-institute">
        <tbody>
            <tr class="<?= TextHelper::cycle('cycle_even', 'cycle_odd') ?>">
                <td><?= _('Name:') ?></td>
                <td>
                    <input type="text" <?= $lockCheck('name', 'readonly') ?> name="Name"
                           value="<?= htmlReady(Request::get('Name', $institute['Name'])) ?>">
                </td>
            </tr>

            <tr class="<?= TextHelper::cycle('cycle_even', 'cycle_odd') ?>">
                <td><?= _('Fakult�t:') ?></td>
                <td>
            <? if ($may_edit_faculty): ?>
                <? if ($num_institute): ?>
                    <small>
                        <?= _('Diese Einrichtung hat den Status einer Fakult�t.') ?><br>
                        <?= sprintf(_('Es wurden bereits %u andere Einrichtungen zugeordnet.'), $num_institute) ?>
                    </small>
                    <input type="hidden" name="Fakultaet" value="<?= $institute['Institut_id'] ?>">
                <? else: ?>
                    <select name="Fakultaet">
                    <? if ($perm->have_perm('root')): ?>
                        <option value="<?= $institute['Institut_id'] ?>"
                            <? if ($institute['fakultaets_id'] == Request::option('Fakultaet', $institute['Institut_id'])) echo 'selected'; ?>
                        >
                            <?= _('Diese Einrichtung hat den Status einer Fakult�t.') ?>
                        </option>
                    <? endif; ?>
                    <? foreach ($faculties as $id => $name): ?>
                        <option value="<?= $id ?>"
                                <? if ($id == Request::option('Fakultaet', $institute['fakultaets_id'])) echo 'selected'; ?>
                        >
                            <?= htmlReady($name) ?>
                        </option>
                    <? endforeach; ?>
                    </select>
                <? endif; ?>
            <? else: ?>
                    <?= htmlReady($institute['fak_name']) ?>
                    <input type="hidden" name="Fakultaet" value="<?= $institute['fakultaets_id'] ?>">
            <? endif; ?>
                </td>
            </tr>

            <tr class="<?= TextHelper::cycle('cycle_even', 'cycle_odd') ?>">
                <td><?= _('Bezeichnung:') ?></td>
                <td>
                <? if (!$lockCheck('type')): ?>
                    <select name="type">
                    <? foreach ($GLOBALS['INST_TYPE'] as $i => $inst_type): ?>
                        <option value="<?= $i ?>"
                                <? if (Request::int('type', $institute['type']) == $i) echo 'selected'; ?>
                        >
                            <?= htmlReady($inst_type['name']) ?>
                        </option>
                    <? endforeach; ?>
                <? else :?>
                    <?= htmlReady($GLOBALS['INST_TYPE'][$institute['type']]['name']) ?>
                    <input type="hidden" name="type" value="<?= (int)$institute['type'] ?>">
                <? endif;?>
                </td>
            </tr>

            <tr class="<?= TextHelper::cycle('cycle_even', 'cycle_odd') ?>">
                <td><?= _('Stra�e:') ?></td>
                <td>
                    <input type="text" <?= $lockCheck('strasse', 'readonly') ?> name="strasse"
                           value="<?= htmlReady(Request::get('strasse', $institute['Strasse'])) ?>">
                </td>
            </tr>
            <tr class="<?= TextHelper::cycle('cycle_even', 'cycle_odd') ?>">
                <td><?= _('Ort:') ?></td>
                <td>
                    <input type="text" <?= $lockCheck('plz', 'readonly') ?> name="plz"
                           value="<?= htmlReady(Request::get('plz', $institute['Plz'])) ?>">
                </td>
            </tr>
            <tr class="<?= TextHelper::cycle('cycle_even', 'cycle_odd') ?>">
                <td><?= _('Telefonnummer:') ?></td>
                <td>
                    <input type="tel" <?= $lockCheck('telefon', 'readonly') ?> name="telefon" maxlength=32
                           value="<?= htmlReady(Request::get('telefon', $institute['telefon'])) ?>">
                </td>
            </tr>
            <tr class="<?= TextHelper::cycle('cycle_even', 'cycle_odd') ?>">
                <td><?=_("Faxnummer:")?></td>
                <td>
                    <input type="tel" <?= $lockCheck('fax', 'readonly') ?> name="fax" maxlength=32
                           value="<?= htmlReady(Request::get('fax', $institute['fax'])) ?>">
                </td>
            </tr>
            <tr class="<?= TextHelper::cycle('cycle_even', 'cycle_odd') ?>">
                <td><?= _('E-Mail-Adresse:') ?></td>
                <td>
                    <input type="email" <?= $lockCheck('email', 'readonly') ?> name="email"
                           value="<?= htmlReady(Request::get('email', $institute['email'])) ?>">
                </td>
            </tr>
            <tr class="<?= TextHelper::cycle('cycle_even', 'cycle_odd') ?>">
                <td><?= _('Homepage:') ?></td>
                <td>
                    <input type="url" <?= $lockCheck('url', 'readonly') ?> name="home" 
                           value="<?= htmlReady(Request::get('home', $institute['url'])) ?>">
                </td>
            </tr>

        <? if (get_config('LITERATURE_ENABLE') && $institute['Institut_id'] == $institute['fakultaets_id']):
           // choose preferred lit plugin ?>
            <tr class="<?= TextHelper::cycle('cycle_even', 'cycle_odd') ?>">
                <td><?= _('Bevorzugter Bibliothekskatalog:') ?></td>
                <td>
                    <select name="lit_plugin_name">
                    <? foreach (StudipLitSearch::GetAvailablePlugins() as $name => $title): ?>
                        <option value="<?= $name ?>" 
                                <? if ($name == Request::get('lit_plugin_name', $institute['lit_plugin_name'])) echo 'selected'; ?> 
                        >
                            <?= htmlReady($title) ?>
                        </option>
                    <? endforeach; ?>
                    </select>
                </td>
            </tr>
        <? endif; ?>

        <? if ($GLOBALS['perm']->have_perm('root')):
           // Select lockrule to apply ?>
            <tr class="<?= TextHelper::cycle('cycle_even', 'cycle_odd') ?>">
                <td><?= _('Sperrebene') ?></td>
                <td>
                    <select name="lock_rule">
                        <option value="">&nbsp;</option>
                    <? foreach(LockRule::findAllByType('inst') as $rule): ?>
                        <option value="<?= $rule->getId() ?>"
                                <? if ($rule->getId() == Request::option('lock_rule', $institute['lock_rule'])) echo 'selected'; ?>
                        >
                            <?= htmlReady($rule->name) ?>
                        </option>
                    <? endforeach;?>
                    </select>
                </td>
            </tr>
        <? endif; ?>

        <? foreach ($datafields as $datafield): ?>
            <tr class="<?= TextHelper::cycle('cycle_even', 'cycle_odd') ?>">
                <td style="color: <?= $datafield['color'] ?>">
                    <?= htmlReady($datafield['title']) ?>
                </td>
                <td><?= $datafield['value'] ?></td>
            </tr>
        <? endforeach; ?>

        </tbody>
        <tfoot>
            <tr>
                <td class="table_footer" colspan="2" style="text-align: center;">
                <? if ($i_view != 'new' && isset($institute['Institut_id'])): ?>
                    <input type="hidden" name="i_id" value="<?= $institute['Institut_id'] ?>">
                    <?= Studip\Button::create(_('�bernehmen'), 'i_edit') ?>

                    <? if ($may_delete): ?>
                        <?= Studip\Button::create(_('L�schen'), 'i_trykill') ?>
                    <? endif; ?>
                <? else: ?>
                    <?= Studip\Button::create(_('Anlegen'), 'create') ?>
                <? endif; ?>
                    <input type="hidden" name="i_view" value="<?= $i_view == 'new' ? 'create' : $i_view  ?>">
                </td>
            </tr>
        </foot>
    </table>

</form>
