=== Namaste! LMS ===
Contributors: prasunsen
Tags: LMS, learning, courses, lessons, ILE, wpmu, multisite
Requires at least: 3.3
Tested up to: 4.2
Stable tag: trunk
License: GPL2

Learning management system for Wordpress. Support unlimited number of courses, lessons, assignments, students etc. 

== License ==

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

== Description ==

Namaste! LMS is a learning management system for Wordpress. Supports unlimited number of courses, lessons, assignments, students etc. You can create various rules for course and lesson access and completeness based on assignment completion, test results, or manual admin approval.

Namaste! lets you assign different user roles to work with the LMS and other roles who will manage it.

Students can earn certificates upon completing courses. 

For quick tour and more detailed help go to <a href="http://namaste-lms.org" target="_blank">namaste-lms.org</a>.

== Installation ==

1. Unzip the contents and upload the entire `namaste` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to "Namaste LMS" in your menu and manage the plugin

== Frequently Asked Questions ==

= Do you have any documentation? =

Yes, check the Help page under the Namaste! LMS menu (once the plugin is intalled). You will find links to the online documentation, list of all shortcodes, and more.

= Can I rename the URL slugs from "namaste-course" and "namaste-lesson" to something else? =

Yes, you can set your custom URL slugs in the Namaste Settings page

== Screenshots ==

1. Create/edit course. Courses are custom post type and support rich formatting, can be categorized etc. The course page itself is just a presentation page about what's in the course.
2. Create/edit lesson. Lessons are custom post type and support rich formatting and all kind of categorization. There are various rules about lesson access and lesson completion.
3. Manage assignments/homeworks
4. Student enrollments in a course
5. Progress of a given student in the course
6. Assignments for a lesson
7. Submitting a solution for assignment

== Changelog ==

= Version 1.6.1 =
- Made some improvements to notes / feedback on assignments and their navigation

= Version 1.6 =
- Added [namaste-earned-certificates] shortcode to display certificates earned by the user on a given course. Can be used on course page or elsewhere by passing course_id.
- Added Sandbox mode for testing paypal payments
- Added filter to make sure unenrolled user does not see comments on a lesson
- Added page that shows all users who earned a certificate along with a link to see / print their certificate
- Added option to show lessons in blog and archive pages
- Added information about the "mark completed" button in the lesson to-do popup box
- Added shortcode [namaste-course-link] that creates a link to the course a lesson belongs to
- Switched the lesson order to ID (order of creating) rather than title based in couple of places
- Added custom column with LMS points in the "Users" page of your administration. The column appears if you choose to use points system. There is also a link to cleanup the points earned of every user.
- Made start / end dates of course and lesson use the timezone settings. Added also start / end times.
- You can now set desired paper size and orientation when creating certificates with the PDF bridge. Requires PDF Bridge version 0.7 or newer.

= Version 1.5 =
- Added elaborated access options for the roles that can manage the LMS
- Certificates will now be earned each time when the course is completed even if the same certificate was earned previously
- Added option to change the course and lesson URL slugs from "namaste-course" and "namaste-lesson" to your custom slugs
- Added "custom currency" option in the payment settings page
- Submitting solutions to assignments on a lesson is not allowed if the lesson access requirements are not satisfied.
- Added option to un-enroll student from a course
- Now you can generate PDF certificates using the free PDF bridge plugin
- Added variables for course description and certificate ID in the certificates. Improved the certificate displaying.
- Added option to redirect to specific URL after Paypal payment
- Added option to include "enroll" shortcodes outside of course page
- Improved the redirect URL after course enrollment (now defaults to the course page)
- Added [namaste-mycertificates] shortcode and made the certificate display accessible outside of wp-admin

= Version 1.4 =
- Advanced users only: You can now create your own versions of all Namaste views for high-level of customization
- Added shortcode to display functional "lesson assignments" page 
- Added shhortcode to display student grade on given course (see Gradebook)
- Links are generated to exams/tests in [namaste-todo] shortcode
- Allow multiple grades to be used in the "required quiz" for completing a lesson
- Fixed problem with listing pages on your homepage when Namaste is activated
- Course access / prerequisites: you can require other courses to be completed before student can enroll in a course
- Fixed problem with [namaste-enroll] shortcode and the new course pre-requisites
- Added setting to show / hide courses in blog and home pages (default is off)
- Added filers for course access to allow other plugins to add conditions
- Added shortcode to publish a button that lets student mark lesson as completed themselves
- Added shortcode to display the first lesson in a course
- The [lesson-todo] shortcode will now automatically recognize if you have published the [namaste-assignments] shortcode for this lesson and will display the front-end link to submit a solution for the to-do assignments. This way you can entirely hide the back-end if you wish.

= Version 1.3 =
- The URL slugs "namaste-lesson" and "namaste-course" are now translate-able so you can use your own URL rewrites
- Links to assignments are now generated in [namaste-todo] shortcode
- "Accept file upload as solution" is finally implemented
- fixed problems with PDFs and other large files when uploading solution to assignment
- Added arguments that let you control the order of lessons in [namaste-course-lessons] shortcode
- Fixed possible bug with passing course ID through Paypal
- Improved the Paypal error logging and added a "View errorlog" link on the main payment settings page (link visible only if there are errors)
- Added [namaste-next-lesson] shortcode to display a link to the next lesson in a course (please see the Help page inside Namaste menu) 
- Similarly, added [namaste-prev-lesson] to show the previous lesson link
- Improved course cleanup - now homework solutions are deleted on cleanup. If you use watu / watupro exams you can select to cleanup them as well (from the Namaste Settings page)
- User can be enrolled in course using username as well
- Fixed small issue with role restriction on enrolling

= Version 1.2 =
- Avoids duplicate completion on lessons and courses
- Allow using [namaste-course-lessons] inside a lesson page of the course
- Added new shortcodes to allow exporting part of the functionality outside of the user's dashboard. Shorcodes added: [namaste-mycourses] and [namaste-course-lessons]. See the internal Help page for more details.
- Added Help page and information about Namaste! Reports in the Plugins/API page 
- Force activation hook on update because activation hook doesn't run sometimes
- Completed the points system (see Namaste Settings page)
- added shortcodes for user points and simple points-based leaderboard
- Created DB log for all important user actions
- Fixed issues with clearing the DB history log
- Fixed important issue regarding who can change user roles that administre the LMS

= Version 1.1 =
- Gradebook - you can now grade user performance in assignments, lessons and courses
- The grading system is configurable by you
- User sees My Gradebook in their dashboard when grading system is enabled
- Catching watu/watupro submit actions so lesson status can be immediatelly updated when exam is submitted
- Added Course columng in Manage Lessons page so you can see which course each lesson belongs to
- Added shortcode [namaste-enroll] to display enrollment button or information right on the course page
- Added action to allow other plugins add their submenu under Namaste! LMS
- Added basic visit stats for courses and lessons

= Version 1.0 = 
- Paypal payment button can now be generated automatically
- Paypal IPN will be handled and enrollment will be automatically inserted after payment (pending or active, depends on your settings)
- Added information about Namaste! Connect and the Developers API
- Stripe integration implemented, you can now accept Stripe payments
- Fixed issue with backslashes in assignments
- Fixed issues with thickbox
- Fixed bug with marking lessons as visited
- Fixed bug with {{name}} bariable in certificates
- Fixed bug with lesson completeness when admin approval is not required
- Fixed bugs with premature marking lesson as accessed
- Fixed bug when cleaning up student/course record
- Fixed several strick mode issues
- Fixed problem with adding the custom post type to homepage
- Added missing thickbox include 

= Version 0.9 = 
- Important bug fixes on required homeworks
- "In progress" popup showing what does a student has to do to complete a course
- [namaste-todo] shortcode for lessons and courses to show what you need to do to complete them
- Let admin/manager access any lesson (no need to be enrolled)
- Started the developers API. More info on http://namaste-lms.org/developers.php (this is still the very beginning!)
- You can require payment for a course (for now payment processing is manual)
- Fixed bug with certificates
- Filter students by enrollment status
- Cleanup completed or rejected student from a course

= Version 0.8 =
- Admin can create/edit personalized certificates
- Users get certificates assigned to them upon successfully completing courses
- bug fixes and code improvements

= Version 0.7 =
- admin can see everyone's solution to a homework
- admin/manager can also be a student and has My Courses section
- other small bug fixes and code improvements