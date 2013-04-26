<?php
/*
 * Plugin Name: Export Coupons
 * Plugin URI: http://wordpress.lowtone.nl/plugins/woocommerce-coupons-export/
 * Description: Download a list of coupon codes.
 * Version: 1.0
 * Author: Lowtone <info@lowtone.nl>
 * Author URI: http://lowtone.nl
 * License: http://wordpress.lowtone.nl/license
 */
/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2012, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 * @package wordpress\plugins\lowtone\woocommerce\coupons\export
 */

namespace lowtone\woocommerce\coupons\export {

	use lowtone\content\packages\Package,
		lowtone\woocommerce\coupons\Coupon,
		lowtone\types\datetime\DateTime,
		lowtone\util\CSV;

	// Includes
	
	if (!include_once WP_PLUGIN_DIR . "/lowtone-content/lowtone-content.php") 
		return trigger_error("Lowtone Content plugin is required", E_USER_ERROR) && false;

	Package::init(array(
			Package::INIT_PACKAGES => array("lowtone", "lowtone\\woocommerce"),
			Package::INIT_MERGED_PATH => __NAMESPACE__,
			Package::INIT_SUCCESS => function() {

				// This doesn't work; extra actions cannot yet be added.

				/*add_filter("bulk_actions-edit-shop_coupon", function($actions) {
					$actions["export_coupons"] = __("Export Coupons", "lowtone_woocommerce_coupons_export");

					return $actions;
				});*/

				// Fixed with JS
				
				add_action("load-edit.php", function() {
					$screen = get_current_screen();

					if ("shop_coupon" != $screen->post_type)
						return;

					wp_enqueue_script("lowtone_woocommerce_coupons_export", plugins_url("/assets/scripts/jquery.coupons-export.js", __FILE__), array("jquery"));
					wp_localize_script("lowtone_woocommerce_coupons_export", "lowtone_woocommerce_coupons_export", array(
							"action_title" => __("Export Coupons", "lowtone_woocommerce_coupons_export")
						));

					// Do action
					
					$listTable = _get_list_table("WP_Posts_List_Table");

  					$action = $listTable->current_action();
  					
  					if ("export_coupons" !== $action) 
  						return;

  					if (!isset($_REQUEST["post"]))
  						return;

  					$csv = new CSV();

  					Coupon::findById($_REQUEST["post"])
						->each(function($coupon) use (&$csv) {
							$csv->append(array(
									$coupon->code
								));
						});

  					header("Content-Type: application/octet-stream");
  					header(sprintf('Content-Disposition: attachment; filename="%s"', "coupon_export-" . DateTime::now()->format("YmdHis") . ".csv"));

  					echo (string) $csv;

  					exit;
				});

				// Register textdomain
				
				add_action("plugins_loaded", function() {
					load_plugin_textdomain("lowtone_woocommerce_coupons_export", false, basename(__DIR__) . "/assets/languages");
				});

			}
		));

}