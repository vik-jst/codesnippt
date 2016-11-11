<?php
add_action( 'wp_enqueue_scripts', 'twentysixteen_child_scripts' );

function twentysixteen_child_scripts() {
	wp_enqueue_style( 'tortuga-style', get_template_directory_uri().'/style.css' );
}


add_action( 'init', 'twentysixteen_player_post' );
/**
 * Register a Player post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function twentysixteen_player_post() {
	$labels = array(
		'name'               => _x( 'Players', 'post type general name' ),
		'singular_name'      => _x( 'Player', 'post type singular name' ),
		'menu_name'          => _x( 'Players', 'admin menu' ),
		'name_admin_bar'     => _x( 'Player', 'add new on admin bar' ),
		'add_new'            => _x( 'Add New', 'player' ),
		'add_new_item'       => __( 'Add New Player' ),
		'new_item'           => __( 'New Player' ),
		'edit_item'          => __( 'Edit Player' ),
		'view_item'          => __( 'View Player' ),
		'all_items'          => __( 'All Players' ),
		'search_items'       => __( 'Search Players' ),
		'parent_item_colon'  => __( 'Parent Players:' ),
		'not_found'          => __( 'No players found.' ),
		'not_found_in_trash' => __( 'No players found in Trash.' )
	);

	$args = array(
		'labels'             => $labels,
        'description'        => __( 'Description.' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'player' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
	);

	register_post_type( 'Player', $args );
}


// Create taxonomy for player post
add_action( 'init', 'twentysixteen_player_taxonomies', 0 );

/**
 *	create Athlete for the post type "Player"
 *
 * @link https://codex.wordpress.org/Function_Reference/register_taxonomy
 */

function twentysixteen_player_taxonomies() {
	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name'              => _x( 'Athlete', 'taxonomy general name', 'textdomain' ),
		'singular_name'     => _x( 'Athlete', 'taxonomy singular name', 'textdomain' ),
		'search_items'      => __( 'Search Athlete', 'textdomain' ),
		'all_items'         => __( 'All Athlete', 'textdomain' ),
		'parent_item'       => __( 'Parent Athlete', 'textdomain' ),
		'parent_item_colon' => __( 'Parent Athlete:', 'textdomain' ),
		'edit_item'         => __( 'Edit Athlete', 'textdomain' ),
		'update_item'       => __( 'Update Athlete', 'textdomain' ),
		'add_new_item'      => __( 'Add New', 'textdomain' ),
		'new_item_name'     => __( 'Athlete Name', 'textdomain' ),
		'menu_name'         => __( 'Athlete', 'textdomain' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'athlete' ), 
	);

	register_taxonomy( 'athlete', array( 'player' ), $args ); // athlete is name of category && player is name of post
}

add_action( 'athlete_add_form_fields', 'athlete_taxonomy_add_new_meta_field', 10, 2 );

/**
 *	create Athlete for the post type "Player"
 *	{$taxonomy_name}_add_form_fields.
 * manage_edit-{$post_type}_columns to add column
 */

function athlete_taxonomy_add_new_meta_field() {
	// this will add the custom meta field to the add new term page
	?>
	<div class="form-field">
		<label for="term_meta[custom_term_meta]"><?php _e( 'Salary', 'pippin' ); ?></label>
		<input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="">
		<p class="description"><?php _e( 'Enter a value for this field','pippin' ); ?></p>
	</div>
<?php
}

// Edit term page
add_action( 'athlete_edit_form_fields', 'athlete_taxonomy_edit_meta_field', 10, 2 );

function athlete_taxonomy_edit_meta_field($term) {
 
	// put the term ID into a variable
	$t_id = $term->term_id;
 
	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_option( "taxonomy_$t_id" ); ?>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[custom_term_meta]"><?php _e( 'Salary', 'pippin' ); ?></label></th>
		<td>
			<input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="<?php echo esc_attr( $term_meta['custom_term_meta'] ) ? esc_attr( $term_meta['custom_term_meta'] ) : ''; ?>">
			<p class="description"><?php _e( 'Enter a value for this field','pippin' ); ?></p>
		</td>
	</tr>
<?php
}

// Save extra taxonomy fields callback function.
add_action( 'edited_category', 'save_taxonomy_custom_meta', 10, 2 );  
add_action( 'create_category', 'save_taxonomy_custom_meta', 10, 2 );
function save_taxonomy_custom_meta( $term_id ) {
	if ( isset( $_POST['term_meta'] ) ) {
		$t_id = $term_id;
		$term_meta = get_option( "taxonomy_$t_id" );
		$cat_keys = array_keys( $_POST['term_meta'] );
		foreach ( $cat_keys as $key ) {
			if ( isset ( $_POST['term_meta'][$key] ) ) {
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		// Save the option array.
		update_option( "taxonomy_$t_id", $term_meta );
	}
}  

add_filter('manage_edit-athlete_columns', 'ST4_columns_head');
add_filter('manage_athlete_custom_column', 'ST4_columns_content_taxonomy', 10, 3);
function ST4_columns_head( $defaults ) {
    $defaults['first_column']  = 'First Column';
 //print_r( $defaults );
    /* ADD ANOTHER COLUMN (OPTIONAL) */
    // $defaults['second_column'] = 'Second Column';
 
    /* REMOVE DEFAULT CATEGORY COLUMN (OPTIONAL) */
    // unset($defaults['categories']);
 
    /* TO GET DEFAULTS COLUMN NAMES: */
    // print_r($defaults);
 
    return $defaults;
}
// TAXONOMIES: CATEGORIES (POSTS AND LINKS), TAGS AND CUSTOM TAXONOMIES
function ST4_columns_content_taxonomy( $c, $column_name, $term_id ) {
   /// if ($column_name == 'first_column') {
        echo 'The term ID is: ' . $term_id;
    //}
}

add_action( 'init' , 'add_promotional_text' );
function add_promotional_text() {
	//global $post;
	//echo '<pre>';
	print_r( get_post() );
	die();
	//print_r( get_the_terms( 4, 'athlete' ) );
	//$terms = get_terms( array(
	  //  'taxonomy' => 'athlete',
	    //'hide_empty' => true,
	//) );
	//print_r( $terms );
	print_r( the_terms( 4, 'athlete' ) );
 	die();
}