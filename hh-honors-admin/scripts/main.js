function setAllOff () {
    var v = document.getElementsByClassName('sendto');
    var i;
    for( i=0; i < v.length; i++ ) {
        v[i].checked = false;
    }
}

function setAllOn () {
    var v = document.getElementsByClassName('sendto');
    var i;
    for( i=0; i < v.length; i++ ) {
        v[i].checked = true;
    }
}

function copyEmailParts () {
    var v1, v2;
// Copy the Subject:
    v1 = document.getElementById('subject');
    v2 = document.getElementById('subject-div');
    v1.value = v2.innerHTML;

// Copy the Body:
    v1 = document.getElementById('body');
    v2 = document.getElementById('body-div');
    v1.value = v2.innerHTML;
}

function loginSetFocus() {
    var v1 = document.getElementById('username');
    if( v1 ) {
        setFocus('username');
        v1.select();
    }
}