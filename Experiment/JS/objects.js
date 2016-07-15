//practice with making objects

//EXAMPLE ONE: DOG
//a constructor function allows us to make many different Dog objects by passing in different arguments to the parameters
function Dog(name, breed, color, sound) {
    this.name = name;
    this.breed = breed;
    this.color = color;
    this.sound = sound;
    this.bark = function() {
        alert(this.name + " says, " + this.sound);
    }
}
//the new keyword creats a new instance of the Dog object 
var clifford = new Dog("clifford", "husky", "red", "woof!");
var lassie = new Dog("lassie", "lab", "yellow", "ruff!");
var snoopy = new Dog("snoopy", "snoopDog", "black and white", "wuf!");

var myFriends = {
    clifford,
    lassie,
    snoopy
};

//iterate through myFriends and have each give their sound
alert("When I go to the park, ");
for (pup in myFriends) {
    pup.bark();
}

//EXAMPLE TWO: MOVIES
//instead of creating a generic constructor, we can just hard-code an object wherever we want, slightly different sytax
var movie1 = {
    //notice the commas separating the attributes
    title: "Plan 9 from Outer Space",
    genre: "Cult Classic",
    rating: 5,
    showtimes: ["3:00pm", "7:00pm", "11:00pm"],
    getNextShowing: function(movie) {
            var now = new Date().getTime();
            for (var i = 0; i < this.showtimes.length; i++) { //still need to use keyword this to specify that you want THIS movie's showtime.
                var showtime = getTimeFromString(movie.showtimes[i]);
                if ((showtime - now) > 0) {
                    return "Next showing of " + this.title + " is " + this.showtimes[i];
                }
                return null;
            }
        } //don't need to end this with a semi-colon because not a constructor...it's a variable
}

//alternative version with constructor:

function Movie(title, showtimes, rating, genre) {
    this.title = title; //note: attribute names can be anything but by convention name same thing as parameters for readability
    this.showtimes = showtimes;
    this.rating = rating;
    this.genre = genre;
    this.getNextShowing = function() {
        var now = new Date.getTime(); //this is a built in API and function name(params)
        for (var i = 0; i < this.showtimes.length; i++) {
            var showtime = getTimeFromString(this.showtimes[i]);
            if (showtime - now > 0) {
                return "Next Showtime of " + this.title + " is " + this.showtime[i];
            }
        }
    }; //need to end this method with a semi-colon since this is a function
}

//test the constructor

var banzaiMovie = new Movie("Buckaroo Banzai", "Cult Classic",
    5, ["1:00pm", "5:00pm", "7:00pm", "11:00pm"]);
var plan9Movie = new Movie("Plan 9 from Outer Space", "Cult Classic",
    2, ["3:00pm", "7:00pm", "11:00pm"]);
var forbiddenPlanetMovie = new Movie("Forbidden Planet", "Classic Sci-fi",
    5, ["5:00pm", "9:00pm"]);
    
alert(banzaiMovie.getNextShowing());
alert(plan9Movie.getNextShowing());
alert(forbiddenPlanetMovie.getNextShowing());