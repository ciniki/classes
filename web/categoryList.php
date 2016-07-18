<?php
//
// Description
// -----------
// This function will return a list of categories for the classes.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// business_id:     The ID of the business to get events for.
//
//
// Returns
// -------
//
function ciniki_classes_web_categoryList($ciniki, $settings, $business_id) {

    //
    // Build the query to get the categories
    //
    $strsql = "SELECT DISTINCT category, category_permalink "
        . "FROM ciniki_classes "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND (webflags&0x01) > 0 "
        . "ORDER BY category "
        . "";
    //
    // Get the list of posts, sorted by publish_date for use in the web CI List Categories
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.classes', array(
        array('container'=>'categories', 'fname'=>'category_permalink', 
            'fields'=>array('name'=>'category', 'permalink'=>'category_permalink')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['categories']) ) {
        $categories = $rc['categories'];
    } else {
        $categories = array();
    }

    return array('stat'=>'ok', 'categories'=>$categories);
}
?>
