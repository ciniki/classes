<?php
//
// Description
// -----------
// This function will return the details about a class, along with the list of images.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// business_id:     The ID of the business to get class from.
//
//
// Returns
// -------
//
function ciniki_classes_web_classDetails($ciniki, $settings, $business_id, $permalink) {

    //
    // Build the query
    //
    $strsql = "SELECT ciniki_classes.id, "
        . "ciniki_classes.name, "
        . "ciniki_classes.permalink, "
        . "ciniki_classes.category, "
        . "ciniki_classes.category_permalink, "
        . "ciniki_classes.subcat, "
        . "ciniki_classes.primary_image_id, "
        . "ciniki_classes.webflags, "
        . "ciniki_classes.synopsis, "
        . "ciniki_classes.description "
        . "FROM ciniki_classes "
        . "WHERE ciniki_classes.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_classes.permalink = '" . ciniki_core_dbQuote($ciniki, $permalink) . "' "
        . "AND (ciniki_classes.webflags&0x01) > 0 "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.classes', array(
        array('container'=>'classes', 'fname'=>'id',
            'fields'=>array('id', 'name', 'permalink', 'category', 'category_permalink', 'subcat',
                'webflags', 'primary_image_id', 'synopsis', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['classes']) || count($rc['classes']) < 1 ) {
        return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1814', 'msg'=>"I'm sorry, but we can't find the class you requested."));
    }
    $class = array_pop($rc['classes']);

    //
    // Get the images for the class
    //
    $strsql = "SELECT id, image_id, name, permalink, description, "
        . "UNIX_TIMESTAMP(ciniki_class_images.last_updated) AS image_last_updated "
        . "FROM ciniki_class_images "
        . "WHERE ciniki_class_images.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_class_images.class_id = '" . ciniki_core_dbQuote($ciniki, $class['id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.classes', array(
        array('container'=>'images', 'fname'=>'id', 
            'fields'=>array('id', 'image_id', 'title'=>'name', 'permalink', 
                'description', 'last_updated'=>'image_last_updated')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['images']) ) {
        $class['images'] = $rc['images'];
    }


    //
    // Get the files for the class
    //
    $strsql = "SELECT id, name, extension, permalink, description "
        . "FROM ciniki_class_files "
        . "WHERE ciniki_class_files.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_class_files.class_id = '" . ciniki_core_dbQuote($ciniki, $class['id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.classes', array(
        array('container'=>'files', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'extension', 'permalink', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['files']) ) {
        $class['files'] = $rc['files'];
    }

    return array('stat'=>'ok', 'class'=>$class);
}
?>
