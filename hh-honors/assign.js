var cong_buttons = [];
var cong_buttons_live = [];
var day_buttons = [];
var day_buttons_live = [];

function button_init() {
	var e = document.getElementsByTagName("input");
	for( var i=0; i < e.length; i++ ) {
		if ( e[i].id.match(/-all$/ ) ) {
			continue;  // Don't load the All key
		} else if ( e[i].id.match(/^day/) ) {
			day_buttons.push( e[i].id );
		} else if (e[i].id.match(/^cong/)) {
			cong_buttons.push( e[i].id );
		}
	}
}

function myCongClick(id) {
	var e = document.getElementsByTagName("p");
	for ( var i=0; i < e.length; i++ ) {
		if ( e[i].id.match(/^cong_/) ) {
			if ( e[i].id == id ) {
				if (e[i].className == "" ) {
					e[i].className = "closed";
				} else {
					e[i].className = "";
				}
			} else {
				e[i].className = "";
			}
		}
	}
}

function myDisplayHonors()  {
	var i, j, e;
	for( i=0; i<day_buttons.length; i++ ) {
		e = document.getElementById(day_buttons[i]);
		e.className = "";
	}
	for( i=0; i<day_buttons_live.length; i++ ) {
		e = document.getElementById(day_buttons_live[i]);
		e.className = "closed";
	}
	var visible = 0;
	
	for( i=0; i<honors_db.length; i++ ) {
		var found = 0;
		for( j=0; j<day_buttons_live.length; j++ ) {
			if ( honors_db[i].service.match( day_buttons_live[j] ) ) {
				found = 1;
			}
		}
		e = document.getElementById( 'honor_' + honors_db[i].id );
		if ( found ) {
			e.style.display='block';
			if ( honors_db[i].selected ) {
				e.className = "closed";
			} else {
				e.className = "";
			}
			visible++;
		} else {
			e.style.display='none';
		}
	}
//	e = document.getElementById('tot-honors');
//	e.textContent = "(" + visible + "/" + honors_db.length + ")";
	
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

function oldMyPress(id) {
	var e, b, i;
	if ( id == 'filter-reset' ) {
		e = document.getElementsByTagName('input'); 
		for( i=0; i < e.length; i++ ) {
			if( e[i].id.match(/^day-/) || e[i].id.match(/^opt-/) ) {
				e[i].className = '';
			}
		}
		e = document.getElementById('day-all');
		e.value = 'All';
		e.className = "closed";
		e = document.getElementById('opt-all');
		e.value = 'All';
		e.className = "closed";
		
   } else if( id == 'day-all' ) {
		var t = document.getElementById('day-all');
		var state = t.value;
		
		e = document.getElementsByTagName('input'); 
		for( i=0; i < e.length; i++ ) {
			if(e[i].id.match(/^day-/) ) {
				if( state == 'All' ) {
					e[i].className = 'closed';
				} else {
					e[i].className = '';
				}
			}
		}
		if( t.value == 'All' ) {
			t.value = 'None';
			t.className = "";
		} else {
			t.value = 'All';
			t.className = "closed";
		}
		
   } else if( id == 'opt-all' ) {
		var t = document.getElementById('opt-all');
		var state = t.value;
		
		e = document.getElementsByTagName('input'); 
		for( i=0; i < e.length; i++ ) {
			if(e[i].id.match(/^opt-/) ) {
				if( state == 'All' ) {
					e[i].className = 'closed';
				} else {
					e[i].className = '';
				}
			}
		}
		if( t.value == 'All' ) {
			t.value = 'None';
			t.className = "";
		} else {
			t.value = 'All';
			t.className = "closed";
		}
		
	} else {
	   var e = document.getElementById(id);
	   if( e.className == '' ) {
		   e.className = 'closed';
	   } else {
		   e.className = '';
	   }
   }
}