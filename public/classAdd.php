<?php
//
// Description
// -----------
// This method will add a new class for the business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to add the class to.
// name:			The name of the class.
// url:				(optional) The URL for the class website.
// description:		(optional) The description for the class.
// start_date:		(optional) The date the class starts.  
// end_date:		(optional) The date the class ends, if it's longer than one day.
//
// Returns
// -------
// <rsp stat="ok" id="42">
//
function ciniki_classes_classAdd(&$ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'), 
		'permalink'=>array('required'=>'no', 'blank'=>'no', 'default'=>'', 'name'=>'Permalink'), 
		'category'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Category'), 
		'category_permalink'=>array('required'=>'no', 'blank'=>'no', 'default'=>'', 'name'=>'Category Permalink'), 
		'subcat'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Sub-Category'), 
		'primary_image_id'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Image'), 
		'webflags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'), 
		'synopsis'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Synopsis'),
		'description'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Description'),
		'images'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Images'),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'private', 'checkAccess');
	$ac = ciniki_classes_checkAccess($ciniki, $args['business_id'], 'ciniki.classes.classAdd');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
	if( !isset($args['category_permalink']) || $args['category_permalink'] == '' ) {	
		// This permalink will have duplicates.
		$args['category_permalink'] = ciniki_core_makePermalink($ciniki, $args['category']);
	}

	if( !isset($args['permalink']) || $args['permalink'] == '' ) {
		$permalink = $args['category'];
		$permalink .= ($permalink!=''?'-':'') . $args['subcat'];
		$permalink .= ($permalink!=''?'-':'') . $args['name'];
		$args['permalink'] = ciniki_core_makePermalink($ciniki, $permalink);
	}

	//
	// Check the permalink doesn't already exist
	//
	$strsql = "SELECT id FROM ciniki_classes "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' " 
		. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
		. "AND category = '" . ciniki_core_dbQuote($ciniki, $args['category']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.classes', 'class');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( $rc['num_rows'] > 0 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1774', 'msg'=>'You already have an class with this name, please choose another name'));
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.classes');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Add the class to the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.classes.class', $args, 0x04);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$class_id = $rc['id'];

	$rsp = array('stat'=>'ok', 'id'=>$class_id);

	//
	// Add the images
	//
	if( isset($args['images']) && is_array($args['images']) ) {
		$rsp['images'] = array();
		$sequence = 1;
		foreach($args['images'] as $image_id) {
			$i_args = array(
				'class_id'=>$class_id,
				'name'=>'',
				'sequence'=>$sequence++,
				'webflags'=>'0',
				'image_id'=>$image_id,
				'description'=>'',
				);
			//
			// Get a UUID for use in permalink
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUUID');
			$rc = ciniki_core_dbUUID($ciniki, 'ciniki.classes');
			if( $rc['stat'] != 'ok' ) {
				return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1806', 'msg'=>'Unable to get a new UUID', 'err'=>$rc['err']));
			}
			$i_args['uuid'] = $rc['uuid'];
			$i_args['permalink'] = $rc['uuid'];

			$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.classes.class_image', $i_args, 0x04);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$rsp['images'][] = array('image'=>array('id'=>$rc['id'], 'image_id'=>$image_id));
		}
	}

	//
	// Commit the changes to the database
	//
	$rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.classes');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'classes');

	return $rsp;
}
?>
