<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_classes_objects($ciniki) {
    
    $objects = array();
    $objects['class'] = array(
        'name'=>'Class',
        'sync'=>'yes',
        'table'=>'ciniki_classes',
        'fields'=>array(
            'name'=>array(),
            'permalink'=>array(),
            'category'=>array(),
            'category_permalink'=>array(),
            'subcat'=>array(),
            'primary_image_id'=>array('ref'=>'ciniki.images.image'),
            'webflags'=>array(),
            'synopsis'=>array(),
            'description'=>array(),
            ),
        'history_table'=>'ciniki_class_history',
        );
    $objects['class_image'] = array(
        'name'=>'Class Image',
        'sync'=>'yes',
        'table'=>'ciniki_class_images',
        'fields'=>array(
            'class_id'=>array('ref'=>'ciniki.classes.class'),
            'name'=>array(),
            'permalink'=>array(),
            'sequence'=>array(),
            'webflags'=>array(),
            'image_id'=>array('ref'=>'ciniki.images.image'),
            'description'=>array(),
            ),
        'history_table'=>'ciniki_class_history',
        );
    $objects['class_file'] = array(
        'name'=>'File',
        'sync'=>'yes',
        'table'=>'ciniki_class_files',
        'fields'=>array(
            'class_id'=>array('ref'=>'ciniki.classes.class'),
            'extension'=>array(),
            'name'=>array(),
            'permalink'=>array(),
            'webflags'=>array(),
            'description'=>array(),
            'org_filename'=>array(),
            'publish_date'=>array(),
            'binary_content'=>array('history'=>'no'),
            ),
        'history_table'=>'ciniki_class_history',
        );
    
    return array('stat'=>'ok', 'objects'=>$objects);
}
?>
