<?php

class WSUWP_People_Display_Settings {
	/**
	 * @var WSUWP_People_Display_Settings
	 *
	 * @since 0.3.0
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.3.0
	 *
	 * @return \WSUWP_People_Display_Settings
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_Display_Settings();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks to include.
	 *
	 * @since 0.3.0
	 */
	public function setup_hooks() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'added_option', array( $this, 'options_added' ), 10, 2 );
		add_action( 'updated_option', array( $this, 'options_updated' ), 10, 3 );
		add_action( 'admin_init', array( $this, 'flush_rewrite_rules_for_people_slug' ) );
	}

	/**
	 * Create an admin page for People Settings.
	 *
	 * @since 0.3.0
	 */
	public function add_settings_page() {
		add_submenu_page(
			'edit.php?post_type=' . WSUWP_People_Post_Type::$post_type_slug,
			'Display Settings',
			'Display Settings',
			'manage_options',
			'wsu-people-settings',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Register options for the People Settings page.
	 *
	 * @since 0.3.0
	 */
	public function register_settings() {
		register_setting(
			'wsu_people_settings',
			'wsu_people_display',
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'wsu_people_settings_section',
			'General',
			array( $this, 'wsu_people_settings_section' ),
			'wsu_people_settings'
		);

		add_settings_field(
			'slug',
			'URL slug',
			array( $this, 'render_slug_field' ),
			'wsu_people_settings',
			'wsu_people_settings_section',
			array( 'label_for' => 'slug' )
		);

		add_settings_field(
			'template',
			'Template',
			array( $this, 'render_template_field' ),
			'wsu_people_settings',
			'wsu_people_settings_section',
			array( 'label_for' => 'template' )
		);
	}

	public function wsu_people_settings_section() {
		?><p>These settings determine where the people directory will live on your site.</p><?php
	}

	/**
	 * Output for the directory slug field.
	 *
	 * @since 0.3.0
	 *
	 * @param array $args Extra arguments used when outputting the field.
	 */
	public function render_slug_field( $args ) {
		$options = get_option( 'wsu_people_display' );
		$value = ( isset( $options[ $args['label_for'] ] ) ) ? $options[ $args['label_for'] ] : 'people';

		?>
		<code><?php echo esc_url( trailingslashit( get_home_url() ) ); ?></code><input type="text"
				name="wsu_people_display[<?php echo esc_attr( $args['label_for'] ); ?>]"
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				value="<?php echo esc_attr( $value ); ?>" />
		<?php
	}

	/**
	 * Return a list of page templates, minus those that would be silly to use.
	 *
	 * @since 0.3.0
	 *
	 * @return array Modified array of page templates.
	 */
	public function whitelisted_templates() {
		$templates = get_page_templates();

		unset( $templates['Builder Template'] );
		unset( $templates['Section Label'] );

		return $templates;
	}

	/**
	 * Output for the directory template field.
	 *
	 * @since 0.3.0
	 *
	 * @param array $args Extra arguments used when outputting the field.
	 */
	public function render_template_field( $args ) {
		$templates = $this->whitelisted_templates();
		$options = get_option( 'wsu_people_display' );
		$value = ( isset( $options[ $args['label_for'] ] ) ) ? $options[ $args['label_for'] ] : 'page.php';

		?>
		<select name="wsu_people_display[<?php echo esc_attr( $args['label_for'] ); ?>]"
				id="<?php echo esc_attr( $args['label_for'] ); ?>">
				>
			<option value="page.php"<?php selected( $value, 'page.php' ); ?>>Default</option>
			<?php foreach ( $templates as $name => $filename ) { ?>
			<option value="<?php echo esc_attr( $filename ); ?>"<?php selected( $value, $filename ); ?>><?php echo esc_html( $name ); ?></option>
			<?php } ?>
		</select>

		<p class="description">Choose the page template for displaying the people directory.</p>
		<?php
	}

	/**
	 * Sanitize the general settings values.
	 *
	 * @since 0.3.0
	 *
	 * @param array $input Unsanitized input data.
	 *
	 * @return array The validated input data.
	 */
	public function sanitize_settings( $input ) {
		$output = array();

		foreach ( $input as $key => $value ) {
			if ( isset( $input[ $key ] ) ) {

				if ( 'slug' === $key ) {
					$output[ $key ] = sanitize_title( $value );
				}

				if ( 'template' === $key ) {
					if ( in_array( $value, $this->whitelisted_templates(), true ) ) {
						$output[ $key ] = $value;
					} else {
						$output[ $key ] = 'page.php';
					}
				}
			}
		}

		return apply_filters( 'sanitize_settings', $output, $input );
	}

	/**
	 * Flush rewrite rules if the slug option has changed.
	 *
	 * @since 0.3.0
	 *
	 * @param string $option Name of the updated option.
	 * @param mixed  $value  The option value.
	 */
	public function options_added( $option, $value ) {
		if ( 'wsu_people_display' !== $option ) {
			return;
		}

		if ( 'people' !== $value['slug'] ) {
			update_option( 'wsu_people_display_slug_updated', true );
		}
	}

	/**
	 * Flush rewrite rules if the slug option has changed.
	 *
	 * @since 0.3.0
	 *
	 * @param string $option    Name of the updated option.
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $value     The new option value.
	 */
	public function options_updated( $option, $old_value, $value ) {
		if ( 'wsu_people_display' !== $option ) {
			return;
		}

		if ( $old_value['slug'] !== $value['slug'] ) {
			update_option( 'wsu_people_display_slug_updated', true );
			flush_rewrite_rules();
		}
	}

	/**
	 * Flush rewrite rules when the slug option has changed.
	 */
	public function flush_rewrite_rules_for_people_slug() {
		if ( true === get_option( 'wsu_people_display_slug_updated' ) ) {
			flush_rewrite_rules();
			update_option( 'wsu_people_display_slug_updated', false );
		}
	}

	/**
	 * Output markup for the People Settings page.
	 *
	 * @since 0.3.0
	 */
	public function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error(
				'people_display_settings_messages',
				'people_display_settings_message',
				'Settings Saved',
				'updated'
			);
		}

		settings_errors( 'people_display_settings_messages' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wsu_people_settings' );
				do_settings_sections( 'wsu_people_settings' );
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}
}
