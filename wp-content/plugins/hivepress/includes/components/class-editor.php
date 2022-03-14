<?php
/**
 * Editor component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Implements integration with Gutenberg.
 */
final class Editor extends Component {

	/**
	 * Registered blocks.
	 *
	 * @var array
	 */
	protected $blocks;

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Register categories.
		add_filter( 'block_categories_all', [ $this, 'register_categories' ] );

		// Register blocks.
		add_action( 'init', [ $this, 'register_blocks' ] );

		if ( is_admin() ) {

			// Enqueue styles.
			add_action( 'admin_init', [ $this, 'enqueue_styles' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Checks if preview mode is enabled.
	 *
	 * @return bool
	 */
	public function is_preview() {

		/**
		 * Filters the editor preview mode status.
		 *
		 * @hook hivepress/v1/components/editor/preview
		 * @param {bool} $enabled Preview status.
		 * @return {bool} Preview status.
		 */
		return apply_filters( 'hivepress/v1/components/editor/preview', hp\is_rest() );
	}

	/**
	 * Gets blocks.
	 *
	 * @param array $container Container arguments.
	 * @return array
	 */
	protected function get_blocks( $container ) {
		$blocks = [];

		foreach ( $container['blocks'] as $name => $block ) {
			if ( is_array( $block ) ) {
				if ( isset( $block['_label'] ) ) {
					$blocks[ $name ] = $block;
				} elseif ( isset( $block['blocks'] ) ) {
					$blocks = array_merge( $blocks, $this->get_blocks( $block ) );
				}
			}
		}

		return $blocks;
	}

	/**
	 * Registers block categories.
	 *
	 * @param array $categories Block categories.
	 * @return array
	 */
	public function register_categories( $categories ) {
		$categories[] = [
			'title' => hivepress()->get_name(),
			'slug'  => 'hivepress',
		];

		return $categories;
	}

	/**
	 * Registers blocks.
	 */
	public function register_blocks() {

		// Get blocks.
		$blocks = [];

		foreach ( hivepress()->get_classes( 'blocks' ) as $block_type => $block ) {
			if ( $block::get_meta( 'label' ) ) {

				// Get slug.
				$block_slug = hp\sanitize_slug( $block_type );

				// Add block.
				$blocks[ $block_type ] = [
					'title'      => $block::get_meta( 'label' ),
					'type'       => 'hivepress/' . $block_slug,
					'script'     => 'hivepress-block-' . $block_slug,
					'attributes' => [],
					'settings'   => [],
				];

				foreach ( $block::get_meta( 'settings' ) as $field_name => $field ) {

					// Get field arguments.
					$field_args = $field->get_args();

					if ( isset( $field_args['options'] ) ) {
						if ( is_array( hp\get_first_array_value( $field_args['options'] ) ) ) {
							$field_args['options'] = wp_list_pluck( $field_args['options'], 'label' );
						}

						if ( ! hp\get_array_value( $field_args, 'required', false ) && ! isset( $field_args['options'][''] ) ) {
							$field_args['options'] = [ '' => '&mdash;' ] + $field_args['options'];
						}
					}

					// Add attribute.
					$blocks[ $block_type ]['attributes'][ $field_name ] = [
						'type'    => 'string',
						'default' => hp\get_array_value( $field_args, 'default', '' ),
					];

					// Add setting.
					$blocks[ $block_type ]['settings'][ $field_name ] = $field_args;
				}
			}
		}

		// Register blocks.
		if ( function_exists( 'register_block_type' ) ) {
			foreach ( $blocks as $block_type => $block ) {

				// Register block script.
				wp_register_script( $block['script'], hivepress()->get_url() . '/assets/js/block.min.js', [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ], hivepress()->get_version(), true );
				wp_add_inline_script( $block['script'], 'var hivepressBlock = ' . wp_json_encode( $block ) . ';', 'before' );

				// Register block type.
				register_block_type(
					$block['type'],
					[
						'editor_script'   => $block['script'],
						'render_callback' => [ $this, 'render_' . $block_type ],
						'attributes'      => $block['attributes'],
					]
				);
			}

			if ( $blocks ) {
				wp_localize_script( hp\get_array_value( hp\get_first_array_value( $blocks ), 'script' ), 'hivepressBlocks', $blocks );
			}
		}

		// Add shortcodes.
		if ( function_exists( 'add_shortcode' ) ) {
			foreach ( array_keys( $blocks ) as $block_type ) {
				add_shortcode( 'hivepress_' . $block_type, [ $this, 'render_' . $block_type ] );
			}
		}
	}

	/**
	 * Catches calls to undefined methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @throws \BadMethodCallException Invalid method.
	 * @return string
	 */
	public function __call( $name, $args ) {
		if ( strpos( $name, 'render_' ) === 0 ) {
			$output = ' ';

			// Get block type.
			$block_type = substr( $name, strlen( 'render_' ) );

			// Create block.
			$block = hp\create_class_instance( '\HivePress\Blocks\\' . $block_type, [ (array) hp\get_first_array_value( $args ) ] );

			if ( $block ) {

				// Render block.
				$output .= $block->render();
			}

			return $output;
		}

		throw new \BadMethodCallException();
	}

	/**
	 * Enqueues editor styles.
	 */
	public function enqueue_styles() {
		foreach ( hivepress()->get_config( 'styles' ) as $style ) {
			if ( in_array( 'editor', (array) hp\get_array_value( $style, 'scope' ), true ) ) {
				add_editor_style( $style['src'] );
			}
		}
	}
}
