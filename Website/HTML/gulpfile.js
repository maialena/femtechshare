/*this includes all of gulp library */
var gulp = require('gulp');

/*need to include all the gulp plugins that we want */

var autoprefixer = require('gulp-autoprefixer');

/*now we make a task */
/*this will grab the content of your file, run it through our plugins, in this case auto-prefixer, then return another file with that */

gulp.task('styles', function() {
    /*want to pipe the original file to autoprefixer, and pipe that to a file called build */
    gulp.src('flexbox/autoprefix.css')
        .pipe(autoprefixer())
        .pipe(gulp.dest('output'))
});

/*now we should be able to go back to terminal and type gulp styles, which is the task we just built */