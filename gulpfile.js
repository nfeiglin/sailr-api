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
            'app/assets/js/lib/**/*.js',
            'app/assets/js/directives.js',
            'app/assets/js/factorys.js',
            'app/assets/js/twitter-text-1.9.1.js',
            'app/assets/js/main.js',
            'app/assets/js/controllers/**/*.js'
        ],
    css: ['app/assets/css/**/*.css', 'app/assets/css/**/*.scss'],
    img: ['app/assets/img/**/*.jpg', 'app/assets/img/**/*.jpeg', 'app/assets/img/**/*.png', 'app/assets/img/**/*.gif']
};

var loginCssPath = 'public/css/login.css';

// Clean
gulp.task('clean', function() {
    return gulp.src(['public/build/css', 'public/build/js', 'public/build/img'], {read: false})
        .pipe(clean());
});

gulp.task('scripts', function() {
    return gulp.src(paths.scripts)
        .pipe(uglify())
        .pipe(concat('main.min.js'))
        .pipe(gulp.dest('public/build/js'))
        ;
});

// Styles
gulp.task('styles', function() {
    return gulp.src(paths.css)
        .pipe(sass())
        .pipe(concat('all.min.css'))
        .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1'))
        .pipe(minifycss())
        .pipe(gulp.dest('public/build/css'));
});

// Login css
gulp.task('login-css', function() {
    return gulp.src(loginCssPath)
        .pipe(sass())
        .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1'))
        .pipe(minifycss())
        .pipe(concat('login.min.css'))
        .pipe(gulp.dest('public/build/css'));
});


// Images
gulp.task('images', function() {
    return gulp.src(paths.img)
        .pipe(imagemin(
            {
                optimizationLevel: 7,
                progressive: true
            }
        ))
        .pipe(gulp.dest('public/build/images'));
});



// Default task
gulp.task('default', ['clean'], function() {
    gulp.start('styles', 'scripts');
});

// Watch
gulp.task('watch', function() {

    // Watch .scss files
    gulp.watch(paths.css, ['styles']);
    gulp.watch(loginCssPath, ['login-css']);

    // Watch .js files
    gulp.watch(paths.scripts, ['scripts']);

    // Watch .js files
    gulp.watch(paths.img, ['images']);

    // Create LiveReload server
    var server = livereload();

    // Watch any files in dist/, reload on change
    gulp.watch(['public/build/**']).on('change', function(file) {
        server.changed(file.path);
    });
});