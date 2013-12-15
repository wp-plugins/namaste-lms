<div class="wrap">
	<h1><?php _e('Namaste! LMS Help', 'namaste');?></h1>
	
	<p><?php _e('We are just starting to add some helpful information so please bear with us until it is extended. For now you can learn about the available shortcodes. Do not forget also to check the <a href="http://namaste-lms.org/help.php" target="_blank">quick help guide on our site</a>.', 'namaste');?>
	
	<h2><?php _e('Available Shortcodes', 'namaste');?></h2>
	
	<p><b>[namaste-todo]</b> <?php _e('This is flexible shortcode that can be placed inside a lesson or course content. It will display what the logged in student still needs to do to complete the given lesson or course.', 'namaste');?></p>
	
	<p><b>[namaste-enroll]</b> <?php _e('displays enroll button or "enrolled/pending enrollment" message in the course.', 'namaste')?></p>
	
	<p><b>[namaste-mycourses]</b> <?php _e('Displays simplified version of the student dashboard - the same table with all the available courses but without the "view lessons" link. Instead of this link you can include the shortcode for "lessons in course" (given below) in the course page itself.', 'namaste')?> </p>
	
	<p><b>[namaste-course-lessons]</b> <?php _e('or', 'namaste')?> <b>[namaste-course-lessons status y]</b> <?php _e('or', 'namaste')?> <b>[namaste-course-lessons status y orderby direction]</b> <?php _e('will display all the lessons in a course along with links to them. If you use the second format and pass "status" as first argument, the shortcode will output the lessons in a table where the second argument will be the current status (started, not started, completed). You can also pass a number in place of the argument "y" to specify a course ID (otherwise current course ID is used). This might be useful if you are manually or programmatically creating some list of courses along with the lessons in them. If you want to use the course ID argument but not the status columng, pass 0 in pace of status like this: [namaste-course-lesson 0 5]. The third format lets you specify ordering using SQL field names from the posts table. For example [namaste-course-lessons 0 0 post_date DESC] will return the lessons displayed by the order of publishing, descending (latest lesson will be shown on top).', 'namaste');?> <b><?php _e('Note that status column will be shown only for logged in users.', 'namaste')?></b></p>
	
	<p><b>[namaste-next-lesson]</b> <?php _e('or', 'namaste')?> <b>[namaste-next-lesson "hyperlinked text"]</b>
		<?php _e('can be used only in a lesson and will display the next lesson from the course. You can replace "hyperlinked text" with your own text. If you omit the parameter the link will say "next lesson".', 'namaste')?></p>
		
	<p><b>[namaste-prev-lesson]</b> <?php _e('or', 'namaste')?> <b>[namaste-prev-lesson "hyperlinked text"]</b>
		<?php _e('Similar to the above, used to display a link for the previous lesson in this course. Note that lessons are ordered in the order of creation.', 'namaste')?></p>	
</div>