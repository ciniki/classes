<?php
//
// Description
// -----------
// This function will return the image, caption and content for a page in the classes.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure.
// business_id:		The ID of the business to get events for.
//
//
// Returns
// -------
//
function ciniki_classes_web_pageInfo($ciniki, $settings, $business_id, $permalink) {

	//
	// Build the query to get the categories
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_class_settings', 'business_id', 
		$args['business_id'], 'ciniki.classes', 'settings', 'classes-' . $permalink);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$settings = $rc['settings'];

	$info = array(
		'title'=>(isset($settings['classes-' . $permalink . '-name'])?$settings['classes-' . $permalink . '-name']:''),
		'image_id'=>(isset($settings['classes-' . $permalink . '-image-id'])?$settings['classes-' . $permalink . '-image-id']:''),
		'image_caption'=>(isset($settings['classes-' . $permalink . '-image-caption'])?$settings['classes-' . $permalink . '-image-caption']:''),
		'image_url'=>(isset($settings['classes-' . $permalink . '-image-url'])?$settings['classes-' . $permalink . '-image-url']:''),
		);

	return array('stat'=>'ok', 'info'=>$info);
}
?>
