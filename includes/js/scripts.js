var audio = new Audio();
var playing = false;
function loadplayer(stream, station, locat) {
	playing = false;
	if ($('#player').is(":visible")) {
		name = document.getElementById("player_btn").getAttribute("name");
		url = document.getElementById(name+'_url').getAttribute("name");
		locat = document.getElementById(name+'_locat').getAttribute("name");
		loads = "loadplayer('"+url+"','"+name+"', '"+locat+"')";
		document.getElementById(`${name}`+'_btn').className = "play_btn";
		document.getElementById(`${name}`+'_btn').setAttribute('onclick', loads);
		audio.pause();
		audio.src = stream;
		playaudio(station);
		if (playing = true) {
		  audio.oncanplay = function(){
			  $( "#player_name" ).html(`<p>${station}</p>`);
			  $( "#player_location" ).html(`<p>${locat}</p>`);
			  document.getElementById("player_btn").className = "pause";
			  document.getElementById(`${station}`+'_btn').className = "pause_btn";
			  document.getElementById(`${station}`+'_btn').setAttribute('onclick', 'play()');
			  document.getElementById("player_btn").setAttribute("name", station);
		  };
		} else {
		  $( "#player_name" ).html(`<p>${station} is off air :(</p>`);
		}
	}else{
		$( "#player" ).addClass( "show" );
		audio.src = stream;
		playaudio(station);
		if (playing = true) {
		  audio.oncanplay = function(){
			  $( "#player_name" ).html(`<p>${station}</p>`);
			  $( "#player_location" ).html(`<p>${locat}</p>`);
			  document.getElementById("player_btn").className = "pause";
			  document.getElementById(`${station}`+'_btn').className = "pause_btn";
			  document.getElementById(`${station}`+'_btn').setAttribute('onclick', 'play()');
			  document.getElementById("player_btn").setAttribute("name", station);
		  };
		} else {
		  $( "#player_name" ).html(`<p>${station} is off air :(</p>`);
		}
	}
}

function playaudio(station) {
  audio.load();
  var hasPromiseSupport = typeof Promise === "function";

  var loadPromise = audio.play();

  // In browsers that donâ€™t yet support this functionality,
  // playPromise wonâ€™t be defined.
  if (hasPromiseSupport) {
    console.log(loadPromise);
    if (loadPromise !== undefined) {
      loadPromise.then(function() {
        // Automatic playback started!
        playing = true
      }).catch(function(error) {
        // Automatic playback failed.
        // Show a UI element to let the user manually start playback.
        // $( "#player_info" ).html(`<p>${station} is off air :(</p>`);
        playing = false
      });
    }
  } else {
      playing = true
  }



}

function play() {
	name = document.getElementById("player_btn").getAttribute("name");
	url = document.getElementById(name+'_url').getAttribute("name");
	locat = document.getElementById(name+'_locat').getAttribute("name");
	if (audio.paused) {
		audio.load();
		audio.play();
		loads = "loadplayer('"+url+"','"+name+"', '"+locat+"')";
		document.getElementById("player_btn").className = "pause";
		document.getElementById(`${name}`+'_btn').className = "pause_btn";
		document.getElementById(`${name}`+'_btn').setAttribute('onclick', loads);
		// pButtonMobile.className = "pause";
	} else {
		audio.pause();
		document.getElementById("player_btn").className = "play";
		document.getElementById(`${name}`+'_btn').className = "play_btn";
		document.getElementById(`${name}`+'_btn').setAttribute('onclick', 'play()');
	}
}
