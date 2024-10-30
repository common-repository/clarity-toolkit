<?php
/*
Plugin Name: Clarity Toolkit
Plugin URI: http://www.blogthememachine.com
Description: Shortcodes and widgets for the Clarity WordPress theme from Blog Theme Machine.
Version: 1.3
Author: Mike Smith
Author URI: http://www.madebyguerrilla.com
*/

/*
 * Clarity Shortcodes
 *
**/

function clarity_toolkit_shortcodes_style() {
	// Register the style like this for a plugin:
	wp_register_style( 'clarity-toolkit-shortcodes', plugins_url( 'style.css', __FILE__ ), array(), '20160209', 'all' );
	// For either a plugin or a theme, you can then enqueue the style:
	wp_enqueue_style( 'clarity-toolkit-shortcodes' );
}

add_action( 'wp_enqueue_scripts', 'clarity_toolkit_shortcodes_style' );

// This theme uses post thumbnails

function clarity_shortcodes_add_image_size() {
    add_image_size( 'recentposts', 280, 140, true ); // Recent Posts
	add_image_size( 'work', 328, 328, true ); // Work image
    add_image_size( 'popularposts', 390, 180, true ); // Popular Posts
    add_image_size( 'relatedposts', 180, 180, true ); // Related Posts
}
add_action( 'after_setup_theme', 'clarity_shortcodes_add_image_size', 11 );

// Recent Posts Shortcode

function clarity_recent_posts_shortcode($atts){
	
	extract(shortcode_atts(array(
		'posts' => '4',
		'class' => 'col3',
		'order' => 'date',
		'category' => '',
		'imgsize' => 'recentposts',
	), $atts));

	$q = new WP_Query(
		array( 'orderby' => $order, 'posts_per_page' => $posts, 'cat' => $category )
	);
	$list = '';

	while($q->have_posts()) : $q->the_post();
		$thumb_id = get_post_thumbnail_id();
		$thumb_url_array = wp_get_attachment_image_src($thumb_id, $imgsize, true);
		$thumb_url = $thumb_url_array[0];
		$list .= '<div class="'. $class .' recentposts"><a href="' . get_permalink() . '"><img src="' . $thumb_url .'" alt="' . get_the_title() . '" /></a><h5><a href="' . get_permalink() . '">' . get_the_title() . '</a></h5></div>';
	endwhile;
wp_reset_query();
return $list . '';
}

add_shortcode('recent-posts', 'clarity_recent_posts_shortcode');

// Recent Work Shortcode
// Requires "Guerrilla's Work CPT plugin"
// https://wordpress.org/plugins/guerrillas-work-cpt/

function clarity_work_shortcode($atts){
	
	extract(shortcode_atts(array(
		'posts' => '3',
		'class' => 'col4',
		'order' => 'date',
	), $atts));

	$q = new WP_Query(
		array( 'post_type' => 'work', 'orderby' => $order, 'posts_per_page' => $posts)
	);
	$list = '';

	while($q->have_posts()) : $q->the_post();
		$featuredimage = wp_get_attachment_url( get_post_thumbnail_id($post->ID), 'work', true);
			$thumb_id = get_post_thumbnail_id();
			$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'work', true);
			$thumb_url = $thumb_url_array[0];
		$list .= '<div class="'. $class .' work"><a href="' . get_permalink() . '"><img src="' . $thumb_url .'" alt="' . get_the_title() . '" /></a></div>';
	endwhile;

wp_reset_query();
return $list . '';
}

add_shortcode('recent-work', 'clarity_work_shortcode');

// Filter out wpautop and fix shortcode output

remove_filter( 'the_content', 'wpautop' );
add_filter( 'the_content', 'wpautop', 99 );
add_filter( 'the_content', 'shortcode_unautop', 100 );

// Button Shortcodes
// added: 5-23-2015

// Regular button shortcode Shortcode
function claritybtn($atts, $content = null) {
	extract(shortcode_atts(array(
		"class" => 'btn',
		"id" => '',
		"href" => ''
	), $atts));
	return '<a class="'.$class.'" id="'.$id.'" href="'.$href.'">'.do_shortcode($content).'</a>';
}

add_shortcode("btn", "claritybtn");

// Infobox Shortcode
// added: 1-22-2016
// updated: 2-9-2016
function clarityinfobox($atts, $content = null) {
	extract(shortcode_atts(array(
		"class" 	=> 'col6',
		"title" 	=> '',
		"subtitle" 	=> '',
		"image"		=> '',
		"href" 		=> '',
		"text"		=> 'light',
	),
	$atts));
	return '<div class="infobox '. $class .' '. $text .'"><a href="' . $href . '"><img src="' . $image .'" alt="' . $title . '" /></a><h3>' . $title . '<span>' . $subtitle . '</span></h3></div>';
}
add_shortcode("infobox", "clarityinfobox");
	
// Related Posts Shortcode
// added: 2-11-2016
function clarity_related_posts_shortcode($atts){
	
	extract(shortcode_atts(array(
		'posts' => '4',
		'class' => 'col3',
		'imgsize' => 'relatedposts',
	), $atts));
	
	$categories = get_the_category($post->ID);
	if ($categories) {
		$category_ids = array();
		foreach($categories as $individual_category) $category_ids[] = $individual_category->term_id;
	}

	$q = new WP_Query(
		array(
			'category__in' => $category_ids,
			'post__not_in' => array($post->ID),
			'posts_per_page'=> $posts, // Number of related posts that will be shown.
			'caller_get_posts'=>1
		)
	);
	$list = '<div class="relatedposts"><h3>Related Posts</h3>';

	while($q->have_posts()) : $q->the_post();
		$thumb_id = get_post_thumbnail_id();
		$thumb_url_array = wp_get_attachment_image_src($thumb_id, $imgsize, true);
		$thumb_url = $thumb_url_array[0];
		$list .= '<div class="'. $class .'"><a href="'. get_permalink() .'" rel="bookmark" title="'. get_the_title() .'"><img src="'. $thumb_url .'" alt=""><span>'. get_the_title().'</span></a></div>';
	endwhile;
wp_reset_query();
return $list . '</div>';
}

add_shortcode('related-posts', 'clarity_related_posts_shortcode');


/*
 * Clarity Widgets
 *
**/

// Social icons widget

class ClaritySocialIconsWidget extends WP_Widget
{
    function ClaritySocialIconsWidget(){
		$widget_ops = array('description' => 'Display your social profile icons');
		$control_ops = array('width' => 300, 'height' => 300);
		parent::WP_Widget(false,$name='Clarity Social Icons',$widget_ops,$control_ops);
    }

  /* Displays the Widget in the front-end */
    function widget($args, $instance){
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		$ClaritySocialTwitter = empty($instance['ClaritySocialTwitter']) ? '' : $instance['ClaritySocialTwitter'];
		$ClaritySocialFacebook = empty($instance['ClaritySocialFacebook']) ? '' : $instance['ClaritySocialFacebook'];
		$ClaritySocialLinkedin = empty($instance['ClaritySocialLinkedin']) ? '' : $instance['ClaritySocialLinkedin'];
		$ClaritySocialGoogle = empty($instance['ClaritySocialGoogle']) ? '' : $instance['ClaritySocialGoogle'];
		$ClaritySocialInstagram = empty($instance['ClaritySocialInstagram']) ? '' : $instance['ClaritySocialInstagram'];
		$ClaritySocialPinterest = empty($instance['ClaritySocialPinterest']) ? '' : $instance['ClaritySocialPinterest'];
		$ClaritySocialYoutube = empty($instance['ClaritySocialYoutube']) ? '' : $instance['ClaritySocialYoutube'];
		$ClaritySocialTumblr = empty($instance['ClaritySocialTumblr']) ? '' : $instance['ClaritySocialTumblr'];
		$ClaritySocialEmail = empty($instance['ClaritySocialEmail']) ? '' : $instance['ClaritySocialEmail'];

		echo $before_widget;

		if ( $title )
		echo $before_title . $title . $after_title;
?>

<div class="clarity-social-icons">

<?php if ( $ClaritySocialTwitter !='' ) { ?>
<a href="<?php echo($ClaritySocialTwitter); ?>" target="_blank"><i class="fa fa-twitter fa-2x"></i></a>  
<?php } ?>

<?php if ( $ClaritySocialFacebook !='' ) { ?>
<a href="<?php echo($ClaritySocialFacebook); ?>" target="_blank"><i class="fa fa-facebook fa-2x"></i></a>  
<?php } ?>

<?php if ( $ClaritySocialLinkedin !='' ) { ?>
<a href="<?php echo($ClaritySocialLinkedin); ?>" target="_blank"><i class="fa fa-linkedin fa-2x"></i></a>  
<?php } ?>

<?php if ( $ClaritySocialGoogle !='' ) { ?>
<a href="<?php echo($ClaritySocialGoogle); ?>" target="_blank"><i class="fa fa-google fa-2x"></i></a>  
<?php } ?>

<?php if ( $ClaritySocialInstagram !='' ) { ?>
<a href="<?php echo($ClaritySocialInstagram); ?>" target="_blank"><i class="fa fa-instagram fa-2x"></i></a>  
<?php } ?>

<?php if ( $ClaritySocialPinterest !='' ) { ?>
<a href="<?php echo($ClaritySocialPinterest); ?>" target="_blank"><i class="fa fa-pinterest fa-2x"></i></a>  
<?php } ?>

<?php if ( $ClaritySocialYoutube !='' ) { ?>
<a href="<?php echo($ClaritySocialYoutube); ?>" target="_blank"><i class="fa fa-youtube fa-2x"></i></a>  
<?php } ?>

<?php if ( $ClaritySocialTumblr !='' ) { ?>
<a href="<?php echo($ClaritySocialTumblr); ?>" target="_blank"><i class="fa fa-tumblr fa-2x"></i></a>  
<?php } ?>

<?php if ( $ClaritySocialEmail !='' ) { ?>
<a href="mailto:<?php echo($ClaritySocialEmail); ?>" target="_blank"><i class="fa fa-envelope fa-2x"></i></a>  
<?php } ?>

</div><!-- END .clarity-social-icons -->

<?php
		echo $after_widget;
	}

  /*Saves the settings. */
    function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = stripslashes($new_instance['title']);
		$instance['ClaritySocialTwitter'] = stripslashes($new_instance['ClaritySocialTwitter']);
		$instance['ClaritySocialFacebook'] = stripslashes($new_instance['ClaritySocialFacebook']);
		$instance['ClaritySocialLinkedin'] = stripslashes($new_instance['ClaritySocialLinkedin']);
		$instance['ClaritySocialGoogle'] = stripslashes($new_instance['ClaritySocialGoogle']);
		$instance['ClaritySocialInstagram'] = stripslashes($new_instance['ClaritySocialInstagram']);
		$instance['ClaritySocialPinterest'] = stripslashes($new_instance['ClaritySocialPinterest']);
		$instance['ClaritySocialYoutube'] = stripslashes($new_instance['ClaritySocialYoutube']);
		$instance['ClaritySocialTumblr'] = stripslashes($new_instance['ClaritySocialTumblr']);
		$instance['ClaritySocialEmail'] = stripslashes($new_instance['ClaritySocialEmail']);

		return $instance;
	}

  /*Creates the form for the widget in the back-end. */
    function form($instance){
		//Defaults
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'=>'',
				'ClaritySocialTwitter'=>'',
				'ClaritySocialFacebook'=>'',
				'ClaritySocialLinkedin'=>'',
				'ClaritySocialGoogle'=>'',
				'ClaritySocialInstagram'=>'',
				'ClaritySocialPinterest'=>'',
				'ClaritySocialYoutube'=>'',
				'ClaritySocialTumblr'=>'',
				'ClaritySocialEmail'=>''
			)
		);

		$title = htmlspecialchars($instance['title']);
		$ClaritySocialTwitter = htmlspecialchars($instance['ClaritySocialTwitter']);
		$ClaritySocialFacebook = htmlspecialchars($instance['ClaritySocialFacebook']);
		$ClaritySocialLinkedin = htmlspecialchars($instance['ClaritySocialLinkedin']);
		$ClaritySocialGoogle = htmlspecialchars($instance['ClaritySocialGoogle']);
		$ClaritySocialInstagram = htmlspecialchars($instance['ClaritySocialInstagram']);
		$ClaritySocialPinterest = htmlspecialchars($instance['ClaritySocialPinterest']);
		$ClaritySocialYoutube = htmlspecialchars($instance['ClaritySocialYoutube']);
		$ClaritySocialTumblr = htmlspecialchars($instance['ClaritySocialTumblr']);
		$ClaritySocialEmail = htmlspecialchars($instance['ClaritySocialEmail']);

		# Title
		echo '<p><label for="' . $this->get_field_id('title') . '">' . 'Title:' . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>';
		# Twitter URL
		echo '<p><label for="' . $this->get_field_id('ClaritySocialTwitter') . '">' . 'Twitter URL (full http://):' . '</label><input class="widefat" id="' . $this->get_field_id('ClaritySocialTwitter') . '" name="' . $this->get_field_name('ClaritySocialTwitter') . '" type="text" value="' . $ClaritySocialTwitter . '" /></p>';
		# Facebook URL
		echo '<p><label for="' . $this->get_field_id('ClaritySocialFacebook') . '">' . 'Facebook URL (full http://):' . '</label><input class="widefat" id="' . $this->get_field_id('ClaritySocialFacebook') . '" name="' . $this->get_field_name('ClaritySocialFacebook') . '" type="text" value="' . $ClaritySocialFacebook . '" /></p>';
		# Linkedin URL
		echo '<p><label for="' . $this->get_field_id('ClaritySocialLinkedin') . '">' . 'Linkedin URL (full http://):' . '</label><input class="widefat" id="' . $this->get_field_id('ClaritySocialLinkedin') . '" name="' . $this->get_field_name('ClaritySocialLinkedin') . '" type="text" value="' . $ClaritySocialLinkedin . '" /></p>';
		# Google URL
		echo '<p><label for="' . $this->get_field_id('ClaritySocialGoogle') . '">' . 'Google URL (full http://):' . '</label><input class="widefat" id="' . $this->get_field_id('ClaritySocialGoogle') . '" name="' . $this->get_field_name('ClaritySocialGoogle') . '" type="text" value="' . $ClaritySocialGoogle . '" /></p>';
		# Instagram URL
		echo '<p><label for="' . $this->get_field_id('ClaritySocialInstagram') . '">' . 'Instagram URL (full http://):' . '</label><input class="widefat" id="' . $this->get_field_id('ClaritySocialInstagram') . '" name="' . $this->get_field_name('ClaritySocialInstagram') . '" type="text" value="' . $ClaritySocialInstagram . '" /></p>';
		# Pinterest URL
		echo '<p><label for="' . $this->get_field_id('ClaritySocialPinterest') . '">' . 'Pinterest URL (full http://):' . '</label><input class="widefat" id="' . $this->get_field_id('ClaritySocialPinterest') . '" name="' . $this->get_field_name('ClaritySocialPinterest') . '" type="text" value="' . $ClaritySocialPinterest . '" /></p>';
		# Youtube URL
		echo '<p><label for="' . $this->get_field_id('ClaritySocialYoutube') . '">' . 'Youtube URL (full http://):' . '</label><input class="widefat" id="' . $this->get_field_id('ClaritySocialYoutube') . '" name="' . $this->get_field_name('ClaritySocialYoutube') . '" type="text" value="' . $ClaritySocialYoutube . '" /></p>';
		# Tumblr URL
		echo '<p><label for="' . $this->get_field_id('ClaritySocialTumblr') . '">' . 'Tumblr URL (full http://):' . '</label><input class="widefat" id="' . $this->get_field_id('ClaritySocialTumblr') . '" name="' . $this->get_field_name('ClaritySocialTumblr') . '" type="text" value="' . $ClaritySocialTumblr . '" /></p>';
		# Email URL
		echo '<p><label for="' . $this->get_field_id('ClaritySocialEmail') . '">' . 'Email Address (you@email.com):' . '</label><input class="widefat" id="' . $this->get_field_id('ClaritySocialEmail') . '" name="' . $this->get_field_name('ClaritySocialEmail') . '" type="email" value="' . $ClaritySocialEmail . '" /></p>';
	}

}// end ClaritySocialIconsWidget class

function ClaritySocialIconsWidgetInit() {
  register_widget('ClaritySocialIconsWidget');
}

add_action('widgets_init', 'ClaritySocialIconsWidgetInit');


// About box widget

class ClarityAboutBoxWidget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'ClarityAboutBoxWidget', // Base ID
			'Clarity About Box', // Name
 
			array( 'description' => __( 'A widget to display a custom about box', 'clartitytoolkit' ), ) // Args
		);
	}
 
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		$link = apply_filters( 'widget_link', $instance['link'] );
		$bio = empty($instance['bio']) ? '' : $instance['bio'];
		$image_uri = apply_filters( 'widget_image_uri', $instance['image_uri'] );
		
		echo $before_widget;
	
		if ( $title )
		echo $before_title . $title . $after_title;
		?>
			<?php if ( $image_uri !='' ) { ?>
			<img src="<?php echo esc_url($instance['image_uri']); ?>" alt="<?php echo $instance['title']; ?>" />
			<?php } ?>
			<?php if ( $bio !='' ) { ?>
			<p><?php echo $instance['bio']; ?></p>
			<?php } ?>
			<?php if ( $link !='' ) { ?>
			<p><a href="<?php echo $instance['link']; ?>" class="btn wide"><?php _e('Read More', 'clartitytoolkit'); ?></a></p>
			<?php } ?>
    <?php
		echo $after_widget;
	}
 
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['bio'] = ( ! empty( $new_instance['bio'] ) ) ? strip_tags( $new_instance['bio'] ) : '';
		$instance['link'] = ( ! empty( $new_instance['link'] ) ) ? strip_tags( $new_instance['link'] ) : '';
		$instance['image_uri'] = ( ! empty( $new_instance['image_uri'] ) ) ? strip_tags( $new_instance['image_uri'] ) : '';
		return $instance;
	}
 
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
        if ( isset( $instance[ 'image_uri' ] ) ) {
			$image_uri = $instance[ 'image_uri' ];
		}
		else {
			$image_uri = __( '', 'clartitytoolkit' );
		}
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( '', 'clartitytoolkit' );
		}
		if ( isset( $instance[ 'bio' ] ) ) {
			$bio = $instance[ 'bio' ];
		}
		else {
			$bio = __( '', 'clartitytoolkit' );
		}
		if ( isset( $instance[ 'link' ] ) ) {
			$link = $instance[ 'link' ];
		}
		else {
			$link = __( '', 'claritytoolkit' );
		}
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'claritytoolkit'); ?></label><br />
			<input type="text" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" value="<?php echo $title; ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('bio'); ?>"><?php _e('Bio:', 'clartitytoolkit'); ?></label><br />
			<textarea name="<?php echo $this->get_field_name('bio'); ?>" id="<?php echo $this->get_field_id('bio'); ?>" class="widefat"><?php echo $bio; ?></textarea>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('link'); ?>"><?php _e('Read More Link:', 'claritytoolkit'); ?></label><br />
			<input type="text" name="<?php echo $this->get_field_name('link'); ?>" id="<?php echo $this->get_field_id('link'); ?>" value="<?php echo $link; ?>" class="widefat" />
		</p>
    
		<p>
			<label for="<?php echo $this->get_field_id('image_uri'); ?>"><?php _e('Image:', 'clartitytoolkit'); ?></label><br />
			<img class="custom_media_image" src="<?php echo $image_uri; ?>" style="margin:0;padding:0;max-width:100%;float:left;display:inline-block" />
			<input type="text" class="widefat custom_media_url" name="<?php echo $this->get_field_name('image_uri'); ?>" id="<?php echo $this->get_field_id('image_uri'); ?>" value="<?php echo $image_uri; ?>">
		</p>
		<p>
			<input type="button" value="<?php _e( 'Upload Image', 'clartitytoolkit' ); ?>" class="button custom_media_upload" id="custom_image_uploader"/>
		</p>
		<?php 
	}
	
}
add_action( 'widgets_init', create_function( '', 'register_widget( "ClarityAboutBoxWidget" );' ) );

function claritytoolkit_wdScript(){
  wp_enqueue_media();
  wp_enqueue_script('clarityScript', plugins_url( '/js/image-upload-widget.js' , __FILE__ ));
}
add_action('admin_enqueue_scripts', 'claritytoolkit_wdScript');


// Popular Posts widget

class ClarityPopularPostsWidget extends WP_Widget {
    function ClarityPopularPostsWidget(){
		$widget_ops = array('description' => 'Displays Your Popular Posts');
		$control_ops = array('width' => 300, 'height' => 300);
		parent::WP_Widget(false,$name='Clarity Popular Posts',$widget_ops,$control_ops);
    }

  /* Displays the Widget in the front-end */
    function widget($args, $instance){
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? 'Post Updates' : $instance['title']);
		$PostCount = empty($instance['PostCount']) ? '' : $instance['PostCount'];

		echo $before_widget;

		if ( $title )
		echo $before_title . $title . $after_title;
?>
		<?php $my_query = new WP_Query("orderby=comment_count&showposts=$PostCount"); while ($my_query->have_posts()) : $my_query->the_post(); $do_not_duplicate = $post->ID; ?>
		<div class="clarity-popular-posts">
			<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'popularposts' ); ?></a>
			<p class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p>
			<p class="info"><?php the_time('m/d/Y'); ?> | <?php comments_popup_link('0 Comments','1 Comment','% Comments'); ?></p>
		</div><!-- END clarity-popular-posts -->
		<?php endwhile; ?>

<?php
		echo $after_widget;
	}

  /*Saves the settings. */
    function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = stripslashes($new_instance['title']);
		$instance['PostCount'] = stripslashes($new_instance['PostCount']);

		return $instance;
	}

  /*Creates the form for the widget in the back-end. */
    function form($instance){
		//Defaults
		$instance = wp_parse_args( (array) $instance, array('title'=>'', 'PostCount'=>'', 'PostID'=>'') );

		$title = htmlspecialchars($instance['title']);
		$PostCount = htmlspecialchars($instance['PostCount']);
		$PostID = htmlspecialchars($instance['PostID']);

		# Title
		echo '<p><label for="' . $this->get_field_id('title') . '">' . 'Title:' . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>';
		# Post Update Count
		echo '<p><label for="' . $this->get_field_id('PostCount') . '">' . 'Update Count (ex: 3):' . '</label><input class="widefat" id="' . $this->get_field_id('PostCount') . '" name="' . $this->get_field_name('PostCount') . '" type="text" value="' . $PostCount . '" /></p>';	
	}

} // end ClarityPopularPostsWidget class

function ClarityPopularPostsWidgetInit() {
  register_widget('ClarityPopularPostsWidget');
}

add_action('widgets_init', 'ClarityPopularPostsWidgetInit');

?>