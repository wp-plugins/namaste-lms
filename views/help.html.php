<div class="wrap">
	<h1><?php _e('Namaste! LMS Help', 'namaste');?></h1>
	
	<p><?php _e('For a quick overview of how to get started check the <a href="http://namaste-lms.org/help.php" target="_blank">quick help guide on our site</a>.', 'namaste');?>
	
	<p><?php printf(__('More detailed documentation is available at the <a href="%s" target="_blank">new documentation site</a>.', 'namaste'), 'http://demo.namaste-lms.org');?></p>
	
	<h2><?php _e('Please Rate Us!', 'namaste')?></h2>
	
	<p><?php printf(__('Several hundred hours of development have been invested into the development of this free plugin. Now we need <a href="%s" target="_blank">five minutes of your time and five stars</a> to help the core plugin stay free!', 'namaste'), 'http://wordpress.org/support/view/plugin-reviews/namaste-lms');?></p>
	
	<h2><?php _e('Available Shortcodes', 'namaste');?></h2>
	
	<p><input type="text" value='[namaste-todo]' onclick="this.select();" readonly="readonly"> <?php _e('This is flexible shortcode that can be placed inside a lesson or course content. It will display what the logged in student still needs to do to complete the given lesson or course. You can pass "ul" or "ol" as first argument to define ordered or unordered list.', 'namaste');?></p>
	
	<p><input type="text" value='[namaste-enroll]' onclick="this.select();" readonly="readonly"> <?php _e('displays enroll button or "enrolled/pending enrollment" message in the course.', 'namaste')?></p>
	
	<p><input type="text" value='[namaste-mycourses]' onclick="this.select();" readonly="readonly"> <?php _e('Displays simplified version of the student dashboard - the same table with all the available courses but without the "view lessons" link. Instead of this link you can include the shortcode for "lessons in course" (given below) in the course page itself.', 'namaste')?> </p>
	
	<p><input type="text" value='[namaste-mycertificates]' onclick="this.select();" readonly="readonly"> <?php _e('Displays the "My certificates" page.', 'namaste')?> </p>
	
	<p><input type="text" value='[namaste-earned-certificates]' onclick="this.select();" readonly="readonly"> <?php _e('Displays links to the certificates earned by the currently logged user in the course. This works for the current course when placed in a course page. You can also use the shortcode by passing parameter "course_id" like this: [namaste-earned-certificates course_id=5]. You can also add some text which will be conditionally displayed where there are any certificates earned. For example: [namaste-earned-certificates course_id=5 text="You have earned certificates for completing this course:"]', 'namaste')?> </p>
	
	<p><input type="text" value='[namaste-course-lessons]' onclick="this.select();" readonly="readonly"> <?php _e('or', 'namaste')?> <b>[namaste-course-lessons status y]</b> <?php _e('or', 'namaste')?> <b>[namaste-course-lessons status y orderby direction list_tag]</b> <?php _e('will display all the lessons in a course along with links to them. If you use the second format and pass "status" as first argument, the shortcode will output the lessons in a table where the second argument will be the current status (started, not started, completed). You can also pass a number in place of the argument "y" to specify a course ID (otherwise current course ID is used). This might be useful if you are manually or programmatically creating some list of courses along with the lessons in them. If you want to use the course ID argument but not the status columng, pass 0 in pace of status like this: [namaste-course-lesson 0 5]. The third format lets you specify ordering using SQL field names from the posts table. For example [namaste-course-lessons 0 0 post_date DESC] will return the lessons displayed by the order of publishing, descending (latest lesson will be shown on top). In place of "list_tag" you can pass "ul" or "ol". If empty, defaults to "ul".', 'namaste');?> <b><?php _e('Note that status column will be shown only for logged in users.', 'namaste')?></b></p>
	
	<p><input type="text" value='[namaste-first-lesson]' onclick="this.select();" readonly="readonly"> <?php _e('or', 'namaste')?> <b>[namaste-first-lesson "hyperlinked text"]</b>
		<?php _e('can be used only in a course page and will display the first lesson from the course. You can replace "hyperlinked text" with your own text. If you omit the parameter the link will say "first lesson".', 'namaste')?></p>
	
	<p><input type="text" value='[namaste-next-lesson]' onclick="this.select();" readonly="readonly"> <?php _e('or', 'namaste')?> <b>[namaste-next-lesson "hyperlinked text"]</b>
		<?php _e('can be used only in a lesson and will display the next lesson from the course. You can replace "hyperlinked text" with your own text. If you omit the parameter the link will use the lesson title as hyperlinked text.', 'namaste')?></p>
		
	<p><input type="text" value='[namaste-prev-lesson]' onclick="this.select();" readonly="readonly"> <?php _e('or', 'namaste')?> <b>[namaste-prev-lesson "hyperlinked text"]</b>
		<?php _e('Similar to the above, used to display a link for the previous lesson in this course. Note that lessons are ordered in the order of creation.', 'namaste')?></p>	
		
	<p><input type="text" value='[namaste-assignments lesson_id="X"]' onclick="this.select();" readonly="readonly" size="30"> <?php _e('(where X is lesson ID) will output the assignments to the lesson on the front-end. The links to submit and view solutions will also work. You can omit the "lesson_id" parameter and pass it as URL variable. This could be useful if you are manually building a page with lessons and want to give links to assignments from it.', 'namaste')?></p>	
	
	<h2><?php _e('Customizing the Look of Course and Lesson Pages', 'namaste');?> </h2>
	
	<p><?php printf(__('Courses and lessons in Namaste! LMS are custom post types. How to create your custom post type templates is explained <a href="%s" target="_blank">on this page</a>. In short, here are the templates you may want to create are:<b>%s</b>.', 'namaste'), 'https://codex.wordpress.org/Post_Type_Templates', 'archive-namaste_course.php, single-namaste_course.php, archive-namaste_lesson.php, single-namaste_lesson.php');?></p>
		
	<h2><?php _e('Redesigning and Customizing the Views / Templates', 'namaste');?></h2>
	
	<p style="color:red;"><b><?php _e('Only for advanced users!', 'namaste')?></b></p>
	
	<p><?php _e('You can safely customize all files from the "views" folders by placing their copies in your theme folder. Simply create folder "namaste" <b>in your theme root folder</b> and copy the files you want to custom from "views" folder directly there.', 'namaste')?></p>

	<p><?php _e('For example:', 'namaste')?></p>
	
	<ol>
		<li><?php _e('If you are using the Twenty Fourteen theme, you should create folder "namaste" under it so the structure will now be something like <b>wp-content/themes/twentyfourteen/namaste</b>. (The files that are above the new "namaste" folder should remain where they are)', 'namaste')?></li>
		<li><?php _e('Then if you want to modify the "Manage Certificates" page copy the file certificates.php from the plugin "views" folder and place it in the new "namaste" folder so you will have  <b>wp-content/themes/twentyfourteen/namaste/certificates.php</b>', 'namaste')?></li>	
	</ol>	
	
	<p><?php _e("Don't worry if you use modified WordPress directory structure and don't have 'wp-content' folder. The trick will work with any structure as long as you follow the same logic.", 'namaste')?></p>
	
	<p><?php _e('Then feel free to modify the code, but of course be careful not to mess with the PHP or Javascript inside. This will let you change the design and even part of the functionality and not lose these changes when the plugin is upgraded. Be careful: we can not provide support for your custom versions of our views.', 'namaste')?></p>
		
	<?php do_action('namaste-help');?>	
</div>