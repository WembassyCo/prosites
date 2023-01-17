var gulp  = require('gulp'),
  sass = require('gulp-sass'),
  sourcemaps = require('gulp-sourcemaps'),
  cleanCss = require('gulp-clean-css'),
  rename = require('gulp-rename'),
  postcss      = require('gulp-postcss'),
  autoprefixer = require('autoprefixer');
  sassGlob = require('gulp-sass-glob');

gulp.task('build-theme', function() {
  return gulp.src(['assets/sass/**/*.scss', 'assets/sass/**/*.sass'])
    .pipe(sourcemaps.init())
    .pipe(sassGlob())
    .pipe(sass({
      // includePaths: ['breakpoint-sass/stylesheets'],
      includePaths: require('node-normalize-scss').includePaths
    }).on('error', sass.logError))
    .pipe(postcss([ autoprefixer({ browsers: [
      'Chrome >= 35',
      'Firefox >= 38',
      'Edge >= 12',
      'Explorer >= 10',
      'iOS >= 8',
      'Safari >= 8',
      'Android 2.3',
      'Android >= 4',
      'Opera >= 12']})]))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest('assets/css/'))
    .pipe(cleanCss())
    .pipe(rename({suffix: '.min'}))
    .pipe(gulp.dest('assets/css/'))
});

gulp.task('watch', ['build-theme'], function() {
  gulp.watch(['assets/sass/**/*.scss', 'assets/sass/**/*.sass'], ['build-theme']);
});

gulp.task('default', ['build-theme'], function() {
});
