<?php
//
// Description
// -----------
// The module flags
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_classes_flags($ciniki, $modules) {
    $flags = array(
        array('flag'=>array('bit'=>'1', 'name'=>'Categories')),
        array('flag'=>array('bit'=>'2', 'name'=>'Sub-Categories')),
        );

    return array('stat'=>'ok', 'flags'=>$flags);
}
?>
