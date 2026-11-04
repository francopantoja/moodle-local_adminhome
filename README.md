<img src="pix/icon.svg" alt="Eazy Home icon" width="96" height="96">

# Eazy Home #

Eazy Home replaces the site frontpage with a custom home for the users you choose.
It is made above all for administrators, who are not enrolled in every course and need a quick view of
the site as soon as they log in.

The plugin is transparent for the user: the page is titled with the site name and greets the user by
their first name.

## Features ##

* **Welcome** with the plugin icon and the first name of the user.
* **Site summary cards** (site administrators only), each one can be switched off in the settings:
    * 👥 Users: accounts that can log in (not deleted, not suspended).
    * 📚 Courses: visible courses.
    * 🟢 Online today: users who have accessed the site since midnight.
    * 😴 Never accessed: accounts that have never logged in.
* **Courses grouped by category**, with their subcategories, using the brand colour of the theme.
* **Who sees it**: site administrators and/or any roles you choose ("Authenticated user" for everybody).
* **Button to the normal frontpage**: always for administrators, optional for the rest.

## Settings ##

*Site administration > Plugins > Local plugins > Eazy Home*

| Setting | Default |
|---|---|
| Enable Eazy Home | Yes |
| Site administrators | Yes |
| Roles | None |
| Button to the normal frontpage | No |
| Card: Users / Courses / Online today / Never accessed | Yes |
| Categories to show | None (all the categories) |

## How it works ##

When one of the chosen users opens the site frontpage, the callback of the hook
`\core\hook\output\before_http_headers` redirects them to `/local/adminhome/index.php`.
Any other page of the site is not changed. Administrators can still see the normal frontpage with
`/?noadminhome=1`.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/adminhome

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

## Requirements ##

Moodle 4.5 or later.

## Privacy ##

Eazy Home does not store any personal data.

## License ##

2026 by IDEF https://idef21.com

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
