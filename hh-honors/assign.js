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

function cong_select(id) {
	for( var i=0; i<cong_db.length; i++ ) {
		if ( cong_db[i].id == id ) {
			cong_db[i].selected = 1;
		} else if ( cong_db[i].selected ) {
			cong_db[i].selected = 0;
		}
	}
}

function honors_select(id) {
	for( var i=0; i<honors_db.length; i++ ) {
		if ( honors_db[i].id == id ) {
			honors_db[i].selected = 1;
		} else if ( honors_db[i].selected ) {
			honors_db[i].selected = 0;
		}
	}
}

function myCategoryClick(id,forced_state) {
	var pos;
	
	if ( id == 'opt-all' ) {
		cong_buttons_live = cong_buttons;
		
	} else if ( id == 'opt-none' ) {
		cong_buttons_live = [];
		
	} else if ( arguments.length == 1 ) { //toggle mode
		pos = cong_buttons_live.indexOf(id);
		if ( pos >= 0 ) {
			cong_buttons_live.splice(pos,1); // remove
		} else {
			cong_buttons_live.push(id); // add
		}
		
	} else if ( arguments.length == 2 ) { //forced mode
		pos = cong_buttons_live.indexOf(id);
		if ( forced_state && pos < 0 ) {
			cong_buttons_live.push(id);
		} else if ( ! forced_state && pos >= 0 ) {
			cong_buttons_live.splice(pos,1);
		}
	}
}

function myCongClick(id) {
	var e = document.getElementById('cong-div');
	
	if ( assign_mode == 'assign' ) {
		for( var i=0; i<e.children.length; i++ ) {
			if ( e.children[i].id == "cong_" + id ) {
				e.children[i].className = "closed";
			} else {
				e.children[i].className = "";
			}
		}
		cong_select(id);
		
	} else {
		for( var i=0; i<cong_db.length; i++ ) {
			if( cong_db[i].id == id ) {
				cong_db[i].selected = 1;
				document.getElementById('cong_' + cong_db[i].id ).className = "closed";
				document.getElementById('honor_' + cong_db[i].assigned ).className = "closed";
			} else if ( cong_db[i].selected ) {
				cong_db[i].selected = 0;			
				document.getElementById('cong_' + cong_db[i].id ).className = "";
				document.getElementById('honor_' + cong_db[i].assigned ).className = "";
			}
		}
	}
	myHighlightAction();
}

function myDayClick(id,forced_state) {
	var pos;
	
	if ( id == 'day-all' ) {
		day_buttons_live = day_buttons
		
	} else if ( id == 'day-none' ) {
		day_buttons_live = [];

	} else if ( arguments.length == 1 ) {  //toggle mode
		pos = day_buttons_live.indexOf(id);
		if( pos >= 0 ) {
			day_buttons_live.splice(pos,1); // remove
		} else {
			day_buttons_live.push(id); // add
		}
		
	} else if ( arguments.length == 2 ) { //forced mode
		pos = day_buttons_live.indexOf(id);
		if ( forced_state && pos < 0 ) {
			day_buttons_live.push(id);
		} else if ( ! forced_state && pos >= 0 ) {
			day_buttons_live.splice(pos,1);
		}
	}
}

function myDisplayCong()  {
	var i, j, e, ok, show;
	
	for( cat of cong_buttons ) {
		if ( cong_buttons_live.indexOf(cat) >= 0 ) {
			document.getElementById(cat).className = "closed";
		} else {
			document.getElementById(cat).className = "";
		}
	}

	var num_visible = 0;
	var num_assigned = 0;
	
	for( i=0; i<cong_db.length; i++ ) {
		e = document.getElementById( 'cong_' + cong_db[i].id );

		show = 0;
		for( j=0; j<cong_buttons_live.length; j++ ) {
			var tmp = cong_buttons_live[j].split("-");
			var attr = tmp[1];
			if ( cong_db[i][attr] ) {
				show = 1;
			}
		}
		
		if ( show ) {
			num_visible++;
			if ( cong_db[i].assigned ) {
				num_assigned++;
			}
		}
		
		if ( assign_mode == 'view' ) {
			if ( show && cong_db[i].assigned ) {
				e.style.display = 'block';
			} else {
				e.style.display = 'none';
			}
			
			if ( cong_db[i].selected ) {
				e.className = "closed";
				for( j=0; j < honors_db.length; j++ ) {
					var f = document.getElementById( 'honor_' + honors_db[j].id );
					if ( honors_db[j].id != cong_db[i].assigned ) {
						f.className = "";
					} else {
						f.className = "closed";
						myDayClick( honors_db[j].service, 1 );
					}
				}
			} else {
				e.className = "";
			}
			
		} else {
			if ( show && ! cong_db[i].assigned ) {
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
	var i, j, e, ok, show;
	
	for( day of day_buttons ) {
		if ( day_buttons_live.indexOf(day) >= 0 ) {
			document.getElementById(day).className = "closed";
		} else {
			document.getElementById(day).className = "";
		}
	}

	var num_visible = 0;
	var num_assigned = 0;
	
	for( i=0; i < honors_db.length; i++ ) {
		e = document.getElementById( 'honor_' + honors_db[i].id );
		
		show = day_buttons_live.indexOf( honors_db[i].service ) >= 0;
		if ( show ) {
			num_visible++;
			if ( honors_db[i].assigned ) {
				num_assigned++;
			}
		}
		
		if ( assign_mode == 'view' ) {
			if ( show && honors_db[i].assigned ) {
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
	e = document.getElementById('tot-honors');
	e.innerHTML = num_assigned.toString() + '/' + num_visible.toString();
}

function myDisplayRefresh() {
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
	var e = document.getElementById('honors-div');
	
	if ( assign_mode == 'assign' ) {
		for( var i=0; i<e.children.length; i++ ) {
			if( e.children[i].id == "honor_" + id ) {
				e.children[i].className = "closed";
			} else {
				e.children[i].className = "";
			}
		}
		honors_select(id);

	} else {
		for( var i=0; i<honors_db.length; i++ ) {
			if( honors_db[i].id == id ) {
				honors_db[i].selected = 1;
				document.getElementById('honor_' + honors_db[i].id ).className = "closed";
				document.getElementById('cong_' + honors_db[i].assigned ).className = "closed";
			} else if ( honors_db[i].selected ) {
				honors_db[i].selected = 0;			
				document.getElementById('honor_' + honors_db[i].id ).className = "";
				document.getElementById('cong_' + honors_db[i].assigned ).className = "";
			}
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

	myDisplayRefresh();
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