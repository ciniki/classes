<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to add the class image to.
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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'private', 'checkAccess');
    $rc = ciniki_classes_checkAccess($ciniki, $args['tnid'], 'ciniki.classes.classList'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    //
    // Get the existing details
    //
    $strsql = "SELECT id, "
        . "category, category_permalink, subcat, name "
        . "FROM ciniki_classes "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
//  if( isset($args['category']) && $args['category'] != '' ) {
//      $strsql .= "AND category = '" . ciniki_core_dbQuote($ciniki, $args['category']) . "' ";
//  }
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
