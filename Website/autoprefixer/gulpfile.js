var gulp = require('gulp'); /*include library */

/*include plugins */
var autoprefixer = require('gulp-autoprefixer');

gulp.task('styles', function() {
    gulp.src('autoprefix.css').pipe(autoprefixer()).pipe(gulp.dest('build'))
});

gulp.task('watch', function() {
    /*this says just rerun styles whenever you save this file */
    gulp.watch('autoprefix.css', ['styles']);

});