<?php
/**
 * Listing view page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing page in view context.
 */
class Listing_View_Page extends Page_Sidebar_Right {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_columns' => [
						'attributes' => [
							'class' => [ 'hp-listing', 'hp-listing--view-page' ],
						],
					],

					'page_topbar'  => [
						'_order'     => 30,

						'attributes' => [
							'class' => [ 'hp-page__topbar--separate' ],
						],

						'blocks'     => [
							'listing_manage_menu'       => [
								'type'       => 'menu',
								'menu'       => 'listing_manage',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-menu--tabbed' ],
								],
							],

							'listing_actions_secondary' => [
								'type'       => 'container',
								'optional'   => true,
								'blocks'     => [],
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing__actions', 'hp-listing__actions--secondary' ],
								],
							],
						],
					],

					'page_content' => [
						'blocks' => [
							'listing_title'                => [
								'type'       => 'container',
								'tag'        => 'h1',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-listing__title' ],
								],

								'blocks'     => [
									'listing_title_text' => [
										'type'   => 'part',
										'path'   => 'listing/view/page/listing-title',
										'_order' => 10,
									],

									'listing_verified_badge' => [
										'type'   => 'part',
										'path'   => 'listing/view/listing-verified-badge',
										'_order' => 20,
									],
								],
							],

							'listing_details_primary'      => [
								'type'       => 'container',
								'optional'   => true,
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
								],

								'blocks'     => [
									'listing_category'     => [
										'type'   => 'part',
										'path'   => 'listing/view/listing-categories',
										'_order' => 10,
									],

									'listing_created_date' => [
										'type'   => 'part',
										'path'   => 'listing/view/listing-created-date',
										'_order' => 20,
									],
								],
							],

							'listing_images'               => [
								'type'   => 'part',
								'path'   => 'listing/view/page/listing-images',
								'_order' => 40,
							],

							'listing_attributes_secondary' => [
								'type'   => 'part',
								'path'   => 'listing/view/page/listing-attributes-secondary',
								'_order' => 50,
							],

							'listing_description'          => [
								'type'   => 'part',
								'path'   => 'listing/view/page/listing-description',
								'_order' => 60,
							],
						],
					],

					'page_sidebar' => [
						'attributes' => [
							'data-component' => 'sticky',
						],

						'blocks'     => [
							'listing_attributes_primary' => [
								'type'   => 'part',
								'path'   => 'listing/view/page/listing-attributes-primary',
								'_order' => 10,
							],

							'listing_actions_primary'    => [
								'type'       => 'container',
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary', 'hp-widget', 'widget' ],
								],

								'blocks'     => [
									'listing_report_modal' => [
										'type'        => 'modal',
										'title'       => hivepress()->translator->get_string( 'report_listing' ),
										'_capability' => 'read',

										'blocks'      => [
											'listing_report_form' => [
												'type'   => 'form',
												'form'   => 'listing_report',
												'_order' => 10,

												'attributes' => [
													'class' => [ 'hp-form--narrow' ],
												],
											],
										],
									],

									'listing_report_link'  => [
										'type'   => 'part',
										'path'   => 'listing/view/page/listing-report-link',
										'_order' => 1000,
									],
								],
							],

							'listing_vendor'             => [
								'type'     => 'template',
								'template' => 'vendor_view_block',
								'_order'   => 30,
							],

							'page_sidebar_widgets'       => [
								'type'   => 'widgets',
								'area'   => 'hp_listing_view_sidebar',
								'_order' => 100,
							],
						],
					],

					'page_footer'  => [
						'blocks' => [
							'related_listings_container' => [
								'type'   => 'section',
								'title'  => hivepress()->translator->get_string( 'related_listings' ),
								'_order' => 10,

								'blocks' => [
									'related_listings' => [
										'type'    => 'related_listings',
										'columns' => 3,
										'_order'  => 10,
									],
								],
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
