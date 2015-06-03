// JavaScript Document

function clearSpiritOther() {
	var e = document.getElementById('spiritOther');
	e.value = '';
	e.focus();
}

function firstName() {
	var e = document.getElementById('firstName');
	if(e) e.focus();
}

function formatPhone(num) {
	var i;
	var str = '';
	if ( num.length == 7 ) {
		for ( i=0; i < 3; i++ ) {
			str += num.charAt(i);
		}
		str += '-';
		for ( i=3; i<7; i++ ) {
			str += num.charAt(i);
		}

	} else if ( num.length == 10 ) {
		str += '(';
		for ( i=0; i < 3; i++ ) {
			str += num.charAt(i);
		}
		str += ') ';
		for ( i=3; i < 6; i++ ) {
			str += num.charAt(i);
		}
		str += '-';
		for ( i=6; i<10; i++ ) {
			str += num.charAt(i);
		}
	
	}
	return str;
}

function goToURL(page) {
	window.location.href = page;
}

function makeActive(area) {
	//background-color: #ADB96E;
	var e, ok;
	var num_filled = 0;
	
	if( area == 'pledges' ) {
		e = document.getElementsByName('Pledges');
		for( var i=0; i<e.length; i++ ) {
			if( e[i].checked ) {
				if( e[i].value == 'other' ) {
					var f = document.getElementById('pledgeOther');
					f = f.value.replace(/,/g,'');
					if( f > 0 ) ok = 1;
				} else {
					ok = 1;
				}
			}
		}
		e = document.getElementById('pledgeNow');
		if( ok ) {
			e.className = 'buttonOk';
			e.disabled = false;
		} else {
			e.className = 'buttonNotOk';
			e.disabled = true;
		}
		
	} else if( area == 'spirit' ) {
		e = document.getElementsByName('spirit');
		for( var i=0; i<e.length; i++ ) {
			if( e[i].id == 'other' ) {
				var f = document.getElementById('spiritOther');
				if( e[i].checked ) {
					if( f.value !== '' ) ok = 1;
				} else {
					f.value = 'Please enter description';
				}
			} else {
				if( e[i].checked ) ok = 1;
			}
		}
		e = document.getElementById('spiritNow');
		if( ok ) {
			e.className = 'buttonOk';
			e.disabled = false;
		} else {
			e.className = 'buttonNotOk';
			e.disabled = true;
		}
		
	} else if( area == 'confirm' ) {
		e = document.getElementsByTagName('input');
		var num_required = 3;
		for( var i=0; i<e.length; i++ ) {
			if( e[i].id == 'bidder_email' ) {
				num_filled += validateEmail(e[i]);
			} else if ( e[i].id == 'bidder_first' ) {
				if( e[i].value !== '' ) num_filled++;
			} else if ( e[i].id == 'bidder_last' ) {
				if( e[i].value !== '' ) num_filled++;
			} else if ( e[i].id == 'bidder_phone' ) {
				num_filled += validatePhone(e[i]);
				num_required = 4;
			}
		}
		e = document.getElementById('confirm');
		ok = ( num_filled == num_required );
		if( ok ) {
			e.className = 'buttonOk';
			e.disabled = false;
		} else {
			e.className = 'buttonNotOk';
			e.disabled = true;
		}
		
	} else if( area == 'paynow' ) {
		e = document.getElementsByName('paynow');
		var radio = 0;
		for( var i=0; i<e.length; i++ ) {
			if( e[i].type == 'text' ) {
				if ( e[i].id == 'phone' ) {
					num_filled += validatePhone(e[i]);
				} else if ( e[i].id == 'email' ) {
					num_filled += validateEmail(e[i]);
				} else {
					if( e[i].value !== '' ) num_filled++;
				}
			} else if( e[i].type == 'radio' ) {
				if( e[i].checked ) radio++;
			}
		}
		
		ok = ( num_filled == 4 );
		if( radio_required ) {
			ok = ok && ( radio > 0 );
		}
		e = document.getElementById('paynow');
		if( ok ) {
			e.className = 'buttonOk';
			e.disabled = false;
		} else {
			e.className = 'buttonNotOk';
			e.disabled = true;
		}
	}
}

function myScroll($id) {
	var r = document.getElementById('itm_' + $id );
	for( var i=0; i<r.cells.length; i++ ) {
		var e = r.cells[i];
		e.style.backgroundColor = '#F5DEB3';
	}
	r.scrollIntoView(true);
}

function payNow() {
	var items = new Array();
	var keys = new Array( 'lastName', 'firstName', 'phone', 'email' );
	var e;
	for( var i=0; i<keys.length; i++ ) {
		e = document.getElementById(keys[i]);
		items.push( keys[i] + '=' + e.value );
	}
	if( gFrom == 'financial' ) {
		items.push( 'amount=' + pledge_amount );
	} else {
		if( pledgeIds.length ) {
			var str = pledgeIds.join(',');
			items.push( 'pledgeIds=' + str );
		}
		if( pledgeOther ) {
			items.push( 'pledgeOther=' + pledgeOther );
		}
	}
	e = document.getElementById('fields');
	e.value = items.join('|');
}

function paypal() {
	var id, n;
	id = document.getElementById('lastName');
	n = document.getElementsByName('lastName');
	n[0].value = id.value;
	
	id = document.getElementById('firstName');
	n = document.getElementsByName('firstName');
	n[0].value = id.value;
	
	id = document.getElementById('phone');
	n = document.getElementsByName('phone');
	n[0].value = id.value;
	
	id = document.getElementById('email');
	n = document.getElementsByName('email');
	n[0].value = id.value;
	
	n = document.getElementsByName('amount');
	n[0].value = pledge_amount;
}

function setAmount() {
	var e = document.getElementsByName('Pledges');
	for( var i=0; i<e.length; i++ ) {
		if( e[i].checked ) {
			if( e[i].value == 'other' ) {
				var t = document.getElementById('pledgeOther').value;
				var x = t.replace(/,/g,'');
		} else {
				var x = e[i].value;
			}
		}
	}
	e = document.getElementById('amount');
	e.value = x;
}

function spiritFields() {
	var items = new Array();
	var e = document.getElementsByName('spirit');
	for( var i=0; i<e.length; i++ ) {
		if( e[i].type == 'checkbox' && e[i].checked ) {
			if( e[i].id == 'other' ) {
				var f = document.getElementById('spiritOther');
				items.push( f.value );
			} else {
				items.push( e[i].id );
/*
				var f = e[i].parentNode;
				for( var j=0; j<f.childNodes.length; j++ ) {
				var g = f.childNodes[j];
					if( f.childNodes[j].nodeType == 3 ) {  // TEXT_NODE
						items.push( f.childNodes[j].nodeValue );
					}
				}
*/
			}
		}
	}
	e = document.getElementById('fields');
	e.value = items.join('|');
}

function toggleBgRedGreen( id ) {
   var e = document.getElementById( id );
	if( ! e ) alert( "Can't find id: " + id );
	if( e.style.backgroundColor == '#0f0' ) {
		e.style.backgroundColor = '#f00';
	} else {
		e.style.backgroundColor = '#0f0';
	}
}

function trim(s)
{
  return s.replace(/^\s+|\s+$/, '');
} 

function validateEmail(fld) {
	var status = 0;
	var tfld = trim(fld.value);                        // value of field with whitespace trimmed off
	var emailFilter = /^[^@]+@[^@.]+\.[^@]*\w\w$/ ;
	var illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/ ;
    
	if (fld.value == "") {
		status = 0;
   } else if (!emailFilter.test(tfld)) {              //test email for illegal characters
      fld.style.background = 'Yellow';
		status = 0;
	} else if (fld.value.match(illegalChars)) {
      fld.style.background = 'Yellow';
      status = 0;
   } else {
      fld.style.background = 'White';
		status = 1;
   }
   return status;
}

function validatePhone(fld) {
   var status = 0;
//   var stripped = fld.value.replace(/[\(\)\.\-\ ]/g, '');     
   var stripped = fld.value.replace(/[^0-9]/g, '');     

   if (fld.value == "") {
		status = 0;
	} else if (isNaN(parseInt(stripped))) {
      fld.value = stripped.value;
   } else if (!(stripped.length == 10 || stripped.length == 7 )) {
      fld.style.background = 'Yellow';
   } else {
		status = 1;
	}
	if ( status ) {
		fld.style.background = '';
		fld.value = formatPhone(stripped);
	}
   return status;
}
