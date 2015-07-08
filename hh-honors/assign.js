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
	myDisplayCong();
}

function myDayClick(id) {
	var i, found;
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
	myDisplayHonors();
}

function myDisplayCong()  {
	var i, j, e, ok;
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
	var visible = 0;
	
	for( i=0; i<cong_db.length; i++ ) {
		e = document.getElementById( 'cong_' + cong_db[i].id );
		if ( assign_mode == 'view' ) {
			if ( cong_db[i].assigned ) {
				e.style.display = 'block';
			} else {
				e.style.display = 'none';
			}
		} else {
			var found = 0;
			for( j=0; j<cong_buttons_live.length; j++ ) {
				var tmp = cong_buttons_live[j].split("-");
				var attr = tmp[1];
				if ( cong_db[i][attr] ) {
					found = 1;
				}
			}
			if ( found ) {
				e.style.display='block';
				if ( cong_db[i].selected ) {
					e.className = "closed";
				} else {
					e.className = "";
				}
				visible++;
			} else {
				e.style.display='none';
			}
		}
	}
}

function myDisplayHonors()  {
	var i, j, e, ok;
	for( i=0; i<day_buttons.length; i++ ) {
		e = document.getElementById(day_buttons[i]);
		e.className = "";
	}
	for( i=0; i<day_buttons_live.length; i++ ) {
		e = document.getElementById(day_buttons_live[i]);
		e.className = "closed";
	}
	var visible = 0;
	
	for( i=0; i < honors_db.length; i++ ) {
		e = document.getElementById( 'honor_' + honors_db[i].id );
		if ( assign_mode == 'view' ) {
			if ( honors_db[i].assigned ) {
				e.style.display = 'block';
			} else {
				e.style.display = 'none';
			}
		} else {
			var found = 0;
			for( j=0; j<day_buttons_live.length; j++ ) {
				if ( honors_db[i].service.match( day_buttons_live[j] ) ) {
					found = 1;
				}
			}
			if ( found ) {
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
}

function myDisplayMode(id) {
	var e = document.getElementById('mode-' + id );
	e.className = "closed";
	assign_mode = id;
	myDisplayHonors();
	myDisplayCong();
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
	}
	myDisplayHonors();
}

function myHonorsClick(id) {
	for( var i=0; i<honors_db.length; i++ ) {
		if( honors_db[i].id == id ) {
			honors_db[i].selected = 1;
		} else {
			honors_db[i].selected = 0;			
		}
	}
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
	myDisplayHonors(1);
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
}