((w, $) => {
	'use strict';

	w.giftflow = {};
	w.giftflow.testSendMail_Handle = async( name ) => {
		let r = confirm( 'Are you sure you want to send the test email?' );
		if ( ! r) {
			return;
		}

		const res = await $.ajax(
			{
				url: giftflow_admin.ajax_url,
				type: 'POST',
				data: {
					action: 'giftflow_test_send_mail',
					name,
					nonce: giftflow_admin.nonce,
				},
				error: (xhr, status, error) => {
					console.error( status, error );
				}
			}
		)

		console.log(res);
	}

})( window, jQuery )