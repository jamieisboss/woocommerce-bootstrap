<?php
/**
 * Bootstrap Integration.
 *
 * @package  WC_Integration_Bootstrap_Integration
 * @category Integration
 * @author   SolveHQ
 */

if ( ! class_exists( 'WC_Integration_Bootstrap_Integration' ) ) :

class WC_Integration_Bootstrap_Integration extends WC_Integration {

	/**
	 * Init and hook in the integration.
	 */
	public function __construct() {
		global $woocommerce;

		$this->id                 = 'Bootstrap Integration';
		$this->method_title       = __( 'Bootstrap Integration', 'woocommerce-bootstrap-integration' );
		$this->method_description = __( 'An integration of the bootstrap responsive grid into woocommerce.', 'woocommerce-bootstrap-integration' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->number_of_columns          = $this->get_option( 'number_of_columns' );
		$this->bootstrap_version          = $this->get_option( 'bootstrap_version' );
		//$this->image_size                 = $this->get_option( 'image_size' );

		// Actions.
		add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );

	}

	/**
	 * Initialize integration settings form fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'number_of_columns' => array(
				'title'             => __( 'Number of Columns', 'woocommerce-bootstrap-integration' ),
				'type'              => 'select',
				'description'       => __( 'Choose the number of columns.', 'woocommerce-bootstrap-integration' ),
				'desc_tip'          => true,
				'default'           => '',
				'options'           => array(1,2,3,4,5,6)
			),
			'bootstrap_version' => array(
				'title'             => __( 'Bootstrap Version', 'woocommerce-bootstrap-integration' ),
				'type'              => 'select',
				'description'       => __( 'Choose the version of bootstrap you are using.', 'woocommerce-bootstrap-integration' ),
				'desc_tip'          => true,
				'default'           => '',
				'options'           => array(
				    
				            2 => 'Bootstrap 2.x',
				            3 => 'Bootstrap 3.x'
				            )
			),
			
			
		);
	}


	/**
	 * Generate Button HTML.
	 */
	public function generate_button_html( $key, $data ) {
		$field    = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'class'             => 'button-secondary',
			'css'               => '',
			'custom_attributes' => array(),
			'desc_tip'          => false,
			'description'       => '',
			'title'             => '',
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<button class="<?php echo esc_attr( $data['class'] ); ?>" type="button" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php echo $this->get_custom_attribute_html( $data ); ?>><?php echo wp_kses_post( $data['title'] ); ?></button>
					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}








	


}

endif;