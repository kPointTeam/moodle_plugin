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
 * The mod_kpoint kpoint_log event.
 *
 * @package    mod_kpoint
 */

namespace mod_kpoint\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_kpoint kpoint_log event class.
 *
 * @package    mod_kpoint
 */
class kpoint_log extends \core\event\base
{
    
    /**
     * Create instance of event.
     * @param \stdClass $kpoint
     * @param \context_module $context
     * @return kpoint_log
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
        return "Error occured in '{$this->data['objecttable']}' :  '{$this->data['other']}'.";
    }

    public static function get_objectid_mapping()
    {
        return array('db' => 'kpoint', 'restore' => 'kpoint');
    }
}
