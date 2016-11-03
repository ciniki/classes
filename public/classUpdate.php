<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business to add the class image to.
// name:                The name of the image.  
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_classes_classUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'class_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Class'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'), 
        'permalink'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Permalink'), 
        'category'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Category'), 
        'category_permalink'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Category Permalink'), 
        'subcat'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sub-Category'), 
        'primary_image_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image'), 
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'), 
        'synopsis'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Synopsis'),
        'description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Description'),
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
    $rc = ciniki_classes_checkAccess($ciniki, $args['business_id'], 'ciniki.classes.classUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    //
    // Get the existing details
    //
    $strsql = "SELECT id, uuid, category, category_permalink, subcat, name, primary_image_id "
        . "FROM ciniki_classes "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['class_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.classes', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['item']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.classes.26', 'msg'=>'Class not found'));
    }
    $item = $rc['item'];

    //
    // Check if the permalink needs rebuilding
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
    if( isset($args['category']) ) {
        // This permalink will have duplicates.
        $args['category_permalink'] = ciniki_core_makePermalink($ciniki, $args['category']);
    }
    if( isset($args['category']) || isset($args['subcat']) || isset($args['name']) ) {
        $permalink = '';
        if( isset($args['category']) ) {
            if( $args['category'] != '' ) {
                $permalink = $args['category'];
            }
        } elseif( $item['category'] != '' ) {
            $permalink = $item['category'];
        }
        if( isset($args['subcat']) ) {
            if( $args['subcat'] != '' ) {
                $permalink .= ($permalink!=''?'-':'') . $args['subcat'];
            }
        } elseif( $item['subcat'] != '' ) {
            $permalink .= ($permalink!=''?'-':'') . $item['subcat'];
        }
        if( isset($args['name']) ) {
            if( $args['name'] != '' ) {
                $permalink .= ($permalink!=''?'-':'') . $args['name'];
            }
        } elseif( $item['name'] != '' ) {
            $permalink .= ($permalink!=''?'-':'') . $item['name'];
        }

        $args['permalink'] = ciniki_core_makePermalink($ciniki, $permalink);

        //
        // Make sure the permalink is unique
        //
        $strsql = "SELECT id, name, permalink "
            . "FROM ciniki_classes "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
            . "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['class_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.classes', 'class');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['num_rows'] > 0 ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.classes.27', 'msg'=>'You already have an class with this name, please choose another name'));
        }
    }

    //
    // Update the class in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    return ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.classes.class', $args['class_id'], $args);
}
?>
