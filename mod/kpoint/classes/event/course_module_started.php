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

/**
 * The mod_kpoint course_module_started event.
 *
 * @package    mod_kpoint
 */

namespace mod_kpoint\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_kpoint course module started event class.
 *
 * @package    mod_kpoint
 */
class course_module_started extends \core\event\base
{
    /**
     * Create instance of event.
     *
     * @param \stdClass $kpoint
     * @param \context_module $context
     * @return course_module_started
     */

    /**
     * Init method.
     *
     * @return void
     */
    //global $USER;
    protected function init()
    {
        $this->data['crud']        = 'r';
        $this->data['edulevel']    = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'kpoint';
    }
    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description()
    {
        global $DB;
        $user         = $DB->get_record('user', array('id' => ($this->userid)), '*', MUST_EXIST);
        $coursemodule = $DB->get_record('course_modules', array('id' => ($this->contextinstanceid)), 'course,module', MUST_EXIST);
        $course       = $DB->get_record('course', array('id' => ($coursemodule->course)), 'fullname', MUST_EXIST);
        $module       = $DB->get_record('modules', array('id' => ($coursemodule->module)), 'name', MUST_EXIST);
        $kpoint       = $DB->get_record('kpoint', array('id' => ($this->objectid)), 'name', MUST_EXIST);
        return "The user '{$user->username}' (user id->{$this->userid}) started watching the '{$this->objecttable}' video named '{$kpoint->name}'(video id->{$this->objectid}) of "
        . "course '{$course->fullname}'(course id->{$coursemodule->course}).";
    }

    public static function get_objectid_mapping()
    {
        return array('db' => 'kpoint', 'restore' => 'kpoint');
    }
}
