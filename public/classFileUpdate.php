<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to add the class to.
// name:                The name of the file.  
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_classes_classFileUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'file_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'File'), 
        'name'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'First Name'),
        'description'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Description'),
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Web Flags'),
        'publish_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Publish Date'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    if( isset($args['name']) && (!isset($args['permalink']) || $args['permalink'] == '') ) {
        $args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['name'])));
    }

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'private', 'checkAccess');
    $rc = ciniki_classes_checkAccess($ciniki, $args['tnid'], 'ciniki.classes.classFileUpdate', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    //
    // Get the current information about the file
    //
    $strsql = "SELECT id, class_id, name, permalink FROM ciniki_class_files "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['file_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.classes', 'file');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['file']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.classes.15', 'msg'=>'File does not exist.'));
    }
    $file = $rc['file'];

    //
    // Check the permalink doesn't already exist
    //
    if( isset($args['permalink']) ) {
        $strsql = "SELECT id, name, permalink FROM ciniki_class_files "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND class_id = '" . ciniki_core_dbQuote($ciniki, $file['class_id']) . "' "
            . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
            . "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['file_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.classes', 'files');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['num_rows'] > 0 ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.classes.16', 'msg'=>'You already have a file with this name, please choose another name.'));
        }
    }

    //
    // Update the file in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    return ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.classes.file', $args['file_id'], $args, 0x07);
}
?>
