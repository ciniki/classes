<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to add the class image to.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_classes_classList(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'private', 'checkAccess');
    $rc = ciniki_classes_checkAccess($ciniki, $args['business_id'], 'ciniki.classes.classList'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	//
	// Get the existing details
	//
	$strsql = "SELECT id, "
		. "category, category_permalink, subcat, name "
		. "FROM ciniki_classes "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
//	if( isset($args['category']) && $args['category'] != '' ) {
//		$strsql .= "AND category = '" . ciniki_core_dbQuote($ciniki, $args['category']) . "' ";
//	}
	$strsql .= "ORDER BY category, subcat, name "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.classes', array(
		array('container'=>'classes', 'fname'=>'id', 'name'=>'class',
			'fields'=>array('id', 'category', 'category_permalink', 'subcat', 'name')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['classes']) ) {
		return array('stat'=>'ok', 'classes'=>array());
	}
	$classes = $rc['classes'];

	return array('stat'=>'ok', 'classes'=>$classes);
}
?>
