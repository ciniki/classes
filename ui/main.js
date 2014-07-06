//
// This app will handle the listing, additions and deletions of classes.  These are associated business.
//
function ciniki_classes_main() {
	//
	// Panels
	//
	this.init = function() {
		//
		// classes panel
		//
		this.menu = new M.panel('Classes',
			'ciniki_classes_main', 'menu',
			'mc', 'medium', 'sectioned', 'ciniki.classes.main.menu');
        this.menu.sections = {
			'info':{'label':'', 'list':{}},
			'classes':{'label':'Classes', 'type':'simplegrid', 'num_cols':1,
				'headerValues':null,
				'dataMaps':['name'],
				'cellClasses':['multiline', 'multiline'],
				'addTxt':'Add Class',
				'addFn':'M.startApp(\'ciniki.classes.class\',null,\'M.ciniki_classes_main.showMenu();\',\'mc\',{\'class_id\':0});',
				},
			};
		this.menu.sectionData = function(s) { 
			if( s == 'info' ) { return this.sections[s].list; }
			return this.data[s]; 
		};
		this.menu.cellValue = function(s, i, j, d) {
			return d.class[M.ciniki_classes_main.menu.sections[s].dataMaps[j]];
		};
		this.menu.rowFn = function(s, i, d) {
			return 'M.startApp(\'ciniki.classes.class\',null,\'M.ciniki_classes_main.showMenu();\',\'mc\',{\'class_id\':\'' + d.class.id + '\'});';
		};
		this.menu.addButton('add', 'Add', 'M.startApp(\'ciniki.classes.class\',null,\'M.ciniki_classes_main.showMenu();\',\'mc\',{\'class_id\':0});');
		this.menu.addClose('Cancel');
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
		var appContainer = M.createContainer(appPrefix, 'ciniki_classes_main', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		if( (M.curBusiness.modules['ciniki.classes'].flags&0x02) > 0 ) {
			this.menu.sections.classes.num_cols = 3;
			this.menu.sections.classes.headerValues = ['Category', 'Sub-Category', 'Name'];
			this.menu.sections.classes.dataMaps = ['category', 'subcat', 'name'];
		} else if( (M.curBusiness.modules['ciniki.classes'].flags&0x01) > 0 ) {
			this.menu.sections.classes.num_cols = 2;
			this.menu.sections.classes.headerValues = ['Category', 'Name'];
			this.menu.sections.classes.dataMaps = ['category', 'name'];
		} else {
			this.menu.sections.classes.num_cols = 1;
			this.menu.sections.classes.headerValues = null;
			this.menu.sections.classes.dataMaps = ['name'];
		}

		this.showMenu(cb);
	}

	this.showMenu = function(cb) {
		this.menu.data = {};
		M.api.getJSONCb('ciniki.classes.classList', 
			{'business_id':M.curBusinessID}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_classes_main.menu;
				p.data = {'classes':rsp.classes};
				p.sections.info.list['introduction'] = {'label':'Introduction',
					'fn':'M.startApp(\'ciniki.classes.info\',null,\'M.ciniki_classes_main.showMenu();\',\'mc\',{\'page\':\'introduction\',\'name\':\'Introduction\'});'};
				// if subcats enabled, then display list of categories for info details
				if( (M.curBusiness.modules['ciniki.classes'].flags&0x02) > 0 ) {
					p.sections.info.label = 'Category Descriptions';
					for(i in rsp.classes) {
						if( rsp.classes[i].class.category_permalink == '' ) { continue; }
						var cp = 'category-' + rsp.classes[i].class.category_permalink;
						if( p.sections.info.list[cp] == null ) {
							p.sections.info.list[cp] = {'label':rsp.classes[i].class.category,
								'fn':'M.startApp(\'ciniki.classes.info\',null,\'M.ciniki_classes_main.showMenu();\',\'mc\',{\'page\':\'' + cp + '\',\'name\':\'' + escape(rsp.classes[i].class.category) + '\'});'};
						}
					}
				} else {
					p.sections.info.label = '';
				}
				p.refresh();
				p.show(cb);
			});
	};
};
