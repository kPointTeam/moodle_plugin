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
 * @package    mod_kpoint
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Dongsheng Cai <dongsheng@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_chat_activity_task
 */
class backup_kpoint_activity_structure_step extends backup_activity_structure_step
{
    protected function define_structure()
    {

        // Define each element separated.
        $bkupKpoint = new backup_nested_element('kpoint', array('id'), array(
            'name', 'externalurl', 'timecreated', 'timemodified', 'embedcode','description'));

        // Define sources.
        $bkupKpoint->set_source_table('kpoint', array('id' => backup::VAR_ACTIVITYID));

        // Annotate the file areas in chat module.
        //$bkupKpoint->annotate_files('mod_kpoint', 'intro', null); // The chat_intro area doesn't use itemid.

        // Return the root element (chat), wrapped into standard activity structure.
        return $this->prepare_activity_structure($bkupKpoint);
    }
}
