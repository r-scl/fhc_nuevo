<?php
/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PUM_RC_Shortcode_RemoteContent
 *
 * Registers the pum_remote_content shortcode.
 */
class PUM_RC_Shortcode_RemoteContent extends PUM_Shortcode {

	/**
	 * @var int
	 */
	public $version = 2;

	public $has_content = true;

	/**
	 * The shortcode tag.
	 *
	 * @return string
	 */
	public function tag() {
		return 'pum_remote_content';
	}

	/**
	 * @return string
	 */
	public function label() {
		return __( 'Remote Content Area', 'popup-maker-remote-content' );
	}

	/**
	 * @return string
	 */
	public function description() {
		return __( 'Inserts the dynamic content area for remote content.', 'popup-maker-remote-content' );
	}

	/**
	 * @return array
	 */
	public function post_types() {
		return array( 'popup' );
	}

	/**
	 * @return array
	 */
	public function fields() {

		$post_types = PUM_Admin_Helpers::post_type_dropdown_options( array( '_builtin' => false, 'publicly_queryable' => true ), 'or' );

		foreach ( $post_types as $key => $value ) {
			if ( in_array( $key, array( 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'popup_theme', 'nf_sub' ) ) ) {
				unset( $post_types[ $key ] );
			}
		}

		return array(
			'general' => array(
				'main' => array(
					'method'                => array(
						'label'    => __( 'Choose the method used to load dynamic content.', 'popup-maker-remote-content' ),
						'desc'     => sprintf( __( '%sNote:%s Iframe is the only available method for external urls.', 'popup-maker-remote-content' ), '<strong>', '</strong>' ),
						'type'     => 'select',
						'std'      => 'load',
						'options'  => array(
							'load'   => __( 'Load From URL', 'popup-maker-remote-content' ),
							'iframe' => __( 'IFrame', 'popup-maker-remote-content' ),
							'posts'  => __( 'Post Type Content (experimental)', 'popup-maker-remote-content' ),
							'ajax'   => __( 'AJAX', 'popup-maker-remote-content' ),
						),
						'doclink' => 'https://docs.wppopupmaker.com/article/30-remote-content-setup-documentation',
						'priority' => 10,
					),
					'load_css_selector'     => array(
						'label'        => __( 'Content CSS Selector', 'popup-maker-remote-content' ),
						'placeholder'  => __( '#main .content', 'popup-maker-remote-content' ),
						'desc'         => __( 'Enter a CSS selector to filter what content will be loaded from url.', 'popup-maker-remote-content' ),
						'dependencies' => array(
							'method' => 'load',
						),
						'priority'     => 20,
					),
					'iframe_default_source' => array(
						'label'        => __( 'Default IFrame URL', 'popup-maker-remote-content' ),
						'desc'         => __( 'Will be shown by default with normal triggers open this popup.', 'popup-maker-remote-content' ),
						'dependencies' => array(
							'method' => 'iframe',
						),
						'priority'     => 30,
					),
					'posts_post_type'       => array(
						'label'        => __( 'Post Type', 'popup-maker-remote-content' ),
						'type'         => 'select',
						'desc'         => __( 'Which post type do you want data to come from?', 'popup-maker-remote-content' ),
						'options'      => $post_types,
						'dependencies' => array(
							'method' => 'posts',
						),
						'priority'     => 40,
					),
					'ajax_function_name'    => array(
						'label'        => __( 'Function Name', 'popup-maker-remote-content' ),
						'placeholder'  => __( 'my_custom_function_name', 'popup-maker-remote-content' ),
						'desc'         => __( 'A PHP function that will be called to render results. Name only, no ().', 'popup-maker-remote-content' ),
						'dependencies' => array(
							'method' => 'ajax',
						),
						'priority'     => 60,
					),
					'loading_icon'          => array(
						'label'    => __( 'Loading Icon Style', 'popup-maker-remote-content' ),
						'type'     => 'select',
						'std'      => 'lines-1',
						'options'  => array(
							'lukehass/load1' => __( 'Lines: Growing', 'popup-maker-remote-content' ),
							'lukehass/load2' => __( 'Dots: Growing', 'popup-maker-remote-content' ),
							'lukehass/load3' => __( 'Circles: Streaking', 'popup-maker-remote-content' ),
							'lukehass/load4' => __( 'Circles: Chasing Tail', 'popup-maker-remote-content' ),
							'lukehass/load5' => __( 'Circles: Dots Chasing', 'popup-maker-remote-content' ),
							'lukehass/load6' => __( 'Circles: Dots Fading', 'popup-maker-remote-content' ),
							'lukehass/load7' => __( 'Circles: Dots Streaking', 'popup-maker-remote-content' ),
							'lukehass/load8' => __( 'Circles: Racetrack', 'popup-maker-remote-content' ),
							'custom_1'       => __( 'Custom #1', 'popup-maker-remote-content' ),
							'custom_2'       => __( 'Custom #2', 'popup-maker-remote-content' ),
							'custom_3'       => __( 'Custom #3', 'popup-maker-remote-content' ),
						),
						'priority' => 70,
					),
					'min_height'            => array(
						'label'    => __( 'Minimum Height', 'popup-maker-remote-content' ),
						'type'     => 'rangeslider',
						'std'      => '200',
						'unit'     => 'px',
						'priority' => 80,
					),
				),
			),
		);
	}

	/**
	 * Shortcode handler
	 *
	 * @param  array  $atts    shortcode attributes
	 * @param  string $content shortcode content
	 *
	 * @return string
	 */
	public function handler( $atts, $content = null ) {
		$atts = $this->shortcode_atts( $atts );

		$settings = array(
			'method'     => $atts['method'],
			'min_height' => intval( $atts['min_height'] ),
		);

		if ( in_array( $atts['loading_icon'], array( 'custom_1', 'custom_2', 'custom_3' ) ) ) {
			$id = str_replace( 'custom_', '', $atts['loading_icon'] );

			$loading_icon = pum_get_option( 'rc_custom_icon_' . $id . '_html', '' );
			$loading_icon = PUM_RC_Loaders::generate_custom_icon( $loading_icon, 'custom-' . $id );

		} else {
			$loader = explode( '/', $atts['loading_icon'] );

			$loader_group = $loader[0];
			$loader_icon  = $loader[1];
			$loading_icon = PUM_RC_Loaders::generate_icon( $loader_icon, $loader_group );
		}
		/*

		*/

		$template = false;

		switch ( $atts['method'] ) {
			case 'load':
				$settings['load_css_selector'] = $atts['load_css_selector'];
				break;
			case 'iframe':
				$settings['iframe_default_source'] = $atts['iframe_default_source'];
				break;
			case 'posts':
				$settings['posts_post_type'] = $atts['posts_post_type'];

				$content = html_entity_decode( $content );

				$allowed_html = array(
					'a'      => array(
						'id'    => array(),
						'class' => array(),
						'href'  => array(),
						'title' => array(),
					),
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'div'    => array(
						'id'    => array(),
						'class' => array(),
					),
					'p'      => array(
						'id'    => array(),
						'class' => array(),
					),
				);

				$template = wp_kses( $content, $allowed_html );

				break;
			case 'ajax':
				$settings['ajax_function_name'] = $atts['ajax_function_name'];
				break;
		}

		ob_start(); ?>

		<div class="pum-rc-box" data-settings="<?php echo esc_js( json_encode( $settings ) ); ?>">
			<?php if ( $template ) : ?>
				<script type="text/html" class="pum-rc-content-template">
					<?php echo $template; ?>
				</script>
			<?php endif; ?>

			<?php echo $loading_icon; ?>

			<div class="pum-rc-content-area">
				<?php if ( $settings['method'] === 'iframe' ) : ?>
					<div class="pum-remote-content-frame">
						<iframe scrolling="no" sandbox="allow-forms allow-scripts allow-same-origin allow-pointer-lock"></iframe>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<?php

		return ob_get_clean();
	}

	/**
	 * @return bool|string
	 */
	public function template_styles() { ?>
		.pum-rc-content-area {
		border: 1px dotted #ccc;
		padding: 5px 10px;
		display: block;
		}

		.pum-rc-content-area i {
		display: inline-block;
		vertical-align: middle;
		line-height: 20px;
		width: 20px;
		height: 20px;
		text-align: center;
		margin: 0;
		padding: 0;
		background: url(<?php echo Popup_Maker::$URL . 'assets/images/admin/popup-maker-icon.png'; ?>) no-repeat center center transparent;
		background-size: contain;
		}
		<?php
	}

	/**
	 * @return bool|string
	 */
	public function template() { ?>
		<div class="pum-rc-content-area">
			<i></i>
			<small>
				<?php _e( 'Dynamic/remote content area', 'popup-maker-remote-content' ); ?>
			</small>
		</div>
		<?php
	}

	/**
	 * @return array
	 */
	public function inner_content_labels() {
		return array(
			'label'       => __( 'Content Template', 'popup-maker-remote-content' ),
			'description' => __( 'Include our template tags to render custom html content, or run your own shortcodes here.', 'popup-maker-remote-content' ),
		);
	}

	/**
	 * Used internally to modify the inner content field to our needs here.
	 *
	 * @return array
	 */
	public function _fields() {
		$fields = parent::_fields();


		$fields[ $this->inner_content_section ]['main']['_inner_content'] = array_merge( $fields[ $this->inner_content_section ]['main']['_inner_content'], array(
			'allow_html'   => true,
			'dependencies' => array(
				'method' => 'posts',
			),
			'std'          => "<div id='post-{pum_rc_post_id}'>\r\n	<h1>{pum_rc_post_title}</h1>\r\n	<div>{pum_rc_post_content}</div>\r\n</div>",
			'priority'     => 50,
		) );

		$fields = PUM_Admin_Helpers::parse_tab_fields( $fields, array(
			'has_subtabs' => $this->version >= 2,
			'name'        => 'attrs[%s]',
		) );

		return $fields;
	}
}

