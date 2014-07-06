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
		$business_id, 'ciniki.classes', 'settings', 'classes-' . $permalink);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$settings = $rc['settings'];
		
	//
	// Get the category name
	//
	$title = '';
	if( preg_match("/^category-(.*)$/", $permalink, $m) ) {
		$strsql = "SELECT category "
			. "FROM ciniki_classes "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "AND category_permalink = '" . ciniki_core_dbQuote($ciniki, $m[1]) . "' "
			. "LIMIT 1 "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.classes', 'cat');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['cat']) ) {
			$title = $rc['cat']['category'];
		}
	}

	$info = array(
		'title'=>(isset($settings['classes-' . $permalink . '-name'])?$settings['classes-' . $permalink . '-name']:$title),
		'image_id'=>(isset($settings['classes-' . $permalink . '-image-id'])?$settings['classes-' . $permalink . '-image-id']:''),
		'image_caption'=>(isset($settings['classes-' . $permalink . '-image-caption'])?$settings['classes-' . $permalink . '-image-caption']:''),
		'image_url'=>(isset($settings['classes-' . $permalink . '-image-url'])?$settings['classes-' . $permalink . '-image-url']:''),
		'content'=>(isset($settings['classes-' . $permalink . '-content'])?$settings['classes-' . $permalink . '-content']:''),
		);

	return array('stat'=>'ok', 'info'=>$info);
}
?>
