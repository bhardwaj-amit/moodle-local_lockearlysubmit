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
 * Event observer to lock early submissions after due date and unlock on extension.
 *
 * @package    local_lockearlysubmit
 * @author     Amit Bhardwaj (moodlebyamit@gmail.com)
 * @copyright  2025 Amit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lockearlysubmit;

use mod_assign\assign;

/**
 * Event observers for local_lockearlysubmit plugin.
 */
class observer {
    /**
     * Lock early submissions when assignment is viewed after the due date.
     *
     * @param \core\event\course_module_viewed $event
     */
    public static function handle_view(\core\event\course_module_viewed $event) {
        global $DB, $USER;

        // We only care about 'assign' modules.
        if ($event->objecttable !== 'assign') {
            return;
        }

        // Get course module and assignment instance.
        $cm = get_coursemodule_from_id('assign', $event->contextinstanceid);
        if (!$cm) {
            return;
        }

        $context = \context_module::instance($cm->id);
        $assign = new \assign($context, null, null);
        $instance = $assign->get_instance();

        // Skip if due date hasn't passed or unlock the submission if due date extended.
        if ($instance->duedate == 0 || time() <= $instance->duedate) {
            // Unlock only for this user if previously locked.
            $flags = $assign->get_user_flags($USER->id, true);
            if ($flags && $flags->locked) {
                $flags->locked = 0;
                $assign->update_user_flags($flags);
            }
            return;
        }

        // Check if this user's submission is already processed (locked).
        $exists = $DB->record_exists('local_lockearlysubmit_log', [
            'assignid' => $instance->id,
            'userid' => $USER->id,
        ]);
        if ($exists) {
            return;
        }

        // Lock only this user's submission if submitted before due date and not already locked.
        $submission = $assign->get_user_submission($USER->id, false);
        if ($submission &&
            $submission->status === ASSIGN_SUBMISSION_STATUS_SUBMITTED &&
            $submission->timemodified <= $instance->duedate) {
            $flags = $assign->get_user_flags($USER->id, true);
            if ($flags && empty($flags->extensionduedate)) {
                $flags->locked = 1;
                $assign->update_user_flags($flags);
                // Log this lock action for this user and assignment.
                $DB->insert_record('local_lockearlysubmit_log', [
                    'assignid' => $instance->id,
                    'userid' => $USER->id,
                    'timeprocessed' => time(),
                ]);
            }
        }
    }

    /**
     * Unlock a student's submission if an extension is granted.
     *
     * @param \mod_assign\event\extension_granted $event
     */
    public static function handle_extension(\mod_assign\event\extension_granted $event) {
        global $DB;

        $cm = get_coursemodule_from_id('assign', $event->contextinstanceid);
        if (!$cm) {
            return;
        }

        $context = \context_module::instance($cm->id);
        $assign = new \assign($context, null, null);
        $userid = $event->relateduserid;

        // Check user flags for new extension time.
        $flags = $DB->get_record('assign_user_flags', [
            'assignment' => $assign->get_instance()->id,
            'userid' => $userid,
        ]);

        // If extension is in the future, unlock.
        if ($flags && $flags->extensionduedate > time()) {
            $submission = $assign->get_user_submission($userid, false);
            if ($submission && !empty($flags->locked)) {
                $flags->locked = 0;
                $assign->update_user_flags($flags);
            }
        }
    }
}
