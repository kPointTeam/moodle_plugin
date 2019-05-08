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
 * Defines the version of newsletter
 *
 * @package    mod_kpoint
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version      = 2019043000;        // If version == 0 then module will not be installed
$plugin->requires     = 2017111300;        // Requires this Moodle version
$plugin->maturity     = MATURITY_STABLE;   // Maturity
$plugin->component    = 'mod_kpoint';  // To check on upgrade, that module sits in correct place
$plugin->dependencies = array(
    'repository_kpoint' => ANY_VERSION,   // The Foo activity must be present (any version).
);
