<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business to add the slider image to.
// slider_image_id:     The ID of the slider image to get.
//
// Returns
// -------
//
function ciniki_classes_classImages($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'images'=>array('required'=>'yes', 'blank'=>'no', 'type'=>'idlist', 'name'=>'Image'),
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
    $rc = ciniki_classes_checkAccess($ciniki, $args['business_id'], 'ciniki.classes.classImages'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Get the images
    //
    $images = array();
    ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
    foreach($args['images'] as $image_id) {
        $rc = ciniki_images_loadCacheThumbnail($ciniki, $args['business_id'], $image_id, 75);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        //
        // Attach the image data
        //
        $images[] = array('image'=>array('id'=>0, 'image_id'=>$image_id,
            'image_data'=>'data:image/jpg;base64,' . base64_encode($rc['image'])));
    }

    return array('stat'=>'ok', 'images'=>$images);
}
?>
