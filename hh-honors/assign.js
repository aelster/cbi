var cong_buttons = [];
var cong_buttons_live = [];
var day_buttons = [];
var day_buttons_live = [];
var honors_db = [];
var cong_db = [];
var assign_mode = "view";  // View or Assign

function button_init() {
	var e = document.getElementsByTagName("input");
	for( var i=0; i < e.length; i++ ) {
		if ( e[i].id.match(/-all$/ ) ) {
			continue;  // Don't load the All key
		} else if ( e[i].id.match(/^day/) ) {
			day_buttons.push( e[i].id );
		} else if (e[i].id.match(/^opt/)) {
			cong_buttons.push( e[i].id );
		}
	}
}

function myCategoryClick(id) {
	var i, found;
	var new_live = [];
	found = 0;
	for( i=0; i<cong_buttons_live.length; i++ ) {
		if( id == cong_buttons_live[i] ) {
			found = 1;
		}
	}
	if ( found ) {
		for( i=0; i<cong_buttons_live.length; i++ ) {
			if ( id != cong_buttons_live[i] ) {
				new_live.push( cong_buttons_live[i] );
			}
		}
		cong_buttons_live = new_live;
	} else {
		cong_buttons_live.push(id);
	}
	myDisplayCong();
}

function myCongClick(id) {
	for( var i=0; i<cong_db.length; i++ ) {
		if( cong_db[i].id == id ) {
			cong_db[i].selected = 1;
		} else {
			cong_db[i].selected = 0;			
		}
	}
	myHighlightAction();
	myDisplayCong();
}

function myDayClick(id) {
	var i, found, e;
	var new_live = [];
	found = 0;
	for( i=0; i<day_buttons_live.length; i++ ) {
		if( id == day_buttons_live[i] ) {
			found = 1;
		}
	}
	if ( found ) {
		for( i=0; i<day_buttons_live.length; i++ ) {
			if ( id != day_buttons_live[i] ) {
				new_live.push( day_buttons_live[i] );
			}
		}
		day_buttons_live = new_live;
	} else {
		day_buttons_live.push(id);
	}
	e = document.getElementById('day-all');
	if ( day_buttons_live.length == day_buttons.length ) {
		e.value = 'None';
	} else {
		e.value = 'All';
	}
	myDisplayHonors();
}

function myDisplayCong()  {
	var i, j, e, ok, found;
	// Turn off the color of all Congregant Button Selectors
	for( i=0; i<cong_buttons.length; i++ ) {
		e = document.getElementById(cong_buttons[i]);
		e.className = "";
	}
	// Turn on the color of the live buttons
	for( i=0; i<cong_buttons_live.length; i++ ) {
		e = document.getElementById(cong_buttons_live[i]);
		e.className = "closed";
	}
	var num_visible = 0;
	var num_assigned = 0;
	
	for( i=0; i<cong_db.length; i++ ) {
		e = document.getElementById( 'cong_' + cong_db[i].id );
		found = 0;
		for( j=0; j<cong_buttons_live.length; j++ ) {
			var tmp = cong_buttons_live[j].split("-");
			var attr = tmp[1];
			if ( cong_db[i][attr] ) {
				found = 1;
				num_visible++;
				if ( cong_db[i].assigned ) {
					num_assigned++;
				}
			}
		}
		if ( assign_mode == 'view' ) {
			if ( found && cong_db[i].assigned ) {
				e.style.display = 'block';
			} else {
				e.style.display = 'none';
			}
		} else {
			if ( found && ! cong_db[i].assigned ) {
				e.style.display='block';
				if ( cong_db[i].selected ) {
					e.className = "closed";
				} else {
					e.className = "";
				}
			} else {
				e.style.display='none';
			}
		}
	}
	e = document.getElementById('tot-cong');
	e.innerHTML = num_assigned.toString() + '/' + num_visible.toString();
}

function myDisplayHonors()  {
	var i, j, e, ok, found;
	
	for( i=0; i<day_buttons.length; i++ ) {
		e = document.getElementById(day_buttons[i]);
		e.className = "";
	}
	for( i=0; i<day_buttons_live.length; i++ ) {
		e = document.getElementById(day_buttons_live[i]);
		e.className = "closed";
	}
	var num_visible = 0;
	var num_assigned = 0;
	
	for( i=0; i < honors_db.length; i++ ) {
		e = document.getElementById( 'honor_' + honors_db[i].id );
		
		for( j=0; j<day_buttons_live.length; j++ ) {
			if ( honors_db[i].service.match( day_buttons_live[j] ) ) {
				found = 1;
				num_visible++;
				if ( honors_db[i].assigned ) {
						num_assigned++;
				}
			}
		}
		
		if ( assign_mode == 'view' ) {
			if ( found && honors_db[i].assigned ) {
				e.style.display = 'block';
			} else {
				e.style.display = 'none';
			}
			if ( honors_db[i].selected ) {
				e.className = "closed";
				for( j=0; j < cong_db.length; j++ ) {
					var f = document.getElementById( 'cong_' + cong_db[j].id );
					if( cong_db[j].id != honors_db[i].assigned ) {
						f.className = "";
					} else {
						f.className = "closed";
					}
				}
			} else {
				e.className = "";
			}

		} else {
			if ( found && ! honors_db[i].assigned ) {
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
	e = document.getElementById('tot-honors');
	e.innerHTML = num_assigned.toString() + '/' + num_visible.toString();
}

function myFilterReset(mode) {
	if ( mode == 'reset' ) {
		day_buttons_live = [];
		cong_buttons_live = [];
	} else if( mode == 'day-all' ) {
		var e = document.getElementById('day-all');
		if ( e.value == "All" ) {
			day_buttons_live = day_buttons;
			e.value = "None";
		} else {
			day_buttons_live = [];
			e.value = "All";
		}
	} else if( mode == 'opt-all' ) {
		var e = document.getElementById('opt-all');
		if ( e.value == "All" ) {
			cong_buttons_live = cong_buttons;
			e.value = "None";
		} else {
			cong_buttons_live = [];
			e.value = "All";
		}
	}
	myDisplayHonors();
	myDisplayCong();
}

function myHighlightAction() {
	var e, i, needed;
	if ( assign_mode == 'view' ) { // Option to delete, only need 1 click
		e = document.getElementById( 'action-view' );
		e.className = 'del';
		e.disabled = false;

	} else { // Option to add, need 2 clicks
		needed = 0;
		for( i=0; i<honors_db.length; i++ ) {
			if ( honors_db[i].selected ) {
				needed++;
			}
		}
		for( i=0; i<cong_db.length; i++ ) {
			if ( cong_db[i].selected ) {
				needed++;
			}
		}
		if ( needed == 2 ) {
			e = document.getElementById( 'action-assign');
			e.className = 'add';
			e.disabled = false;
		}
	}
	
}

function myHonorsClick(id) {
	for( var i=0; i<honors_db.length; i++ ) {
		if( honors_db[i].id == id ) {
			honors_db[i].selected = 1;
		} else {
			honors_db[i].selected = 0;			
		}
	}
	myHighlightAction();
	myDisplayHonors();
}

function myPress(id) {
	if( id.match( /^day/ ) ) {
		
	}
}

function mySetMode(id) {
	var e = document.getElementsByTagName( "input");
	for ( var i=0; i < e.length; i++ ) {
		if (e[i].id.match(/^mode-/) ) {
			e[i].className = "";
			if ( e[i].id == 'mode-' + id ) {
				e[i].className = "closed";
			}
			assign_mode = id;
		}
	}
	document.getElementById('div-action-' + id ).style.display = "block";
	if ( id == 'view' ) {
		document.getElementById('div-action-assign' ).style.display = "none";
	} else {
		document.getElementById('div-action-view' ).style.display = "none";
	}

	myDisplayHonors();
	myDisplayCong();
}

function saveChoices() {
	var i;
	for( i=0; i<honors_db.length; i++ ) {
		if ( honors_db[i].selected ) {
			addField( 'honor_' + honors_db[i].id );
		}
	}
	for( i=0; i<cong_db.length; i++ ) {
		if ( cong_db[i].selected ) {
			addField( 'member_' + cong_db[i].id );
		}
	}
	for( i=0; i<day_buttons_live.length; i++ ) {
		addField( 'day_' + day_buttons_live[i] );
	}
	for( i=0; i<cong_buttons_live.length; i++ ) {
		addField( 'cong_' + cong_buttons_live[i] );
	}
	addField( 'mode_' + assign_mode );
}