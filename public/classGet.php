<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business to add the class to.
// class_id:            The ID of the class to get.
//
// Returns
// -------
//
function ciniki_classes_classGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'class_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Class'),
        'images'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Images'),
        'files'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Files'),
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
    $rc = ciniki_classes_checkAccess($ciniki, $args['business_id'], 'ciniki.classes.classGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    //
    // Get the main information
    //
    $strsql = "SELECT ciniki_classes.id, "
        . "ciniki_classes.name, "
        . "ciniki_classes.permalink, "
        . "ciniki_classes.category, "
        . "ciniki_classes.subcat, "
        . "ciniki_classes.primary_image_id, "
        . "ciniki_classes.webflags, "
        . "ciniki_classes.synopsis, "
        . "ciniki_classes.description "
        . "FROM ciniki_classes "
        . "WHERE ciniki_classes.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_classes.id = '" . ciniki_core_dbQuote($ciniki, $args['class_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.classes', array(
        array('container'=>'classes', 'fname'=>'id', 'name'=>'class',
            'fields'=>array('id', 'name', 'permalink', 'category', 'subcat', 'primary_image_id', 
                'webflags', 'synopsis', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['classes']) ) {
        return array('stat'=>'ok', 'err'=>array('pkg'=>'ciniki', 'code'=>'1786', 'msg'=>'Unable to find class'));
    }
    $class = $rc['classes'][0]['class'];

    //
    // Get the images
    //
    if( isset($args['images']) && $args['images'] == 'yes' ) {
        $strsql = "SELECT id, name, image_id, webflags "
            . "FROM ciniki_class_images "
            . "WHERE class_id = '" . ciniki_core_dbQuote($ciniki, $args['class_id']) . "' "
            . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.info', array(
            array('container'=>'images', 'fname'=>'id', 'name'=>'image',
                'fields'=>array('id', 'name', 'image_id', 'webflags')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['images']) ) {
            $class['images'] = $rc['images'];
            ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
            foreach($class['images'] as $inum => $img) {
                if( isset($img['image']['image_id']) && $img['image']['image_id'] > 0 ) {
                    $rc = ciniki_images_loadCacheThumbnail($ciniki, $args['business_id'], 
                        $img['image']['image_id'], 75);
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $class['images'][$inum]['image']['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                }
            }
        }
    }
    
    //
    // Get any files if requested
    //
    if( isset($args['files']) && $args['files'] == 'yes' ) {
        $strsql = "SELECT id, name, extension, permalink "
            . "FROM ciniki_class_files "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_class_files.class_id = '" . ciniki_core_dbQuote($ciniki, $args['class_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.classes', array(
            array('container'=>'files', 'fname'=>'id', 'name'=>'file',
                'fields'=>array('id', 'name', 'extension', 'permalink')),
        ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['files']) ) {
            $class['files'] = $rc['files'];
        }
    }

    
    return array('stat'=>'ok', 'class'=>$class);
}
?>
