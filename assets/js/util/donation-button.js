const { ajax_url, nonce } = giftflow_common;

export default async function donationButton_Handle(el) {
  const { campaignId, campaignTitle } = el.dataset;
  
  const ajaxModal = new GiftFlowModal({
    ajax: true,
    ajaxUrl: `${ajax_url}?action=giftflow_get_campaign_donation_form&campaign_id=${campaignId}&nonce=${nonce}`,
    onLoad: (content, modal) => {
      // console.log('Content loaded:', modal);

      const donationForm = modal.contentElement.querySelector('form.donation-form');
      if(donationForm){
        new window.donationForm_Class(donationForm, {
          paymentMethodSelected: 'stripe',
        });
      } 
    },
    className: 'modal-transparent-wrapper',
    width: '800px',
    onClose: (_) => {
      _.destroy();
    }
  });

  ajaxModal.open();
}