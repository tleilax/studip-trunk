<?php

require_once('app/controllers/authenticated_controller.php');
require_once('lib/classes/admission/StudipCondition.class.php');
require_once('lib/classes/admission/ConditionField.class.php');

class Conditions_ConditionController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        $this->conditionFields = ConditionField::getAvailableConditionFields();
        if (Request::isXhr()) {
            $this->via_ajax = true;
            $this->set_layout(null);
        } else {
            $layout = $GLOBALS['template_factory']->open('layouts/base');
            $this->set_layout($layout);
            PageLayout::setTitle(_('Auswahlbedingungen'));
            Navigation::activateItem('/tools/coursesets');
        }
        PageLayout::addSqueezePackage('conditions');
    }

    public function configure_action($conditionId='') {
        if ($conditionId) {
            $this->condition = new StudipCondition($conditionId);
        }
    }

    public function add_action() {
        $condition = new StudipCondition();
        $fields = Request::getArray('field');
        $compareOps = Request::getArray('compare_operator');
        $values = Request::getArray('value');
        $data = array();
        for ($i=0 ; $i<sizeof($fields) ; $i++) {
            $current = $fields[$i];
            if ($this->conditionFields[$current]) {
                $field = new $current();
                $field->setCompareOperator($compareOps[$i]);
                $field->setValue($values[$i]);
                $condition->addField($field);
            }
        }
        $this->condition = $condition;
    }

    public function delete_action($conditionId) {
        $condition = new StudipCondition($conditionId);
        $condition->delete();
        $this->render_nothing();
    }

}

?>