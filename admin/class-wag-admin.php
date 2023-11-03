<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.orionorigin.com
 * @since      1.0.0
 *
 * @package    Wag
 * @subpackage Wag/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wag
 * @subpackage Wag/admin
 * @author     ORION <help@orionorigin.com>
 */
class Wag_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wag_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wag_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wag-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wag_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wag_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wag-admin.js', array( 'jquery' ), $this->version, false );
         wp_enqueue_script("o-admin", plugin_dir_url(__FILE__) . 'js/o-admin.js', array('jquery', 'jquery-ui-sortable'), $this->version, false);

	}
    
    public function get_max_input_vars_php_ini() {
        $total_max_normal = ini_get('max_input_vars');
        $msg = __("Your max input var is <strong>$total_max_normal</strong> but this page contains <strong>{nb}</strong> fields. You may experience a lost of data after saving. In order to fix this issue, please increase <strong>the max_input_vars</strong> value in your php.ini file.", "vpc");
        ?> 
        <script type="text/javascript">
            var o_max_input_vars = <?php echo $total_max_normal; ?>;
            var o_max_input_msg = "<?php echo $msg; ?>";
        </script>         
        <?php
    }
    
            /**
     * Register the attributes group custom post type
     */
    public function register_cpt_attr_group() {

        $labels = array(
            'name' => _x('Attributes Group', 'wag'),
            'singular_name' => _x('Attributes Group', 'wag'),
            'add_new' => _x('Add Group', 'wag'),
            'add_new_item' => _x('Add Group', 'wag'),
            'edit_item' => _x('Edit Attributes Group', 'wag'),
            'new_item' => _x('Add Group', 'wag'),
            'view_item' => _x('View Attributes Group', 'wag'),
            //        'search_items' => _x('Search a group', 'wag'),
            'not_found' => _x('No Group found', 'wag'),
            'not_found_in_trash' => _x('No Group in the trash', 'wag'),
            'menu_name' => _x('Attributes Group', 'wag'),
            'all_items' => _x('Attributes Groups', 'wag'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'description' => 'Attributes Groups',
            'supports' => array('title'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=product',
            'show_in_nav_menus' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'has_archive' => false,
            'query_var' => false,
            'can_export' => true,
            'menu_icon' => 'dashicons-feedback',
        );

        register_post_type('attributes-group', $args);
    }
    
        /**
     * Adds the metabox for the attributes group CPT
     */
    public function get_config_metabox() {

        $screens = array('attributes-group');

        foreach ($screens as $screen) {

            add_meta_box(
                    'attributes-group-settings-box', __('Attributes Group', 'wag'), array($this, 'get_config_settings'), $screen
            );
            
        }
    }
    
       /**
     * Configuration CPT metabox callback
     */
    public function get_config_settings() {
        ?>
        <h4>Please select the attributes you want to add in the current group in order to quickly load them together on the product page.</h4>
    <div class='block-form attributes-group-settings'>
        
        <?php
        
        $created_attributes = array();
        
        $attribute_taxonomies = wc_get_attribute_taxonomies();
				if ( ! empty( $attribute_taxonomies ) ) {
				foreach ( $attribute_taxonomies as $tax ) {
                $attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
				$label = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name; 
                    $created_attributes[$attribute_taxonomy_name] = $label;
				}
}
            
            $begin = array(
                'type' => 'sectionbegin',
                'id' => 'attributes-group-container',
                    );
        
            $attribute =array(
                'title' => __('Attribute name', 'wag'),
                'name' => 'attribute',
                'type' => 'select',
                'options' => $created_attributes,
            );                               
            
            $attributes= array(
                'title' => __('Attributes Group', 'wag'),
                'name' => 'attributes-group',
                'type' => 'repeatable-fields',
                'id' => 'product-attributes-group-table',
                'fields' => array($attribute),
                'desc' => __('Product attributes group', 'wag'),
                'ignore_desc_col'=>true,
                'class' => 'striped',
                'add_btn_label'=> __("Add attribute", "wag")
            );
            
            $end = array('type' => 'sectionend');
        
            $settings= array(
                $begin,                
                $attributes,             
                $end
                );
        
            echo o_admin_fields($settings);
            global $o_row_templates;
                    ?>
    </div>
    <script>
        var o_rows_tpl = <?php echo json_encode($o_row_templates);?>;
    </script>
    <?php
    }
    
        
    /**
    * Saves the meta
    * @param type $post_id
    */
   public function save_group($post_id) {
       $meta_key="attributes-group";
       if(isset($_POST[$meta_key]))
       {
           update_post_meta($post_id, $meta_key, $_POST[$meta_key]);
       }
   }
    
   public function set_attributes_group(){
    global $wpdb;
        $querystr = "
    SELECT $wpdb->posts.id as ID, $wpdb->posts.post_title as title
    FROM $wpdb->posts
    WHERE $wpdb->posts.post_status = 'publish' 
    AND $wpdb->posts.post_type = 'attributes-group' ";
    
    // Array of defined attribute taxonomies
   $attributes_groups = $wpdb->get_results($querystr, OBJECT);
   $attributes_group_values = array();
        
        if ( ! empty( $attributes_groups ) ) {  
        ?>
        <div class="toolbar toolbar-top">
            <select name="attribute_group" class="attribute_group">
                <?php
								foreach ( $attributes_groups as $post ) {
									$attribute_group_id = $post->ID;
                                    $attributes = get_post_meta($attribute_group_id, 'attributes-group', true);
                                    $attributes_values = array();
                                    if(!empty($attributes)){
                                         foreach($attributes as $value){
                                        array_push($attributes_values, $value['attribute']);
                                    } $attributes_group_values[$attribute_group_id] = $attributes_values;
									$label =  $post->title;
									echo '<option value="' . $attribute_group_id . '">' . esc_html( $label ) . '</option>';
                                    }
                                   
								}  
						?>
                    <script type="text/javascript">
                        var wag_attributes_groups = <?php echo json_encode($attributes_group_values); ?>;
                    </script>
            </select>
            <button type="button" class="button" id="add_attribute_group">
                <?php _e( 'Add', 'wag' ); ?>
            </button>
        </div>
        <?php
            }
        else{
            ?>
            <div class="toolbar toolbar-top">
                <p>
                    <?php _e( 'There is no attribute group available.', 'wag' ); ?>
                </p>
            </div>
        <?php
        }
        }

    
}
