<?php

require_once('app/controllers/authenticated_controller.php');
require_once('app/models/rule_administration.php');
require_once('lib/classes/admission/AdmissionRule.class.php');

class Admission_RuleAdministrationController extends AuthenticatedController {

    /**
     * Here go actions that must be done before each page load.
     */
    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        $layout = $GLOBALS['template_factory']->open('layouts/base');
        $this->set_layout($layout);
        PageLayout::setTitle(_('Verwaltung von Anmelderegeln'));
        Navigation::activateItem('/admin/config/admissionrules');
        PageLayout::addSqueezePackage('dialogs');
    }

    /**
     * Show overview of available admission rules.
     */
    public function index_action() {
        DBManager::get()->exec("UPDATE `admissionrules` SET `deleteable`=0
            WHERE `ruletype` IN ('ConditionalAdmission', 'LimitedAdmission',
            'LockedAdmission', 'PasswordAdmission', 'TimedAdmission')");
        $this->ruleTypes = RuleAdministrationModel::getAdmissionRuleTypes();
    }

    /**
     * (De-)Activates the given rule type for system wide usage.
     * 
     * @param  String $ruleType the class name of the rule type to activate.
     */
    public function activate_action($ruleType, $status) {
        $stmt = DBManager::get()->prepare("UPDATE `admissionrules` SET `active`=? WHERE `ruletype`=?");
        $stmt->execute(array($status, $ruleType));
        $this->redirect($this->url_for('admission/ruleadministration'));
    }

    /**
     * Installs a new admission rule.
     */
    public function install_action() {
        CSRFProtection::verifyUnsafeRequest();
        try {
            if ($this->flash['upload_file']) {
                $uploadFile = $this->flash['upload_file'];
            } else {
                $uploadFile = $_FILES['upload_file']['tmp_name'];
            }
            $ruleAdmin = new RuleAdministrationModel();
            $ruleAdmin->install($uploadFile);
            $this->flash['success'] = _('Die Anmelderegel wurde erfolgreich installiert.');
            if (isset($uploadFile)) {
                unlink($uploadFile);
            }
            $this->redirect('admission/ruleadministration');
        } catch (Exception $e) {
            $this->flash['error'] = $e->getMessage();
            $this->redirect('admission/ruleadministration');
        }
    }

    /**
     * Deletes the given admission rule type from the system, including all
     * data belonging to it (especially saved values in DB!).
     */
    public function uninstall_action($ruleType) {
        if (Request::int('really')) {
            try {
                $ruleAdmin = new RuleAdministrationModel();
                $ruleAdmin->uninstall($ruleType);
                $this->flash['message'] = _('Die Anmelderegel wurde erfolgreich gelöscht.');
            } catch (AdmissionRuleInstallationException $e) {
                $this->flash['error'] = $e->getMessage();
            }
            $this->redirect($this->url_for('admission/ruleadministration'));
        }
        if (Request::int('cancel')) {
           $this->redirect($this->url_for('admission/ruleadministration'));
        }
    }

    public function download_action($ruleName) {
        $dirname = $GLOBALS['ABSOLUTE_PATH_STUDIP'].'admissionrules/'.
            strtolower($ruleName);
        $filename = $ruleName.'.zip';
        $filepath = get_config('TMP_PATH').'/'.$filename;

        create_zip_from_directory($dirname, $filepath);

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Length: '.filesize($filepath));
        header('Pragma: public');

        $this->render_nothing();

        readfile($filepath);
        unlink($filepath);
    }

    /**
     * Validate ticket (passed via request environment).
     * This method always checks Request::quoted('ticket').
     *
     * @throws InvalidArgumentException  if ticket is not valid
     */
    private function check_ticket() {
        if (!check_ticket(Request::option('ticket'))) {
            throw new InvalidArgumentException(_('Das Ticket für diese Aktion ist ungültig.'));
        }

    }

}

?>