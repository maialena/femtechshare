// we set the onClick handler in the init function, which is called after the page has loaded...
//this means that onload, the init function is called so that the page is waiting for a click. '.onclick' the handleButtonClick method
//is called...springs into action.
function init() {
    var button = document.getElementById("addButton");
    button.onclick = handleButtonClick;
    loadPlaylist(); //new line of code from playlist_store.js - remove to see earlier version


}

function handleButtonClick() {
    var textInput = document.getElementById("songTextInput");
    var songName = textInput.value; // in html attribute was 'placeholder' but here it's 'value'
    //do nothing if the user hasn't typed anything into the form 
    if (songName == "") {
        alert("Please enter a song");
    } else {
        alert("Adding " + songName);
        // now we want to add songs to our playlist
        // the songs will go in the <ul> as new <li> (list items)
        var li1 = document.createElement("li");
        li1.innerHTML = songName; //fill in the value of li1
        var ul = document.getElementById("playlist");
        ul.appendChild(li1); // adding newly created list item as a child to ul, telling the object where to go.
        save(songName); // new line of code from playlist_store.js -- remove to see earlier version
    }




}

window.onload = init;