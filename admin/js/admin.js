import './modules/test-send-mail';
import './modules/dashboard-view';
import './modules/custom-fields';

((w, $) => {
  'use strict';


  /**
   * Handle first activation notice
   * @returns {void}
   */
  const firstActivationNotice = () => {
    const notice = $( '.giftflow-first-activation-notice' );
    if ( notice.length === 0 ) {
      return;
    }

    let nonce = notice.data( 'nonce' );
    if ( ! nonce ) {
      return;
    }

    notice.on( 'click', '.notice-dismiss', () => {
        $.post( ajaxurl, { action: 'giftflow_dismiss_first_activation_notice', _giftflow_nonce: nonce } );
    } );
  }

  // window load
  $(document).on( 'DOMContentLoaded', () => {
    firstActivationNotice();
  });

})(window, jQuery)