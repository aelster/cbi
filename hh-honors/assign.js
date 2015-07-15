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
	for( var i=0; i<members_db.length; i++ ) {
		if ( members_db[i].id == id ) {
			members_status[i].selected = 1;
		} else if ( members_status[i].selected ) {
			members_status[i].selected = 0;
		}
	}
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

function myClickHonor(id,opt_click_members) {
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
				for( j=0; j < members_db.length; j++ ) {
					var f = document.getElementById( 'member_' + members_db[j].id );
					if( members_db[j].id != honors_db[i].assigned ) {
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
				} else {if( members_status[id].assigned ) {
					e.children[i].className = "hidden";
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
					num_assigned++;
				} else if( members_status[id].assigned ) {
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

	/*
	for( i=0; i<members_db.length; i++ ) {
		e = document.getElementById( 'member_' + members_db[i].id );

		show = 0;
		for( j=0; j<member_buttons_live.length; j++ ) {
			var tmp = member_buttons_live[j].split("-");
			var attr = tmp[1];
			if ( members_db[i][attr] ) {
				show = 1;
			}
		}
		
		if ( show ) {
			num_visible++;
			if ( members_status[i].assigned ) {
				num_assigned++;
			}
		}
		
		if ( display_mode == 'view' ) {
			if ( show && members_status[i].assigned ) {
				e.style.display = 'block';
			} else {
				e.style.display = 'none';
			}
			
			if ( members_status[i].selected ) {
				e.className = "closed";
				for( j=0; j < honors_db.length; j++ ) {
					var f = document.getElementById( 'honor_' + honors_db[j].id );
					if ( honors_db[j].id != members_status[i].assigned ) {
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
			if ( show && ! members_status[i].assigned ) {
				e.style.display='block';
				if ( members_status[i].selected ) {
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
	myHighlightAction();
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
		members_db.forEach( function xx( obj, id ) { if ( members_status[id].selected ) { needed++; } } );
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
	honors_db.forEach( function xx(honor) { honor.selected = 0; } );
	members_status.forEach( function xx(member) { member.selected = 0; } );

	honor_refresh = 1;
	member_refresh = 1;
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