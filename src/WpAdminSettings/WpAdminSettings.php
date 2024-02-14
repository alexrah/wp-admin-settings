<?php
/**
 * Created by PhpStorm.
 * User: ale
 * Date: 2020-04-14
 * Time: 22:20
 */

namespace WpAdminSettings;

class WpAdminSettings {

	/**
	 * @var string
	*/
	private string $label;

	/**
	 * @var string
	 */
	private string $options_name;

	/**
     * @var array
	*/
    private array $options_stored;

	/**
	 * @var string
	*/
	private string $capability;

	/**
	 * @var array{name:string, type:string, label:string, required:bool, admin_only:bool, params: array}[]
	*/
	private array $options;

	/**
     * @var string
	*/
    private string $page_description;


	/**
	 * @param array{label:string, options_name: string, capability: string} $args label, options_name, capability
	 * @param array{name:string, type:string, label:string, required:bool, admin_only:bool, params: array}[] $options
	 * @return bool
	*/
	public function __construct(array $args = [],array $options = []) {

		$args_default = array(
			'label'      => 'Impostazione Personalizzate',
			'options_name' => 'alx-admin-settings',
			'capability' => 'edit_posts',
		);

		$args = array_merge($args_default,$args);

		if( empty($args['options_name']) ) return false;

		$this->label          = $args['label'];
		$this->options_name   = $args['options_name'];
		$this->options_stored = get_option( $this->options_name );
        $this->capability     = $args['capability'];
        $this->page_description = '';

        $this->set_options($options);

		return true;
	}

	/**
	 * @return array{name:string, type:string, label:string, required:bool, admin_only:bool, params: array}[]
	 */
	public function get_options(): array {
		return $this->options;
	}

	/**
	 * @param array[] $options
     * @return void
	 */
	private function set_options( array $options ):void {

		foreach ($options as &$option){

			$option = $this->set_option_defaults($option);

		}

	    $this->options = $options;
	}

	/**
     * Fill empty keys with default values
     * @param array $option
     * @return bool|WpAdminOptionData
	*/
	private function set_option_defaults( array $option ): bool|WpAdminOptionData {

		$option_default = array(
			'name' => '',
			'type' => 'text',
			'label' => '',
			'required' => false,
			'admin_only' => true,
			'params' => array()
		);

		$option = (object)array_merge($option_default, $option);
		/**
		 * @var WpAdminOptionData $option
		 */
		if( empty($option->name) ) { return false; }
		if( empty($option->label) ) { $option->label = $option->name; }
		return $option;

	}

	/**
	 * @param array{name:string, type:string, label:string, required:bool, admin_only:bool, params: array} $option
     * @return bool false if $option['name'] not defined
	 */
	public function set_option( array $option ): bool {

	    $option = $this->set_option_defaults($option);
	    if(!$option){ return false; }
	    array_push($this->options, $option);
	    return true;

	}

	/**
     * @param string $page_description
     * @return void
	*/
	public function set_page_description(string $page_description):void{
		/* return false if $description is not HTML */
//	    if( $description == strip_tags($description) ) { return false; }
		$this->page_description = $page_description;

	}

	public function create(): void {
		add_action( 'admin_menu', array($this, 'create_admin_menu') );
		add_action( 'admin_init', array($this, 'create_settings') );
		add_filter( 'option_page_capability_' . $this->options_name, function ($capability){
			return $this->capability;
		} );
	}


	public function create_admin_menu(): void {
		add_menu_page($this->label, $this->label, $this->capability, $this->options_name, array($this, 'create_options_page'), "dashicons-admin-site", 99);
	}

	public function create_settings(): void {


		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'media-upload' );


		register_setting( $this->options_name, $this->options_name );
		add_settings_section('default', '','',$this->options_name);


		/**
		 * @var WpAdminOptionData $option
		 */
		foreach ($this->options as $option){

		    $callback = 'render_'. $option->type .'_field';
			add_settings_field($option->name, $option->label, array($this,$callback) ,$this->options_name,'default', (array)$option );

        }

	}
	
	public function create_options_page(): void { ?>
		<div class="settings-description"><?php echo $this->page_description; ?></div>
        <form id="setting-form" class="setting-form-v2media" action='options.php' method='post'>
			<?php
			settings_fields( $this->options_name );
			do_settings_sections( $this->options_name );
			submit_button();
			?>
		</form>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.1/public/assets/styles/choices.min.css" />
        <style>

            /*================================
			CHOICES OVERRIDES
			================================*/


            .choices__inner {
                min-height: 34px!important;
                height: 34px!important;
                padding: 0!important;
            }
            .choices__list--single {
                padding: 1px 16px 4px 4px!important;
            }

            .choices__placeholder {
                opacity: 1!important;
            }

            .choices {
                margin-bottom: 15px!important;
            }

            .choices__list--dropdown {
                z-index: 2!important;
            }

            select.choices__input {
                display: block!important;
                position: absolute;
                width: 0!important;
                height: 0;
                opacity: 0;
            }

            .choices select[hidden] {
                display: block!important;
            }

        </style>
        <script type="module">

            import Choices from 'https://cdn.skypack.dev/choices.js@10.1';

            const lChoicesSelectors = document.querySelectorAll('.choices-container');

            console.log(lChoicesSelectors);
            for (const [key,element] of lChoicesSelectors.entries()){
                console.log(element);
                new Choices(element,{

                });
            }

        </script>

		<script type="text/javascript">

            jQuery(document).ready(function () {

                jQuery('.wpss_upload_image_button').click(function() {

                    formfield = jQuery(this).prev().attr('name');
                    tb_show('', 'media-upload.php?type=image&TB_iframe=true');
                    return false;
                });

                window.send_to_editor = function(html) {
                    console.log(formfield);
                    console.log(jQuery(html));
                    imgurl = jQuery(html).attr('src');
                    console.log('imgurl',imgurl);

                    jQuery('input[name="'+formfield+'"]').val(imgurl);
                    tb_remove();

                    jQuery('input[name="'+formfield+'"]').prev().html("<img height='65' src='" + imgurl + "'/>");
                }

            });

		</script>

		<?php
	}

	/**
	 *
	 * @param string $options_name
	 * @param string|null $option_id
	 * @return mixed Value set for the option
	 * */
	public static function get_stored_option( string $options_name, string|null $option_id = null):mixed {

		$options = get_option( $options_name );
		if ( isset( $options[ $option_id ] ) ) {
			return $options[ $option_id ];
		} elseif(!$option_id) {
	        return $options;
        } else {
		    return false;
        }
    }


	/**
	 * @param array $option
	 */
	public function render_checkbox_field( array $option  ): void {
		/**
		 * @var WpAdminOptionData $option
		 */
		$option = (object)$option;
		$required = ($option->required)?"required":"";
		$disabled = ($option->admin_only && !current_user_can('update_core'))?'disabled':'';
		$value = (isset($this->options_stored[$option->name]))?$this->options_stored[$option->name]:'';
		$checked = ($value == "on")?"checked":"";
		echo '<input '. $disabled .' '.$checked.' class="'.$required.'"   type="checkbox" name="'.$this->options_name.'['.$option->name.']">';
	}

	/**
     * @param array $option
	*/
	public function render_text_field( array $option  ): void {

	    /**
         * @var WpAdminOptionData $option
	    */
	    $option = (object)$option;
		$required = ($option->required)?"required":"";
		$disabled = ($option->admin_only && !current_user_can('update_core'))?'disabled':'';
		$value = (isset($this->options_stored[$option->name]))?$this->options_stored[$option->name]:'';

		echo '<input '. $disabled .' type="text"  class="'.$required.'" name="'.$this->options_name.'['.$option->name.']" value="'. esc_attr($value) .'"   >';
	}


	/**
	 * @param array $option
	 */
	public function render_textarea_field( array $option  ): void {

		/**
		 * @var WpAdminOptionData $option
		 */
		$option = (object)$option;
		$required = ($option->required)?"required":"";
		$disabled = ($option->admin_only && !current_user_can('update_core'))?'disabled':'';
		$value = (isset($this->options_stored[$option->name]))?$this->options_stored[$option->name]:'';

		echo '<textarea '. $disabled .' class="'.$required.'" name="'.$this->options_name.'['.$option->name.']" style="width: 100%; height: 200px;">'. esc_attr($value) .'</textarea>';
	}

	/**
	 * @param array $option
	 */
	public function render_select_field( array $option ): void {

		/**
		 * @var WpAdminOptionData $option
		 */
		$option = (object)$option;
		$required = ($option->required)?"required":"";
		$disabled = ($option->admin_only && !current_user_can('update_core'))?'disabled':'';
		$type = $option->params['options'];
		$post_types = get_post_types();
		$registered_taxonomies = get_taxonomies();
		$items = array();
		$multi = ($option->params['multi'])?$option->params['multi']:false;
		if( $multi ){
			echo '<select '. $disabled .'  class="choices-container "'. $required .'" multiple="multiple" data-type="'. $type .'" name="'. $this->options_name .'['. $option->name .'][]">';
		} else {
			echo '<select '. $disabled .' class="choices-container "'. $required .'" data-type="'. $type .'" name="'. $this->options_name .'['. $option->name .']">';
		}
		echo '<option value="">--</option>';
		if(in_array($type,$post_types)) { //se si tratta di un post_type....

		    $items = get_posts( array( 'post_type' => $type, 'post_status' => 'publish', 'numberposts' => 100 ) );

		} elseif ( in_array($type,$registered_taxonomies) ) { //se un term faccio cosi..

			$items = get_terms( $type, array( 'hide_empty' => 0, ) );

		} elseif ($type == 'author'){

			$items = get_users( array('role__in' => array('administrator','contributor','editor','author')) );

		} elseif (is_array($type)){  // passing option values manually as array('value'=>'','label'=>'')

		    $items = $type;
		}

		foreach($items as $item) {

			if(isset($item->ID)){
				$item_id = $item->ID;
			} elseif (isset($item->term_id)){
				$item_id = $item->term_id;
			} else {
				$item_id = $item;
			}

			if(isset($item->post_title)){
			    $item_title = $item->post_title;
            } elseif (isset($item->name)){
			    $item_title =  $item->name;
            } elseif (isset($item->data)){
			    $item_title = $item->data->display_name;
            } else {
			    $item_title = $item;
            }

			if( $multi ){
				$selected = (isset($this->options_stored[$option->name]) && in_array($item_id,$this->options_stored[$option->name]))?'selected':'';
			} else {
				$selected = (isset($this->options_stored[$option->name]) && $item_id == $this->options_stored[$option->name])?'selected':'';
			}
			echo '<option '. $selected .' value="'. $item_id .'">'. $item_title .'</option>';
		}

		echo '</select>';
	}

	/**
	 * @param array $option
	 */
	public function render_image_field( array $option ): void {

		$option = (object)$option;
		$required = ($option->required)?"required":"";
		$disabled = ($option->admin_only && !current_user_can('update_core'))?'disabled':'';
		$value = (isset($this->options_stored[$option->name]))?$this->options_stored[$option->name]:'';

		echo '<div id="wpss_upload_image_thumb" class="wpss-file">';
		if(!empty($value) ) {
            echo '<img src="'. $value .'" width="400"/>';
		}
        echo '</div>';

		echo '<input id="wpss_upload_image" '. $disabled .' type="text"  class="wpss_text wpss-file '.$required.'" name="'.$this->options_name.'['.$option->name.']" value="'. esc_attr($value) .'"   >';
		echo '<input  type="button" value="Upload Image" class="wpss-filebtn wpss_upload_image_button" />';

    }

}