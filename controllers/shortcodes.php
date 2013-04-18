<?php
// various Namaste shortcodes
class NamasteLMSShortcodesController {
	// what's todo in a lesson or course
   static function todo() {
   	global $post, $user_ID;
   	
   	if(!is_user_logged_in()) return "";
   	
   	if($post->post_type == 'namaste_lesson') {   		
   		$todo = NamasteLMSLessonModel :: todo($post->ID, $user_ID);   		
   		ob_start();
   		require(NAMASTE_PATH."/views/lesson-todo.php");
   		if(!empty($todo['todo_nothing'])) _e('This lesson has been completed.', 'namaste');
   		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;		
   	}
   	
   	if($post->post_type == 'namaste_course') {
   		$_course = new NamasteLMSCourseModel();
   		
   		$required_lessons = $_course->required_lessons($post->ID, $user_ID);
   		
   		$content = "";
   		
   		if(!empty($required_lessons)) {
   			$content .= "<ul>\n";
   			foreach($required_lessons as $lesson) {
   				$content .= "<li".($lesson->namaste_completed?' class="namaste-completed" ':' class="namaste-incomplete" ')."><a href='".get_permalink($lesson->ID)."'>".$lesson->post_title."</a> - ";
					if($lesson->namaste_completed) $content .= __('Completed', 'namaste');
					else $content .= __('Not completed', 'namaste');			
   				
   				$content .= "</li>\n";
   			}   			
   			$content .= "</ul>";
   		}
   		
   		return $content;
   	}
   } // end todo
}