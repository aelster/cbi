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
		e = document.getElementById('opt-all');
		e.value = 'All';
		
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
		} else {
			t.value = 'All';
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
		} else {
			t.value = 'All';
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