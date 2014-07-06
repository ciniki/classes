//
// Manage the information for the classes introduction and category descriptions
//
function ciniki_classes_info() {
	this.init = function() {
		//
		// The panel to display the class form
		//
		this.edit = new M.panel('Information',
			'ciniki_classes_info', 'edit',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.classes.info.edit');
		this.edit.page_permalink = '';
		this.edit.data = {};	
		this.edit.sections = {};
		this.edit.sectionData = function(s) { return this.data[s]; };
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.classes.settingsHistory', 
				'args':{'business_id':M.curBusinessID, 'setting':i}};
		}
		this.edit.deleteImage = function(fid) {
			this.setFieldValue(fid, 0, null, null);
			return true;
		};
		this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
		this.edit.addButton('save', 'Save', 'M.ciniki_classes_info.saveInfo();');
		this.edit.addClose('Back');
	}

	this.start = function(cb, appPrefix, aG) {
		args = {};
		if( aG != null ) { args = eval(aG); }

		//
		// Create container
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_classes_info', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		}

		this.showPage(cb, args.page, unescape(args.name));
	}

	this.showPage = function(cb, page, name) {
		if( page != null ) { this.edit.page_permalink = page; }
		if( name != null ) { this.edit.title = name; }
		
		M.api.getJSONCb('ciniki.classes.settingsGet', {'business_id':M.curBusinessID, 
			'page':this.edit.page_permalink}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_classes_info.edit;
				p.data = rsp.settings;
				p.sections = {
					'_image':{'label':'', 'aside':'yes', 'fields':{}},
					'_caption':{'label':'', 'aside':'yes', 'fields':{}},
					'_content':{'label':'More information', 'type':'simpleform', 'fields':{}},
					'_buttons':{'label':'', 'buttons':{
						'save':{'label':'Save', 'fn':'M.ciniki_classes_info.saveInfo();'},
					}},
				}
				p.sections._image.fields['classes-' + p.page_permalink + '-image-id'] = 
					{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'};
				p.sections._caption.fields['classes-' + p.page_permalink + '-image-caption'] = 
					{'label':'Caption', 'type':'text'};
				p.sections._content.fields['classes-' + p.page_permalink + '-content'] = 
					{'label':'Content', 'type':'textarea', 'size':'large', 'hidelabel':'yes'};
				p.addDropImage = function(iid) {
					M.ciniki_classes_info.edit.setFieldValue('classes-' + p.page_permalink + '-image-id', iid, null, null);
					return true;
				};
				p.refresh();
				p.show(cb);
			});
	};

	this.saveInfo = function() {
		var c = this.edit.serializeForm('no');
	
		console.log(c);
		if( c != '' ) {
			M.api.postJSONFormData('ciniki.classes.settingsUpdate', 
				{'business_id':M.curBusinessID}, c, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
					M.ciniki_classes_info.edit.close();
				});
		} else {
			this.edit.close();
		}
	};
}
