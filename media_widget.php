<?php
/**
 * Plugin Name: Media Widget
 * Description: Provide a widget for listing media tagged by the Media-Tags plugin by Paul Menard.
 * Version: 0.2
 * Author: Sunny Themes
 * Author URI: http://www.sunnythemes.com
 *

Copyright 2010 Sunny Themes

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 */

class Media_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function Media_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'media_widget', 'description' => __('List media tagged using the Media-Tags plugin.', 'media_widget') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'media-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'media-widget', __('Media Widget', 'media_widget'), $widget_ops, $control_ops );

		/* Actions */
		add_action( 'widgets_init', array (&$this, 'load_widget') );
		add_action( 'plugins_loaded', array (&$this, 'checkDeps') );

	}

	/**
	 * Check for co-dependencies.
	 */
	function checkDeps() {
		if (!class_exists('MediaTags') ) {
			add_action('admin_notices', create_function('', 'echo \'<div id="message" class="error fade"><p><strong>' . __('Media Widget requires Media Tags plugin.','media-widget') . '</strong></p></div>\';'));
			return;
		}
	}

	/**
	 * Register the widget
	 */
	function load_widget() { 
		register_widget( 'Media_Widget' ); 
	}

	/**
	 * Display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$tag = $instance['tag'];
		$count = $instance['count'];

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		if ( ($tag != '--') && function_exists('get_attachments_by_media_tags') ) {
			echo "<ul>";
			$media_list = get_attachments_by_media_tags("media_tags=$tag&orderby=date&order=DESC");
			if ($media_list != null) {
				foreach($media_list as $media_item) {
					if ($count !== '') {
						if ($count == 0) break;
						$count --; 
					}
					echo "<li><a href='" . $media_item->guid . "'>" . $media_item->post_title . "</a></li>";
				}
			}
			echo "</ul>";
		} else {
			_e('No media selected', 'media_widget');
		}

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['count'] = strip_tags( $new_instance['count'] );
		if (!is_numeric($instance['count'])) $instance['count'] = '';

		/* No need to strip tags for non-text. */
		$instance['tag'] = $new_instance['tag'];

		return $instance;
	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __('Media', 'media_widget'), 'tag' => '', 'count' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults ); 

		/* Make sure Media-Tags has been activated */
		if (!function_exists('get_mediatags')) {
			echo "<p class='error'>";
			_e('ERROR: This widget requires the ', 'media_widget');
			echo "<a href='http://wordpress.org/extend/plugins/media-tags/' target='_blank'>Media-Tags</a>";
			_e(' plugin', 'media_widget');
			echo "</p>";
		}
		?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'media_widget'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<!-- Tag: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'tag' ); ?>"><?php _e('Tag:', 'media_widget'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'tag' ); ?>" name="<?php echo $this->get_field_name( 'tag' ); ?>" class="widefat" style="width:100%;">
				<option>--</option>
				<?php
				if (function_exists('get_mediatags')) {
					$tags = get_mediatags();
					foreach($tags as $tag_item)
					{
						echo "<option ";
						if ($instance['tag'] == $tag_item->name) echo 'selected="selected"';
						echo ">" . $tag_item->name . "</option>";
					}
				}
				?>
			</select>
		</p>

		<!-- Count: How many items should we list? Blank to list all items. -->
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e('Number of media items to list:', 'media_widget'); ?></label>
			<input id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo $instance['count']; ?>" style="width:100%;" />
		</p>

	<?php
	}
}

//start the plugin
$mediaWidget = new Media_Widget();
?>