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

function myHonorsClick(id) {
	var e = document.getElementsByTagName("p");
	for ( var i=0; i < e.length; i++ ) {
		if ( e[i].id.match(/^honor_/) ) {
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

function myPress(id) {
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