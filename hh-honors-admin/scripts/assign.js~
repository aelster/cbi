var honor_buttons = [];
var honor_buttons_live = [];
var honor_refresh = 0;
var honor_selected = 0;
var honors_db = [];

var member_buttons = [];
var member_buttons_live = [];
var member_refresh = 0;
var member_selected = 0;
var members_status = [];
var members_db = [];

var display_mode = "view";  // View or Assign

var click_stack = [];

if ( ! debug_disabled ) {
	createDebugWindow();
}

function myButtonInit() {
	if ( ! debug_disabled ) {
		debug( 'myButtonInit()' );
	}
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

function myClickCategory(id,forced_state) {
	if ( ! debug_disabled ) {
		var str = 'myClickCategory(';
		str += 'id:' + id;
		if ( arguments.length > 1 ) {
			str += ',forced_state:' + forced_state; 
		}
		str += ')';
		debug( str );
	}
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
	if ( ! debug_disabled ) {
		var str = 'myClickDay(';
		str += 'id:' + id;
		if ( arguments.length > 1 ) {
			str += ',forced_state:' + forced_state; 
		}
		str += ')';
		debug( str );
	}
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

function myClickHonor(id,opt_click_members) {
	if ( ! debug_disabled ) {
		var str = 'myClickHonor(';
		str += 'id:' + id;
		if ( arguments.length > 1 ) {
			str += ',opt_click_members:' + opt_click_members; 
		}
		str += ')';
		debug( str );
	}
	click_stack.push('honor');
	if ( display_mode == 'assign' ) {
		honors_db.forEach( function xx( honor, hid ) {
			if ( hid == id ) {
				honor.selected = 1;
			} else {
				honor.selected = 0;
			}
		} );
		honor_refresh = 1;

	} else {
		honors_db.forEach( function xx( honor, hid ) {
			if ( hid == id ) {
				honors_db[hid].selected = 1;
				for( cat in members_db[honor.assigned]) {
					if ( members_db[honor.assigned][cat] ) {
						myClickCategory('opt-' + cat,1);
					}
				}
			} else {
				honors_db[hid].selected = 0;
			}
		} );
		if( arguments.length == 1 ) {
			myClickMember( honors_db[id].assigned, 0 );
		}

		honor_refresh = 1;
		member_refresh = 1;
	}
}

function myClickMember(id, opt_click_honors) {
	if ( ! debug_disabled ) {
		var str = 'myClickMember(';
		str += 'id:' + id;
		if ( arguments.length > 1 ) {
			str += ',opt_click_honors:' + opt_click_honors; 
		}
		str += ')';
		debug( str );
	}
	
	click_stack.push('member');
	if ( display_mode == 'assign' ) {
		members_db.forEach(function xx(member, mid) {
			if (mid == id) {
				members_status[mid].selected = 1;
			} else {
				members_status[mid].selected = 0;
			}
		});
		member_refresh = 1;		

	} else {
		members_db.forEach( function xx( member, mid ) {
			if ( mid == id ) {
				members_status[mid].selected = 1;
			} else {
				members_status[mid].selected = 0;
			}
		})
		if( arguments.length == 1 ) {
			myClickHonor(members_status[id].assigned);
		}

		member_refresh = 1;
		honor_refresh = 1;
	}
}

function myDisplayHonors()  {
	if ( ! debug_disabled ) {
		debug( 'myDisplayHonors' );
	}
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
				if( honors_db[id].selected ) {
					e.children[i].className = "highlighted";
					num_assigned++;
				} else if( honors_db[id].assigned ) {
					e.children[i].className = "hidden";
					num_assigned++;
				} else {
					e.children[i].className = "visible";
				}
			} else {
				e.children[i].className = "hidden";
			}
		}
		
	} else {
		e = document.getElementById( 'honors-div' );
		for( i=0; i<e.children.length; i++ ) {
			var id = e.children[i].id.substr(6); // id's are honor_***
			if ( honor_buttons_live.indexOf(honors_db[id].service) >= 0 ) {
				num_visible++;
				if( honors_db[id].selected ) {
					e.children[i].className = "highlighted";
					if ( click_stack[0] == 'member') {
						var j = num_assigned - 10;
						if ( j < 0 ) { j = 0; }
						e.scrollTop = e.children[i].scrollHeight * j;
					}
					num_assigned++;
				} else if( honors_db[id].accepted ) {
					e.children[i].className = "accepted";
					num_assigned++;
				} else if( honors_db[id].assigned ) {
					e.children[i].className = "visible";
					num_assigned++;
				} else {
					e.children[i].className = "hidden";
				}
			} else {
				e.children[i].className = "hidden";
			}

		}
	}
	e = document.getElementById('tot-honors');
	e.innerHTML = 'Honors Assigned (' + num_assigned.toString() + '/' + num_visible.toString() + ')';
}

function myDisplayMembers()  {
	if ( ! debug_disabled ) {
		debug('myDisplayMembers');
	}
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
			var id = e.children[i].id.substr(7); // id's are honor_***
			ok = 0;
			for( cat of member_buttons_live ) {
				if ( members_db[id][cat.substr(4)] ) { // categories are opt-***
					ok = 1;
				}
			}
			if ( ok ) {
				num_visible++;
				if( members_status[id].selected ) {
					e.children[i].className = "highlighted";
					num_assigned++;
				} else if( members_status[id].accepted ) {
					e.children[i].className = "accepted";
					if ( click_stack[0] == 'honor') {
						var j = num_assigned - 10;
						if ( j < 0 ) { j = 0; }
						e.scrollTop = e.children[i].scrollHeight * j;
					}
					num_assigned++;
				} else if( members_status[id].declined > 0 ) {
					e.children[i].className = "declined";
					
				} else {if( members_status[id].assigned ) {
					e.children[i].className = "assigned";
					num_assigned++;
				} else 
					e.children[i].className = "visible";
				}
			} else {
					e.children[i].className = "hidden";
			}
		}
		
	} else {
		e = document.getElementById( 'members-div' );
		for( i=0; i<e.children.length; i++ ) {
			var id = e.children[i].id.substr(7); // id's are honor_***
			ok = 0;
			for( cat of member_buttons_live ) {
				if ( members_db[id][cat.substr(4)] ) { // categories are opt-***
					ok = 1;
				}
			}
			if ( ok ) {
				num_visible++;
				if ( members_status[id].selected ) {
					e.children[i].className = "highlighted";
					if ( click_stack[0] == 'honor') {
						var j = num_assigned - 10;
						if ( j < 0 ) { j = 0; }
						e.scrollTop = e.children[i].scrollHeight * j;
					}
					num_assigned++;
				} else if( members_status[id].accepted ) {
					e.children[i].className = "accepted";
					if ( click_stack[0] == 'honor') {
						var j = num_assigned - 10;
						if ( j < 0 ) { j = 0; }
						e.scrollTop = e.children[i].scrollHeight * j;
					}
					num_assigned++;
				} else if( members_status[id].assigned ) {
					e.children[i].className = "assigned";
					num_assigned++;
				} else {
					e.children[i].className = "hidden";
				}
			} else {
				e.children[i].className = "hidden";
			}

		}
	}
	e = document.getElementById('tot-members');
	e.innerHTML = 'Members Assigned (' + num_assigned.toString() + '/' + num_visible.toString() + ')';
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
	myHighlightAction();
	click_stack = [];
}

function myHighlightAction() {
	var e, i, needed;
	needed = 0;
	honors_db.forEach( function xx( obj ) { if ( obj.selected ) { needed++; } } );
	members_db.forEach( function xx( obj, id ) { if ( members_status[id].selected ) { needed++; } } );

	e = document.getElementById( 'action-' + display_mode );

	if ( needed == 2 ) {
		e.className = "action-" + display_mode + "-active";
		e.disabled = false;
		
		if ( display_mode == 'view' ) {
			e = document.getElementById('action-mail');
			e.className = "action-mail-active";
			e.disabled = false;
			
			e = document.getElementsByName('action-reply');
			for( i = 0; i<e.length; i++ ) {
				e[i].disabled = false;
			}
		}
	} else {
		e.className = "action-" + display_mode + "-visible";
		e.disabled = true;
	}
}

function mySaveChoices() {
	var i;
	honors_db.forEach( function xx( honor, id ) {
		if ( honor.selected ) {
			addField( 'honor_' + id );
		}
	});
	members_status.forEach( function xy( member, id ) {
		if ( member.selected ) {
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

function mySetMode(mode) {
	var e;
	
	document.getElementById( 'mode-' + display_mode ).className = "mode-off";
	document.getElementById( 'action-' + display_mode ).className = "action-" + mode + "-hidden";
	
	document.getElementById( 'mode-' + mode ).className = "mode-on";
	document.getElementById( 'action-' + mode ).className = "action-" + mode + "-visible";
	display_mode = mode;

	if ( display_mode == 'view' ) {
		document.getElementById('action-mail').className = "action-mail-visible";
		document.getElementById('reply-block').className = "action-visible";
		document.getElementById('preview').className = "preview-visible";
	} else {
		document.getElementById('action-mail').className = "action-mail-hidden";
		document.getElementById('reply-block').className = "action-hidden";
		document.getElementById('preview').className = "preview-hidden";

	}

	honors_db.forEach( function xx(honor) { honor.selected = 0; } );
	members_status.forEach( function xx(member) { member.selected = 0; } );

	honor_refresh = 1;
	member_refresh = 1;
	myDisplayRefresh();
}