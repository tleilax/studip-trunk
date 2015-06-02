<?php
/**
 * StudyAreasWizardStep.php
 * Course wizard step for assigning study areas.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @copyright   2015 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class StudyAreasWizardStep implements CourseWizardStep
{
    /**
     * Returns the Flexi template for entering the necessary values
     * for this step.
     *
     * @param Array $values Pre-set values
     * @return String a Flexi template for getting needed data.
     */
    public function getStepTemplate($values)
    {
        $tpl = $GLOBALS['template_factory']->open('coursewizard/studyareas/index');
        if ($values['studyareas'])
        {
            $tree = $this->buildPartialSemTree(StudipStudyArea::backwards(StudipStudyArea::findMany($values['studyareas'])), false);
            $values['studyareas'] = $tree;
        } else {
            $values['studyareas'] = array();
        }
        $tpl->set_attribute('values', $values);
        // First tree level is always shown.
        $tree = StudipStudyArea::findByParent(StudipStudyArea::ROOT);
        $tpl->set_attribute('tree', $tree);
        return $tpl->render();
    }

    /**
     * Validates if given values are sufficient for completing the current
     * course wizard step and switch to another one. If not, all errors are
     * collected and shown via PageLayout::postMessage.
     *
     * @param mixed $values Array of stored values
     * @return bool Everything ok?
     */
    public function validate($values)
    {
        $ok = true;
        $errors = array();
        if (!$values['studyareas']) {
            $ok = false;
            $errors[] = _('Die Veranstaltung muss mindestens einem Studienbereich zugeordnet sein.');
        }
        if ($errors) {
            PageLayout::postMessage(MessageBox::error(
                _('Bitte beheben Sie erst folgende Fehler, bevor Sie fortfahren:'), $errors));
        }
        return $ok;
    }

    /**
     * Stores the given values to the given course.
     *
     * @param Course $course the course to store values for
     * @param Array $values values to set
     * @return Course The course object with updated values.
     */
    public function storeValues($course, $values)
    {
        $course->study_areas = SimpleORMapCollection::createFromArray(StudipStudyAreas::findMany($values['studyareas']));
        if ($course->store()) {
            return $course;
        } else {
            return false;
        }
    }

    /**
     * Checks if the current step needs to be executed according
     * to already given values. A good example are study areas which
     * are only needed for certain sem_classes.
     *
     * @param Array $values values specified from previous steps
     * @return bool Is the current step required for a new course?
     */
    public function isRequired($values)
    {
        $coursetype = 1;
        foreach ($values as $class)
        {
            if ($class['coursetype'])
            {
                $coursetype = $class['coursetype'];
                break;
            }
        }
        $category = SeminarCategories::GetByTypeId($coursetype);
        return $category->bereiche;
    }

    public function getSemTreeLevel($parentId)
    {
        $level = array();
        $children = StudipStudyArea::findByParent($parentId);
        foreach ($children as $c) {
            $level[] = array(
                'id' => $c->sem_tree_id,
                'name' => studip_utf8encode($c->getName()),
                'has_children' => $c->hasChildren(),
                'parent' => $parentId,
                'assignable' => $c->isAssignable()
            );
        }
        return json_encode($level);
    }

    public function searchSemTree($searchterm)
    {
        $result = array();
        $search = StudipStudyArea::search($searchterm);
        $root = StudipStudyArea::backwards($search);
        $result = $this->buildPartialSemTree($root);
        return json_encode($result);
    }

    public function getAncestorTree($id)
    {
        $result = array();
        $node = StudipStudyArea::find($id);
        $root = StudipStudyArea::backwards(array($node));
        $result = $this->buildPartialSemTree($root);
        return json_encode($result);
    }

    private function buildPartialSemTree($node, $utf = true) {
        $children = array();
        foreach ($node->required_children as $c)
        {
            $data = array(
                'id' => $c->sem_tree_id,
                'name' => $utf ? studip_utf8encode($c->name) : $c->name,
                'has_children' => $c->hasChildren(),
                'parent' => $node->sem_tree_id,
                'assignable' => $c->isAssignable(),
                'children' => $this->buildPartialSemTree($c, $utf)
            );
            $children[] = $data;
        }
        return $children;
    }

}