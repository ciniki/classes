//
// The classes module app
//
function ciniki_classes_class() {
    //
    // Panels
    //
    this.classWebflags = {
        '1':{'name':'Visible'},
        };
    this.init = function() {
        //
        // The panel for editing a class
        //
        this.edit = new M.panel('Class',
            'ciniki_classes_class', 'edit',
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.classes.class.edit');
        this.edit.data = null;
        this.edit.class_id = 0;
        this.edit.additional_images = [];
        this.edit.sections = { 
            '_image':{'label':'', 'aside':'yes', 'type':'imageform', 'fields':{
                'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 
                    'controls':'all', 'history':'no',
                    'addDropImage':function(iid) {
                        M.ciniki_classes_class.edit.setFieldValue('primary_image_id',iid);
                        return true;
                        },
                    'addDropImageRefresh':'',
                    'deleteImage':'M.ciniki_classes_class.edit.deletePrimaryImage',
                    },
            }},
            'details':{'label':'', 'aside':'yes', 'fields':{
                'name':{'label':'Title', 'hint':'Title', 'type':'text'},
                'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
                'subcat':{'label':'Sub-Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
                'webflags':{'label':'Website', 'hint':'', 'type':'flags', 'flags':this.classWebflags},
                }}, 
            '_synopsis':{'label':'Synopsis', 'fields':{
                'synopsis':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'small', 'type':'textarea'},
                }},
            '_description':{'label':'Description', 'fields':{
                'description':{'label':'', 'hidelabel':'yes', 'hint':'', 'type':'textarea'},
                }},
            'images':{'label':'Gallery', 'type':'simplethumbs'},
            '_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
                'addTxt':'Add Additional Image',
                'addFn':'M.ciniki_classes_class.editImage(0);',
                },
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_classes_class.saveClass();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_classes_class.removeClass();'},
                }},
            };  
        this.edit.sectionData = function(s) { return this.data[s]; }
        this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
        this.edit.liveSearchCb = function(s, i, value) {
            if( i == 'category' || i == 'subcat' ) {
                var rsp = M.api.getJSONBgCb('ciniki.classes.classSearchField', {'tnid':M.curTenantID, 'field':i, 'start_needle':value, 'limit':15},
                    function(rsp) {
                        M.ciniki_classes_class.edit.liveSearchShow(s, i, M.gE(M.ciniki_classes_class.edit.panelUID + '_' + i), rsp.results);
                    });
            }
        };
        this.edit.liveSearchResultValue = function(s, f, i, j, d) {
            if( (f == 'category' || f == 'subcat' ) && d.result != null ) { return d.result.name; }
            return '';
        };
        this.edit.liveSearchResultRowFn = function(s, f, i, j, d) { 
            if( (f == 'category' || f == 'subcat' ) && d.result != null ) {
                return 'M.ciniki_classes_class.edit.updateField(\'' + s + '\',\'' + f + '\',\'' + escape(d.result.name) + '\');';
            }
        };
        this.edit.updateField = function(s, fid, result) {
            M.gE(this.panelUID + '_' + fid).value = unescape(result);
            this.removeLiveSearch(s, fid);
        };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.classes.classHistory', 'args':{'tnid':M.curTenantID, 
                'class_id':this.class_id, 'field':i}};
        }
        this.edit.deletePrimaryImage = function(fid) {
            this.setFieldValue(fid, 0, null, null);
            return true;
        };
        this.edit.addDropImage = function(iid) {
            if( M.ciniki_classes_class.edit.class_id > 0 ) {
                var rsp = M.api.getJSON('ciniki.classes.classImageAdd', 
                    {'tnid':M.curTenantID, 'image_id':iid, 
                    'class_id':M.ciniki_classes_class.edit.class_id});
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                return true;
            } else {
                var name = M.ciniki_classes_class.edit.formValue('name');
                if( name == '' ) {
                    M.alert('You must enter the name of the class first');
                    return false;
                }
                // Save the class
                var c = M.ciniki_classes_class.edit.serializeForm('yes');
                c += '&images=' + iid;
                var rsp = M.api.postJSON('ciniki.classes.classAdd', {'tnid':M.curTenantID}, c);
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_classes_class.edit.class_id = rsp.id;
//              , function(rsp) {
//                  if( rsp.stat != 'ok' ) {
//                      M.api.err(rsp);
//                      return false;
//                  }
//                  M.ciniki_classes_class.edit.class_id = rsp.id;
//              });
                return true;
            }
        };
        this.edit.addDropImageRefresh = function() {
            if( M.ciniki_classes_class.edit.class_id > 0 ) {
                var rsp = M.api.getJSONCb('ciniki.classes.classGet', {'tnid':M.curTenantID, 
                    'class_id':M.ciniki_classes_class.edit.class_id, 'images':'yes'}, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        var p = M.ciniki_classes_class.edit;
                        p.data.images = rsp.class.images;
                        p.refreshSection('images');
                        p.show();
                    });
            }
            return true;
        };
        this.edit.thumbFn = function(s, i, d) {
            return 'M.ciniki_classes_class.editImage(\'' + d.image.id + '\');';
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_classes_class.saveClass();');
        this.edit.addClose('Cancel');
    }

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_classes_class', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        } 

        this.editClass(cb, args.class_id);
    }

    this.editClass = function(cb, cid) {
        this.edit.reset();
        if( cid != null ) { this.edit.class_id = cid; }
        this.edit.sections.details.fields.category.active = ((M.curTenant.modules['ciniki.classes'].flags&0x01)>0)?'yes':'no';
        this.edit.sections.details.fields.subcat.active = ((M.curTenant.modules['ciniki.classes'].flags&0x02)>0)?'yes':'no';
        if( this.edit.class_id > 0 ) {
            this.edit.sections._buttons.buttons.delete.visible = 'yes';
            M.api.getJSONCb('ciniki.classes.classGet', {'tnid':M.curTenantID, 
                'class_id':this.edit.class_id, 'images':'yes', 'files':'yes'}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_classes_class.edit;
                    p.data = rsp.class;
                    p.refresh();
                    p.show(cb);
                });
        } else {
            this.edit.sections._buttons.buttons.delete.visible = 'no';
            this.edit.data = {};
            this.edit.additional_images = [];
            this.edit.refresh();
            this.edit.show(cb);
        }
    };

    this.saveClass = function() {
        if( this.edit.class_id > 0 ) {
            var c = this.edit.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.classes.classUpdate', 
                    {'tnid':M.curTenantID, 'class_id':M.ciniki_classes_class.edit.class_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                    M.ciniki_classes_class.edit.close();
                    });
            } else {
                this.edit.close();
            }
        } else {
            var name = this.edit.formValue('name');
            if( name == '' ) {
                M.alert('You must enter the name of the class first');
                return false;
            }
            var c = this.edit.serializeForm('yes');
            M.api.postJSONCb('ciniki.classes.classAdd', 
                {'tnid':M.curTenantID}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_classes_class.edit.close();
                });
        }
    };

    this.editImage = function(iid) {
        if( this.edit.class_id > 0 ) {
            M.startApp('ciniki.classes.classimages',null,'M.ciniki_classes_class.edit.addDropImageRefresh();','mc',{'class_id':this.edit.class_id,'class_image_id':iid});
        } else {
            var name = this.edit.formValue('name');
            if( name == '' ) {
                M.alert('You must enter the name of the class first');
                return false;
            }
            // Save the class
            var c = this.edit.serializeForm('yes');
            M.api.postJSONCb('ciniki.classes.classAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_classes_class.edit.class_id = rsp.id;
                M.startApp('ciniki.classes.classimages',null,'M.ciniki_classes_class.editClass();','mc',{'class_id':rsp.id,'class_image_id':iid});
            });
        }
    };

    this.removeClass = function() {
        M.confirm("Are you sure you want to remove this class and all the images and files associated with it?",null,function() {
            M.api.getJSONCb('ciniki.classes.classDelete', 
                {'tnid':M.curTenantID, 'class_id':M.ciniki_classes_class.edit.class_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_classes_class.edit.close();
                });
        });
    }
};
