<?php
/**
 * Displays a topic.
 *
 * Available Variables:
 * 
 * $course_id 		: (int) ID of the course
 * $course 		: (object) Post object of the course
 * $course_settings : (array) Settings specific to current course
 * $course_status 	: Course Status
 * $has_access 	: User has access to course or is enrolled.
 * 
 * $courses_options : Options/Settings as configured on Course Options page
 * $lessons_options : Options/Settings as configured on Lessons Options page
 * $quizzes_options : Options/Settings as configured on Quiz Options page
 * 
 * $user_id 		: (object) Current User ID
 * $logged_in 		: (true/false) User is logged in
 * $current_user 	: (object) Currently logged in user object
 * $quizzes 		: (array) Quizzes Array
 * $post 			: (object) The topic post object
 * $lesson_post 	: (object) Lesson post object in which the topic exists
 * $topics 		: (array) Array of Topics in the current lesson
 * $all_quizzes_completed : (true/false) User has completed all quizzes on the lesson Or, there are no quizzes.
 * $lesson_progression_enabled 	: (true/false)
 * $show_content	: (true/false) true if lesson progression is disabled or if previous lesson and topic is completed. 
 * $previous_lesson_completed 	: (true/false) true if previous lesson is completed
 * $previous_topic_completed	: (true/false) true if previous topic is completed
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\Topic
 */
?>


<?php
/**
 * Topic Dots
 */
?>
<?php if ( ! empty( $topics ) ) : ?>
	<div id='learndash_topic_dots-<?php echo esc_attr( $lesson_id ); ?>' class="learndash_topic_dots type-dots">

		<b><?php printf( _x( '%s Progress:', 'Topic Progress Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'topic' ) ); ?></b>

		<?php foreach ( $topics as $key => $topic ) : ?>
			<?php $completed_class = empty( $topic->completed ) ? 'topic-notcompleted' : 'topic-completed'; ?>
			<a class='<?php echo esc_attr( $completed_class ); ?>' href='<?php echo get_permalink( esc_attr( $topic->ID ) ); ?>' title='<?php echo esc_attr( $topic->post_title ); ?>'>
				<span title='<?php echo esc_attr( $topic->post_title ); ?>'></span>
			</a>
		<?php endforeach; ?>

	</div>
<?php endif; ?>

<div id="learndash_back_to_lesson"><a href='<?php echo esc_attr( get_permalink( $lesson_id) ); ?>'>&larr; <?php printf( _x( 'Back to %s', 'Back to Lesson Label', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ); ?></a></div>

<?php if ( $lesson_progression_enabled && ! $previous_topic_completed ) : ?>

	<span id="learndash_complete_prev_topic"><?php  echo sprintf( _x( 'Please go back and complete the previous %s.', 'placeholder topic', 'learndash' ), LearnDash_Custom_Label::label_to_lower('topic') ); ?></span>
    <br />

<?php elseif ( $lesson_progression_enabled && ! $previous_lesson_completed ) : ?>

	<span id="learndash_complete_prev_lesson"><?php echo sprintf( _x( 'Please go back and complete the previous %s.', 'placeholder lesson', 'learndash' ), LearnDash_Custom_Label::label_to_lower('lesson') ); ?></span>
    <br />

<?php endif; ?>

<?php if ( $show_content ) : ?>
	
	<?php

	$topic_meta = array();
	$topic_meta['live_session_video'] = get_post_meta( $topic->ID, 'live_session_video', true );
	$topic_meta['facilitator_video'] = get_post_meta( $topic->ID, 'facilitator_video', true );
	$topic_meta['presentation_doc'] = get_post_meta( $topic->ID, 'presentation_doc', true );
	$topic_meta['coaching_guide_doc'] = get_post_meta( $topic->ID, 'coaching_guide_doc', true );
	$topic_meta['facilitators_guide_link'] = get_post_meta( $topic->ID, 'facilitators_guide_link', true );
	$topic_meta['consultants_guide_link'] = get_post_meta( $topic->ID, 'consultants_guide_link', true );

	$topic_meta['workbook_link'] = get_post_meta( $topic->ID, 'workbook_link', true );
	$topic_meta['other_assets'] = get_post_meta( $topic->ID, 'other_assets', true );
	$topic_meta['third_party_assets'] = get_post_meta( $topic->ID, 'third_party_assets', true );

	$vpm_controller = apply_filters( "vpm-sfwd-topic-controller", null);
	$vpm_view = $vpm_controller->get_view();

	$end_user = $vpm_controller->is_enduser();
	$facilitator = $vpm_controller->is_facilitator();

	if ( true === $facilitator ) { ?>
		<div class="vpm-show-videos clearfix">
			<a href="#" id="vpm-toggle-enduser-video-lnk" class="vpm-toggle-link button">
				<?php _e( "End user video", "vpmlang" ); ?>
			</a>
			<a href="#" id="vpm-toggle-facilitator-video-lnk" class="vpm-toggle-link button">
				<?php _e( "Facilitator video", "vpmlang" ); ?>
			</a>
		</div>
		<?php
	}
	?>
	<!-- Load window for end-customer video for the topic/session/segment -->
	<?php
	if ( true === $end_user ) { ?>
		<div class="vpm-video-show vpm-user-video">
		<?php
		echo ( false !== $vpm_view ? $vpm_view->show_topic_video( $topic_meta['live_session_video']) : null ); ?>
		</div><?php
	}
	?>
	<!-- Possible window for facilitator video for the topic/session/segment -->
	<?php
	if ( true === $facilitator ) { ?>

		<div class="vpm-video-show vpm-facilitator-video">
			<div class="vpm-separator"></div>
			<h3><?php _e("Facilitator video", "vpmlang"); ?></h3><?php
			echo $vpm_view->show_topic_video( $topic_meta['facilitator_video'] ); ?>
		</div>
		<?php
	}
	?>
	<!-- Load the text content for the topic/session/segment -->
	<?php echo $content; ?>

	<!-- Load the meta data (links, etc) for the topic/session/segment -->
	<?php echo $vpm_view->show_topic_meta( $topic_meta ); ?>

	<?php if ( ! empty( $quizzes ) ) : ?>
		<div id="learndash_quizzes">
			<div id="quiz_heading"><span><?php echo LearnDash_Custom_Label::get_label( 'quizzes' ); ?></span><span class="right"><?php _e( 'Status', 'learndash' ) ?></span></div>

			<div id="quiz_list">
			<?php foreach( $quizzes as $quiz ) : ?>
				<div id='post-<?php echo esc_attr( $quiz['post']->ID ); ?>' class='<?php echo esc_attr( $quiz['sample'] ); ?>'>
					<div class="list-count"><?php echo $quiz['sno']; ?></div>
					<h4>
						<a class='<?php echo esc_attr( $quiz['status'] ); ?>' href='<?php echo esc_attr( $quiz['permalink'] ); ?>'><?php echo $quiz['post']->post_title; ?></a>
					</h4>
				</div>
			<?php endforeach; ?>
			</div>
		</div>	
	<?php endif; ?>

	<?php if ( lesson_hasassignments( $post ) ) : ?>

		<?php $assignments = learndash_get_user_assignments( $post->ID, $user_id ); ?>

		<div id="learndash_uploaded_assignments">
			<h2><?php _e( 'Files you have uploaded', 'learndash' ); ?></h2>
			<table>
				<?php if ( ! empty( $assignments ) ) : ?>
					<?php foreach( $assignments as $assignment ) : ?>
							<tr>
								<td><a href='<?php echo esc_attr( get_post_meta( $assignment->ID, 'file_link', true ) ); ?>' target="_blank"><?php echo __( 'Download', 'learndash' ) . ' ' . get_post_meta( $assignment->ID, 'file_name', true ); ?></a></td>
								<td><a href='<?php echo esc_attr( get_permalink( $assignment->ID) ); ?>'><?php _e( 'Comments', 'learndash' ); ?></a></td>
							</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</table>
		</div>

	<?php endif; ?>


	<?php
    /**
     * Show Mark Complete Button
     */
    ?>
	<?php if ( $all_quizzes_completed ) : ?>
		<?php echo '<br />' . learndash_mark_complete( $post ); ?>
	<?php endif; ?>

<?php endif; ?>

<p id="learndash_next_prev_link"><?php echo learndash_previous_post_link(); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo learndash_next_post_link(); ?></p>