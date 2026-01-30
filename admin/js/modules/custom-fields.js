import GiftFlowGoogleMapField from './googlemap-field';
import GiftFlowAccordion from './accordion-section';

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

  // window load 
  w.addEventListener( 'load', () => {
    handleMapField()
    handleAccordion()
  } );

})( window, jQuery )