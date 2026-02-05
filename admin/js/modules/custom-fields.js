import GiftFlowGoogleMapField from './googlemap-field';
import GiftFlowAccordion from './accordion-section';
import GiftFlowGalleryField from './gallery-field';
import GiftFlowRepeaterField from './repeater-field';

((w, $) => {
	'use strict';

  const handleMapField = () => {
    const mapFields = $( '.giftflow-googlemap-field' );
    if ( mapFields.length === 0 ) {
      return;
    }

    mapFields.each( ( index, mapField ) => {
      new GiftFlowGoogleMapField( mapField, {
        apiKey: mapField.data( 'api-key' ),
        lat: mapField.data( 'lat' ),
        lng: mapField.data( 'lng' ),
      } );
    } );  
  }

  const handleAccordion = () => {
    const accordionSections = $( '.giftflow-accordion-section' );
    if ( accordionSections.length === 0 ) {
      return;
    }

    accordionSections.each( ( index, accordionSection ) => {
      new GiftFlowAccordion( accordionSection );
    } );
  }

  const handleGalleryField = () => {
    const selector = document.querySelectorAll( '.giftflow-gallery-field' );
    if ( ! selector || selector.length === 0 ) {
      return;
    }

    [...selector].forEach( ( element ) => {
      const options = {
        maxImages: element.dataset.maxImages,
        imageSize: element.dataset.imageSize,
        buttonText: element.dataset.buttonText,
        removeText: element.dataset.removeText,
        nonce: element.dataset.nonce,
      };
      
      new GiftFlowGalleryField( element, options );
    } );

  }

  const navTabHandler = () => {
    $('.giftflow-meta-box-tabs .nav-tab').on('click', function(e) {
      e.preventDefault();
      
      // Remove active class from all tabs and content
      $('.nav-tab').removeClass('nav-tab-active');
      $('.tab-content').removeClass('active');
      
      // Add active class to clicked tab
      $(this).addClass('nav-tab-active');
      
      // Show corresponding content
      var target = $(this).attr('href');
      $(target).addClass('active');
    });
  }

  /**
   * Handle repeater field
   * @returns {void}
   */
  const handleRepeaterField = () => {
    const repeaterFields = document.querySelectorAll( '.giftflow-repeater-field' );
    if ( repeaterFields.length === 0 ) {
      return;
    }

    [...repeaterFields].forEach( ( repeaterField ) => {
      const rowTemplate = $( repeaterField ).find( '.giftflow-repeater-row-template' ).html();
      $( repeaterField ).find( '.giftflow-repeater-row-template' ).remove();

      const options = {
        repeaterId: repeaterField.dataset.id,
        maxRows: repeaterField.dataset.maxRows,
        buttonText: repeaterField.dataset.buttonText,
        removeText: repeaterField.dataset.removeText,
        rowTemplate: rowTemplate,
        rowLabel: repeaterField.dataset.rowLabel,
        minRows: repeaterField.dataset.minRows,
      };

      new GiftFlowRepeaterField( repeaterField, options );
    } );
  }

  // window load 
  w.addEventListener( 'load', () => {
    handleMapField()
    handleAccordion()
    handleGalleryField()
    navTabHandler()
    handleRepeaterField()
  } );

})( window, jQuery )