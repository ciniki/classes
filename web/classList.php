<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_classes_web_classList($ciniki, $settings, $business_id, $args) {

	$strsql = "SELECT id, name, permalink, category, category_permalink, subcat "
		. "synopsis, "
		. "IF(description<>'', 'yes', 'no') AS is_details "
		. "FROM ciniki_classes "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND (webflags&0x01) > 0 "
		. "";
	if( isset($args['category']) && $args['category'] != '' ) {
		$strsql .= "AND category_permalink = '" . ciniki_core_dbQuote($ciniki, $args['category']) . "' ";
	}
	$strsql .= "ORDER BY category, subcat, name ";
	//
	// Based on the options enabled for the business, determine how the returning list
	// to the web should be organized.  
	//.
	if( isset($args['category']) && $args['category'] != '' ) {
		//
		// If the subcat is enabled
		//
		if( ($ciniki['business']['modules']['ciniki.classes']['flags']&0x02) > 0 ) {
			// Use the subcat as the category
			$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.classes', array(
				array('container'=>'categories', 'fname'=>'subcat',
					'fields'=>array('name'=>'subcat')),
				array('container'=>'list', 'fname'=>'id',
					'fields'=>array('id', 'title'=>'name', 'permalink', 'image_id'=>'primary_image_id',
						'description'=>'synopsis', 'is_details')),
				));
		} 
		//
		// Otherwise just list the classes
		//
		else {
			// Use the name as the category
			$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.classes', array(
				array('container'=>'list', 'fname'=>'id',
					'fields'=>array('id', 'title'=>'name', 'permalink', 'image_id'=>'primary_image_id',
						'description'=>'synopsis', 'is_details')),
				));
		}
	} else {
		if( ($ciniki['business']['modules']['ciniki.classes']['flags']&0x01) > 0 ) {
			// Use the category as the category
			$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.classes', array(
				array('container'=>'categories', 'fname'=>'category_permalink',
					'fields'=>array('name'=>'category')),
				array('container'=>'list', 'fname'=>'id',
					'fields'=>array('id', 'title'=>'name', 'permalink', 'image_id'=>'primary_image_id',
						'description'=>'synopsis', 'is_details')),
				));
		} else {
			// Use the name as the category
			$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.classes', array(
				array('container'=>'list', 'fname'=>'id',
					'fields'=>array('id', 'title'=>'name', 'permalink', 'image_id'=>'primary_image_id',
						'description'=>'synopsis', 'is_details')),
				));
		}
	}

	return $rc;
}
?>
