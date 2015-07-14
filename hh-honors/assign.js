var honor_buttons = [];
var honor_buttons_live = [];
var honors_db = [];
var honor_selected = 0;
var honor_refresh = 0;

var member_buttons = [];
var member_buttons_live = [];
var members_db = [];
var member_selected = 0;
var member_refresh = 0;

var display_mode = "view";  // View or Assign

function myButtonInit() {
	var e = document.getElementsByTagName("input");
	for( var i=0; i < e.length; i++ ) {
		if ( e[i].id.match(/-all$/ ) ) {
			continue;  // Don't load the All key
		} else if ( e[i].id.match(/^day/) ) {
			honor_buttons.push( e[i].id );
		} else if (e[i].id.match(/^opt/)) {
			member_buttons.push( e[i].id );
		}
	}
}

function myHonorSelect(id) {
	for( var i=0; i<honors_db.length; i++ ) {
		if ( honors_db[i].id == id ) {
			honors_db[i].selected = 1;
		} else if ( honors_db[i].selected ) {
			honors_db[i].selected = 0;
		}
	}
}

function myMemberSelect(id) {
	for( var i=0; i<member_db.length; i++ ) {
		if ( member_db[i].id == id ) {
			member_db[i].selected = 1;
		} else if ( member_db[i].selected ) {
			member_db[i].selected = 0;
		}
	}
}

function myClickHonor(id) {
	if ( display_mode == 'assign' ) {
		honors_db.forEach( function xx( obj, hid ) {
									if ( hid == id ) {
										obj.selected = 1;
									} else {
										obj.selected = 0;
									}
								}
								);
		
	} else {
		for( var i=0; i<honors_db.length; i++ ) {
			if( honors_db[i].id == id ) {
				honors_db[i].selected = 1;
				document.getElementById('honor_' + honors_db[i].id ).className = "closed";
				document.getElementById('member_' + honors_db[i].assigned ).className = "closed";
			} else if ( honors_db[i].selected ) {
				honors_db[i].selected = 0;			
				document.getElementById('honor_' + honors_db[i].id ).className = "";
				document.getElementById('member_' + honors_db[i].assigned ).className = "";
			}
		}
	}
	myHighlightAction();
	myDisplayHonors();
}

function myClickMember(id) {
	if ( display_mode == 'assign' ) {
		members_db.forEach( function xx( obj, hid ) {
									if ( hid == id ) {
										obj.selected = 1;
									} else {
										obj.selected = 0;
									}
								}
								);
		
	} else {
		for( var i=0; i<member_db.length; i++ ) {
			if( member_db[i].id == id ) {
				member_db[i].selected = 1;
				document.getElementById('member_' + member_db[i].id ).className = "closed";
				document.getElementById('honor_' + member_db[i].assigned ).className = "closed";
			} else if ( member_db[i].selected ) {
				member_db[i].selected = 0;			
				document.getElementById('member_' + member_db[i].id ).className = "";
				document.getElementById('honor_' + member_db[i].assigned ).className = "";
			}
		}
	}
	myHighlightAction();
	myDisplayMembers();
}

function myClickCategory(id,forced_state) {
	var pos;
	
	if ( id == 'opt-all' ) {
		member_buttons_live = member_buttons;
		
	} else if ( id == 'opt-none' ) {
		member_buttons_live = [];
		
	} else if ( arguments.length == 1 ) { //toggle mode
		pos = member_buttons_live.indexOf(id);
		if ( pos >= 0 ) {
			member_buttons_live.splice(pos,1); // remove
		} else {
			member_buttons_live.push(id); // add
		}
		
	} else if ( arguments.length == 2 ) { //forced mode
		pos = member_buttons_live.indexOf(id);
		if ( forced_state && pos < 0 ) {
			member_buttons_live.push(id);
		} else if ( ! forced_state && pos >= 0 ) {
			member_buttons_live.splice(pos,1);
		}
	}
	member_refresh = 1;
}

function myClickDay(id,forced_state) {
	var pos;
	
	if ( id == 'day-all' ) {
		honor_buttons_live = honor_buttons
		
	} else if ( id == 'day-none' ) {
		honor_buttons_live = [];

	} else if ( arguments.length == 1 ) {  //toggle mode
		pos = honor_buttons_live.indexOf(id);
		if( pos >= 0 ) {
			honor_buttons_live.splice(pos,1); // remove
		} else {
			honor_buttons_live.push(id); // add
		}
		
	} else if ( arguments.length == 2 ) { //forced mode
		pos = honor_buttons_live.indexOf(id);
		if ( forced_state && pos < 0 ) {
			honor_buttons_live.push(id);
		} else if ( ! forced_state && pos >= 0 ) {
			honor_buttons_live.splice(pos,1);
		}
	}
	honor_refresh = 1;
}

function myDisplayHonors()  {
	var i, j, e, ok, show;
	
	for( day of honor_buttons ) {
		if ( honor_buttons_live.indexOf(day) >= 0 ) {
			document.getElementById(day).className = "highlighted";
		} else {
			document.getElementById(day).className = "";
		}
	}

	var num_visible = 0;
	var num_assigned = 0;
	
	if ( display_mode == 'assign' ) {
		e = document.getElementById( 'honors-div' );
		for( i=0; i < e.children.length; i++ ) {
			var id = e.children[i].id.substr(6); // id's are honor_***
			if ( honor_buttons_live.indexOf(honors_db[id].service) >= 0 ) {
				num_visible++;
				if( honors_db[id].assigned ) {
					e.children[i].className = "hidden";
					num_assigned++;
				} else if( honors_db[id].selected ) {
					e.children[i].className = "highlighted";
				} else {
					e.children[i].className = "visible";
				}
			} else {
				e.children[i].className = "hidden";
			}
		}
	}
/*	
	for( i=0; i < honors_db.length; i++ ) {
		e = document.getElementById( 'honor_' + honors_db[i].id );
		
		show = honor_buttons_live.indexOf( honors_db[i].service ) >= 0;
		if ( show ) {
			num_visible++;
			if ( honors_db[i].assigned ) {
				num_assigned++;
			}
		}
		
		if ( display_mode == 'view' ) {
			if ( show && honors_db[i].assigned ) {
				e.style.display = 'block';
			} else {
				e.style.display = 'none';
			}
			if ( honors_db[i].selected ) {
				e.className = "closed";
				for( j=0; j < member_db.length; j++ ) {
					var f = document.getElementById( 'member_' + member_db[j].id );
					if( member_db[j].id != honors_db[i].assigned ) {
						f.className = "";
					} else {
						f.className = "closed";
					}
				}
			} else {
				e.className = "";
			}

		} else {
			if ( show && ! honors_db[i].assigned ) {
				e.style.display = 'block';
				if ( honors_db[i].selected ) {
					e.className = "closed";
				} else {
					e.className = "";
				}
			} else {
				e.style.display = 'none';
			}
		}
	}
	*/
	e = document.getElementById('tot-honors');
	e.innerHTML = num_assigned.toString() + '/' + num_visible.toString() + " assigned";
}

function myDisplayMembers()  {
	var i, j, e, ok, show;
	
	for( cat of member_buttons ) {
		if ( member_buttons_live.indexOf(cat) >= 0 ) {
			document.getElementById(cat).className = "highlighted";
		} else {
			document.getElementById(cat).className = "";
		}
	}

	var num_visible = 0;
	var num_assigned = 0;
	
	if ( display_mode == 'assign' ) {
		e = document.getElementById( 'members-div' );
		for( i=0; i < e.children.length; i++ ) {
			var tmp = e.children[i].id.split(/_/);
			var id=tmp[1];
			ok = 0;
			for( cat of member_buttons_live ) {
				if ( members_db[id][cat.substr(4)] ) { // categories are opt-***
					ok = 1;
				}
			}
			if ( ok ) {
				num_visible++;
				if( members_db[id].assigned ) {
					e.children[i].className = "hidden";
					num_assigned++;
				} else if( members_db[id].selected ) {
					e.children[i].className = "highlighted";
				} else {
					e.children[i].className = "visible";
				}
			} else {
					e.children[i].className = "hidden";
			}
		}
	}
	/*
	for( i=0; i<member_db.length; i++ ) {
		e = document.getElementById( 'member_' + member_db[i].id );

		show = 0;
		for( j=0; j<member_buttons_live.length; j++ ) {
			var tmp = member_buttons_live[j].split("-");
			var attr = tmp[1];
			if ( member_db[i][attr] ) {
				show = 1;
			}
		}
		
		if ( show ) {
			num_visible++;
			if ( member_db[i].assigned ) {
				num_assigned++;
			}
		}
		
		if ( display_mode == 'view' ) {
			if ( show && member_db[i].assigned ) {
				e.style.display = 'block';
			} else {
				e.style.display = 'none';
			}
			
			if ( member_db[i].selected ) {
				e.className = "closed";
				for( j=0; j < honors_db.length; j++ ) {
					var f = document.getElementById( 'honor_' + honors_db[j].id );
					if ( honors_db[j].id != member_db[i].assigned ) {
						f.className = "";
					} else {
						f.className = "closed";
						myClickDay( honors_db[j].service, 1 );
					}
				}
			} else {
				e.className = "";
			}
			
		} else {
			if ( show && ! member_db[i].assigned ) {
				e.style.display='block';
				if ( member_db[i].selected ) {
					e.className = "closed";
				} else {
					e.className = "";
				}
			} else {
				e.style.display='none';
			}
		}
	}
	*/
	e = document.getElementById('tot-members');
	e.innerHTML = num_assigned.toString() + '/' + num_visible.toString() + " assigned";
}

function myDisplayRefresh() {
	if( honor_refresh ) {
		myDisplayHonors();
		honor_refresh = 0;
	}
	if( member_refresh ) {
		myDisplayMembers();
		member_refresh = 0;
	}
}

function myHighlightAction() {
	var e, i, needed;
	if ( display_mode == 'view' ) { // Option to delete, only need 1 click
		e = document.getElementById( 'action-view' );
		e.className = 'del';
		e.disabled = false;

	} else { // Option to add, need 2 clicks
		needed = 0;
		honors_db.forEach( function xx( obj ) { if ( obj.selected ) { needed++; } } );
		members_db.forEach( function xx( obj ) { if ( obj.selected ) { needed++; } } );
		if ( needed == 2 ) {
			e = document.getElementById( 'action-assign');
			e.className = 'add';
			e.disabled = false;
		}
	}
	
}

function mySetMode(id) {
	var e = document.getElementsByTagName( "input");
	for ( var i=0; i < e.length; i++ ) {
		if (e[i].id.match(/^mode-/) ) {
			e[i].className = "";
			if ( e[i].id == 'mode-' + id ) {
				e[i].className = "highlighted";
			}
			display_mode = id;
		}
	}
	document.getElementById('div-action-' + id ).style.display = "block";
	if ( id == 'view' ) {
		document.getElementById('div-action-assign' ).style.display = "none";
	} else {
		document.getElementById('div-action-view' ).style.display = "none";
	}

	myDisplayRefresh();
}

function mySaveChoices() {
	var i;
	honors_db.forEach( function xx( obj, id ) {
		if ( obj.selected ) {
			addField( 'honor_' + id );
		}
	});
	members_db.forEach( function xx( obj, id ) {
		if ( obj.selected ) {
			addField( 'member_' + id );
		}
	});
	
	for( i=0; i<honor_buttons_live.length; i++ ) {
		addField( honor_buttons_live[i] );
	}
	for( i=0; i<member_buttons_live.length; i++ ) {
		addField( member_buttons_live[i] );
	}
	addField( 'mode_' + display_mode );
}