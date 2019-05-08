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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/mod/kpoint/lib.php');

$id = required_param('id', PARAM_INT);

if (!$course = $DB->get_record("course", array("id" => $id))) {
    print_error('invalidcourseid');
}
require_login($course);

$PAGE->set_url('/mod/kpoint/index.php');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mod_kpoint'), 2);
echo "Content goes here";
echo $OUTPUT->footer();
