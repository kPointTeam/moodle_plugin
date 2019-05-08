<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/mod/kpoint/lib.php');

$courseModuleID = $_GET['courseModuleID'];
$cm             = get_coursemodule_from_id('kpoint', $courseModuleID, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('kpoint', array('id' => $cm->instance), '*', MUST_EXIST);
$modulecontext  = context_module::instance($cm->id);

$params = array(
        'context' => $modulecontext,
        'objectid' => $moduleinstance->id
);

//trigger video start event
$event = \mod_kpoint\event\course_module_started::create($params);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('kpoint', $moduleinstance);
$event->trigger();
