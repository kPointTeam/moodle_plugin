<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


require_once("$CFG->libdir/blocklib.php");

/**
 * List of features supported in kpoint module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function kpoint_supports($feature)
{
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;    // to display as Resource type
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Saves a new instance of the kpoint into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $kpoint An object from the form in mod_form.php
 * @param mod_kpoint_mod_form $mform
 * @return int The id of the newly inserted kpoint record
 */
function kpoint_add_instance(stdClass $data, mod_kpoint_mod_form $mform = null)
{
    global $DB;
    $now = time();
    $data->timecreated = $now;
    $data->timemodified = $now;
    $data->id = $DB->insert_record('kpoint', $data);

    return $data->id;
}

/**
 * Updates an instance of the kpoint in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $kpoint An object from the form in mod_form.php
 * @param mod_kpoint_mod_form $mform
 * @return boolean Success/Fail
 */
function kpoint_update_instance(stdClass $data, mod_kpoint_mod_form $mform = null)
{
    global $DB;

    $now = time();
    $data->timemodified = $now;
    $data->id = $data->instance;

    return $DB->update_record('kpoint', $data);
}

/**
 * Removes an instance of the kpoint activity from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function kpoint_delete_instance($id)
{
    global $DB;

    if (!$kp = $DB->get_record('kpoint', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('kpoint', array('id' => $kp->id));

    return true;
}

/**
 * trigger the course_module_viewed event.
 *
 * @param  stdClass $kpoint     kpoint object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function kpoint_view($kpoint, $course, $cm, $context)
{

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $kpoint->id
    );

    $event = \mod_kpoint\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('kpoint', $kpoint);
    $event->trigger();

}

/**
 * trigger the course_module_viewed event.
 *
 * @param  stdClass $kpoint     kpoint object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
  */
function kpoint_videocompleted($kpoint, $course, $cm, $context)
{

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $kpoint->id
    );

    $event = \mod_kpoint\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('kpoint', $kpoint);
    $event->trigger();
}

/**
 * function to extract video_id from externalurl
 *
 * @param type $videoUrl
 * @return type
 */
function extract_video_id($videoUrl)
{
    $arrVideo = explode('/', $videoUrl);
    $videoId  = $arrVideo[5];
    unset($arrVideo);

    if (strpos($videoId, '?') !== false) {
        $videoId = substr($videoId, 0, strpos($videoId, '?'));
    }
    return $videoId;
}
