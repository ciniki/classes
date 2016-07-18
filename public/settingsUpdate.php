<?php
//
// Description
// -----------
// This method will update one or more settings for the classes module.
//
// Info
// ----
// Status:          defined
//
// Arguments
// ---------
// user_id:         The user making the request
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_classes_settingsUpdate(&$ciniki) {
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
    $rc = ciniki_classes_checkAccess($ciniki, $args['business_id'], 'ciniki.classes.settingsUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Grab the settings for the business from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    $rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_class_settings', 
        'business_id', $args['business_id'], 'ciniki.classes', 'settings', '');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $settings = $rc['settings'];

    //  
    // Turn off autocommit
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.classes');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Check each argument passed to see if it matches an allowed settings string
    //
    foreach($ciniki['request']['args'] as $field => $fvalue) {
        if( preg_match('/classes-.*-(content|image-id|image-caption|image-url)/', $field, $matches) ) {
            if( $matches[1] == 'image-id' && ($fvalue == 'undefined' || $fvalue == '') ) { continue; }
            //
            // Add the settings if they don't already exist
            //
            if( !isset($settings[$field]) ) {
                $strsql = "INSERT INTO ciniki_class_settings (business_id, detail_key, detail_value, "
                    . "date_added, last_updated) "
                    . "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['business_id']) . "'"
                    . ", '" . ciniki_core_dbQuote($ciniki, $field) . "'"
                    . ", '" . ciniki_core_dbQuote($ciniki, $fvalue) . "'"
                    . ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
                    . "";
                $rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.classes');
                if( $rc['stat'] != 'ok' ) {
                    ciniki_core_dbTransactionRollback($ciniki, 'ciniki.classes');
                    return $rc;
                }
                ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.classes', 'ciniki_class_history', 
                    $args['business_id'], 1, 'ciniki_class_settings', $field, 'detail_value', $fvalue);
                $ciniki['syncqueue'][] = array('push'=>'ciniki.classes.setting', 
                    'args'=>array('id'=>$field));
            } 
            //
            // Update the settings
            //
            elseif( isset($settings[$field]) && $settings[$field] != $fvalue ) {
                $strsql = "UPDATE ciniki_class_settings "
                    . "SET detail_value = '" . ciniki_core_dbQuote($ciniki, $fvalue) . "', "
                    . "last_updated = UTC_TIMESTAMP() "
                    . "WHERE detail_key = '" . ciniki_core_dbQuote($ciniki, $field) . "' "
                    . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
                    . "";
                $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.classes');
                if( $rc['stat'] != 'ok' ) {
                    ciniki_core_dbTransactionRollback($ciniki, 'ciniki.classes');
                    return $rc;
                }
                ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.classes', 'ciniki_class_history', 
                    $args['business_id'], 2, 'ciniki_class_settings', $field, 'detail_value', $fvalue);
                $ciniki['syncqueue'][] = array('push'=>'ciniki.classes.setting', 
                    'args'=>array('id'=>$field));
            }
        }
    }

    //
    // Commit the database changes
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

    return array('stat'=>'ok');
}
?>
