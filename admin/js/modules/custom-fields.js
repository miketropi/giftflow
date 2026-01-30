import GiftFlowGoogleMapField from './googlemap-field';
import GiftFlowAccordion from './accordion-section';
import GiftFlowGalleryField from './gallery-field';

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

  // window load 
  w.addEventListener( 'load', () => {
    handleMapField()
    handleAccordion()
    handleGalleryField()
  } );

})( window, jQuery )