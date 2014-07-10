var gulp = require('gulp'),
    sass = require('gulp-ruby-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss = require('gulp-minify-css'),
    jshint = require('gulp-jshint'),
    uglify = require('gulp-uglify'),
    imagemin = require('gulp-imagemin'),
    rename = require('gulp-rename'),
    clean = require('gulp-clean'),
    concat = require('gulp-concat'),
    notify = require('gulp-notify'),
    cache = require('gulp-cache'),
    livereload = require('gulp-livereload'),
    coffee = require('gulp-coffee');

var paths = {
    scripts:
        [
            'app/assets/js/angular-file/**/*.js',
            'app/assets/js/app.js',
            'app/assets/js/directives.js',
            'app/assets/js/factorys.js',
            'app/assets/js/twitter-text-1.9.1.js',
            'app/assets/js/lib/**/*.js',
            'app/assets/js/main.js',
            'app/assets/js/controllers/**/*.js'
        ],
    css: ['app/assets/css/**/*.css', 'app/provider/assets/css/**/*.scss']
};

// Clean
gulp.task('clean', function() {
    return gulp.src(['public/build/css', 'public/build/css'], {read: false})
        .pipe(clean());
});

gulp.task('scripts', function() {
    return gulp.src(paths.scripts)
        //.pipe(jshint('.jshintrc'))
        //.pipe(jshint.reporter('default'))
        .pipe(concat('main.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('public/build/js'))
        //.pipe(notify({ message: 'Scripts task complete' }))
        ;
});

// Styles
gulp.task('styles', function() {
    return gulp.src(paths.css)
        .pipe(concat('all.min.css'))
        .pipe(minifycss())
        .pipe(gulp.dest('public/build/css'))
});


// Default task
gulp.task('default', ['clean'], function() {
    gulp.start('styles', 'scripts');
});

// Watch
gulp.task('watch', function() {

    // Watch .scss files
    gulp.watch(paths.scripts, ['styles']);

    // Watch .js files
    gulp.watch(paths.scripts, ['scripts']);

    // Create LiveReload server
    var server = livereload();

    // Watch any files in dist/, reload on change
    gulp.watch(['public/build/**']).on('change', function(file) {
        server.changed(file.path);
    });
});