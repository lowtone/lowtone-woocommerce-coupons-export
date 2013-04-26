$ = jQuery

$ ->
	$('<option>')
		.html(lowtone_woocommerce_coupons_export.action_title)
		.val('export_coupons')
		.appendTo $('select[name="action"]')