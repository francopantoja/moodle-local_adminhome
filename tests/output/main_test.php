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
 * Tests for the data of the custom home: welcome, summary cards and categories.
 *
 * @package     local_adminhome
 * @copyright   2026 by IDEF https://idef21.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_adminhome\output;

/**
 * Tests for {@see main}.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(main::class)]
final class main_test extends \advanced_testcase {
    /**
     * Exports the data of the custom home for the current user.
     *
     * @return array
     */
    protected function export(): array {
        global $PAGE;
        return (new main())->export_for_template($PAGE->get_renderer('core'));
    }

    /**
     * Returns the value of each card, keyed by its label.
     *
     * @param array $data Exported data.
     * @return array
     */
    protected function card_values(array $data): array {
        return array_column($data['cards'], 'value', 'label');
    }

    /**
     * The welcome uses the first name of the user.
     */
    public function test_welcome(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user(['firstname' => 'Ana']));

        $this->assertSame('Welcome, Ana!', $this->export()['welcome']);
    }

    /**
     * The four cards count the right users and courses.
     */
    public function test_card_values(): void {
        global $DB;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

        // The admin plus two active users, one of them online today; one suspended user that is not counted.
        $generator->create_user();
        $online = $generator->create_user();
        $DB->set_field('user', 'lastaccess', time(), ['id' => $online->id]);
        $generator->create_user(['suspended' => 1]);

        // Two visible courses and a hidden one.
        $generator->create_course();
        $generator->create_course();
        $generator->create_course(['visible' => 0]);

        $this->setAdminUser();
        $values = $this->card_values($this->export());

        $this->assertSame('3', $values['Users']);
        $this->assertSame('2', $values['Courses']);
        $this->assertSame('1', $values['Online today']);
        $this->assertSame('2', $values['Never accessed']);
    }

    /**
     * A card switched off in the settings is not shown; a setting never saved counts as on.
     */
    public function test_card_switched_off(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $this->assertCount(4, $this->export()['cards']);

        set_config('showcard_courses', 0, 'local_adminhome');
        $values = $this->card_values($this->export());
        $this->assertCount(3, $values);
        $this->assertArrayNotHasKey('Courses', $values);
    }

    /**
     * Users who are not site admins do not see the site summary cards.
     */
    public function test_no_cards_for_users(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $data = $this->export();
        $this->assertFalse($data['hascards']);
        $this->assertSame([], $data['cards']);
    }

    /**
     * Only the chosen categories are shown, a chosen subcategory is not repeated and empty ones are left out.
     */
    public function test_categories(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

        $cata = $generator->create_category(['name' => 'Cat A']);
        $subcat = $generator->create_category(['name' => 'Sub A', 'parent' => $cata->id]);
        $catb = $generator->create_category(['name' => 'Cat B']);
        $generator->create_category(['name' => 'Empty']);
        $generator->create_course(['category' => $cata->id]);
        $generator->create_course(['category' => $subcat->id]);
        $generator->create_course(['category' => $catb->id]);
        $this->setAdminUser();

        // None chosen: every top category with courses, the subcategory inside its parent.
        $categories = $this->export()['categories'];
        $this->assertSame(['Cat A' => ['Sub A' => []], 'Cat B' => []], $this->tree($categories));
        // The course count includes the subcategories.
        $this->assertSame(2, $categories[0]['count']);

        // Cat A and its subcategory chosen: Cat A once, with the subcategory inside.
        set_config('categories', $cata->id . ',' . $subcat->id, 'local_adminhome');
        $this->assertSame(['Cat A' => ['Sub A' => []]], $this->tree($this->export()['categories']));
    }

    /**
     * Main categories start expanded; subcategories only when the setting says so (never saved: collapsed).
     */
    public function test_expand_categories(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

        $cat = $generator->create_category();
        $subcat = $generator->create_category(['parent' => $cat->id]);
        $generator->create_course(['category' => $subcat->id]);
        set_config('categories', $cat->id, 'local_adminhome');
        $this->setAdminUser();

        $main = $this->export()['categories'][0];
        $this->assertTrue($main['open']);
        $this->assertFalse($main['children'][0]['open']);

        set_config('expandcategories', 'all', 'local_adminhome');
        $main = $this->export()['categories'][0];
        $this->assertTrue($main['open']);
        $this->assertTrue($main['children'][0]['open']);
    }

    /**
     * Returns the category tree as nested arrays of names.
     *
     * @param array $nodes Category nodes.
     * @return array
     */
    protected function tree(array $nodes): array {
        $tree = [];
        foreach ($nodes as $node) {
            $tree[$node['name']] = $this->tree($node['children']);
        }
        return $tree;
    }
}
