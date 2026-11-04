<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Tests for who sees the custom home and who may go to the normal frontpage.
 *
 * @package     local_adminhome
 * @copyright   2026 by IDEF https://idef21.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_adminhome;

/**
 * Tests for {@see hook_callbacks}.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(hook_callbacks::class)]
final class hook_callbacks_test extends \advanced_testcase {
    /**
     * The plugin is on until the setting is switched off; a setting never saved counts as on.
     */
    public function test_is_enabled(): void {
        $this->resetAfterTest();

        unset_config('enabled', 'local_adminhome');
        $this->assertTrue(hook_callbacks::is_enabled());

        set_config('enabled', 0, 'local_adminhome');
        $this->assertFalse(hook_callbacks::is_enabled());
    }

    /**
     * Site admins see the custom home only while the "Site administrators" setting is on.
     */
    public function test_siteadmins_setting(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Never saved: on, as its default.
        unset_config('siteadmins', 'local_adminhome');
        $this->assertTrue(hook_callbacks::is_target_user());

        set_config('siteadmins', 1, 'local_adminhome');
        $this->assertTrue(hook_callbacks::is_target_user());

        set_config('siteadmins', 0, 'local_adminhome');
        $this->assertFalse(hook_callbacks::is_target_user());
    }

    /**
     * Users only see it when they have one of the chosen roles, in any context.
     */
    public function test_roles_setting(): void {
        global $DB;
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $student = $generator->create_user();
        $teacher = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        $generator->enrol_user($teacher->id, $course->id, 'editingteacher');
        $studentrole = $DB->get_field('role', 'id', ['shortname' => 'student']);

        // No roles chosen: nobody but the admins.
        $this->setUser($student);
        $this->assertFalse(hook_callbacks::is_target_user());

        // Only students chosen.
        set_config('roles', $studentrole, 'local_adminhome');
        $this->assertTrue(hook_callbacks::is_target_user());
        $this->setUser($teacher);
        $this->assertFalse(hook_callbacks::is_target_user());
    }

    /**
     * Choosing "Authenticated user" shows it to every logged-in user, even without any role assignment.
     */
    public function test_authenticated_user_role(): void {
        global $CFG;
        $this->resetAfterTest();

        $this->setUser($this->getDataGenerator()->create_user());
        $this->assertFalse(hook_callbacks::is_target_user());

        set_config('roles', $CFG->defaultuserroleid, 'local_adminhome');
        $this->assertTrue(hook_callbacks::is_target_user());
    }

    /**
     * Admins may always go to the normal frontpage; the rest only when the setting allows it.
     */
    public function test_can_see_frontpage(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $this->assertTrue(hook_callbacks::can_see_frontpage());

        $this->setUser($this->getDataGenerator()->create_user());
        $this->assertFalse(hook_callbacks::can_see_frontpage());

        set_config('showfrontpage', 1, 'local_adminhome');
        $this->assertTrue(hook_callbacks::can_see_frontpage());
    }
}
