/**
 * Import required node modules
 */
const autoprefixer         = require('autoprefixer');
const browserSync         = require('browser-sync').create();
const Fiber               = require('fibers');
const gulp                = require('gulp');
const cleanCSS            = require('gulp-clean-css');
const eslint              = require('gulp-eslint');
const postcss             = require('gulp-postcss');
const sass                = require('gulp-sass');
const sourcemaps          = require('gulp-sourcemaps');
const stylelint           = require('gulp-stylelint');

/**
 * Sass settings
 *
 * Set Sass compiler. There are two options:
 * - require('sass') for Dart Sass
 * - require('node-sass') for Node Sass (LibSass)
 */
sass.compiler = require('sass');

/**
 * Gulp config
 */
const config = {
  paths: {
    styles: {
      src: './sass/**/*.scss',
      dest: './css/',
    },
    scripts: './js/**/*.js',
  },
  browserSync: {
    proxy: 'http://abtestui.test',
    autoOpen: false,
    browsers: [
      'Google Chrome',
    ],
  }
};

/**
 * SASS:Development Task
 *
 * Sass task for development with live injecting into all browsers
 * @param {function} done For callback function for Async completion.
 */
function sassDevTask(done) {
  gulp
    .src(config.paths.styles.src)
    .pipe(sourcemaps.init({ largeFile: true }))
    .pipe(sass({
      fiber: Fiber,
      outputStyle: 'expanded',
      precision: 10,
      includePaths: ['node_modules'],
    }))
    .on('error', sass.logError)
    .pipe(postcss([
      autoprefixer(),
    ]))
    .pipe(sourcemaps.write({ includeContent: false }))
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(config.paths.styles.dest))
    .pipe(browserSync.stream());
  done();
}

/**
 * SASS:Production Task
 *
 * Sass task for production with linting, to be stored in Git (run before
 * commit)
 * @param {function} done For callback function for Async completion.
 */
function sassProdTask(done) {
  gulp
    .src(config.paths.styles.src)
    .pipe(stylelint({
      fix: true,
      reporters: [
        {
          formatter: 'verbose',
          console: true,
        },
      ],
    }))
    .pipe(sass({
      fiber: Fiber,
      precision: 10,
      includePaths: ['node_modules'],
    }))
    .on('error', sass.logError)
    .pipe(postcss([
      autoprefixer(),
    ]))
    .pipe(cleanCSS({
      compatibility: {
        colors: {
          opacity: true,
        },
        units: {
          rem: true,
          vh: true,
          vm: true,
          vmax: true,
          vmin: true,
        },
      },
      level: 1,
    }))
    .pipe(gulp.dest(config.paths.styles.dest));
  done();
}

/**
 * SASS:Linting Task
 *
 * @param {function} done For callback function for Async completion.
 */
function sassLintTask(done) {
  gulp
    .src(config.paths.sass)
    .pipe(stylelint({
      fix: true,
      reporters: [
        {
          formatter: 'verbose',
          console: true,
        },
      ],
    }));
  done();
}

/**
 * JavaScript Task
 *
 * Currently there is only one JavaScript task (no separated for dev and prod).
 * others) and minified JavaScript files.
 * @param {function} done For callback function for Async completion.
 */
function scriptsTask(done) {
  gulp
    .src(config.paths.scripts)
    .pipe(eslint({ fix: true }))
    .pipe(eslint.format())
    .pipe(browserSync.stream());
  done();
}

/**
 * BrowserSync Task
 *
 * Watching Sass and JavaScript source files for changes.
 * @prop {string} proxy Change it for your local setup.
 * @param {function} done Changed event.
 */
function browserSyncTask(done) {
  browserSync.init({
    proxy: config.browserSync.proxy,
    open: config.browserSync.autoOpen,
    browser: config.browserSync.browsers,
  });
  gulp.watch(config.paths.styles.src, sassDevTask);
  gulp.watch(config.paths.scripts, scriptsTask);
  done();
}

/**
 * BrowserSync Reload Task
 *
 * BrowserSync will reload all synced browsers.
 * @param {function} done Reload event.
 */
// eslint-disable-next-line no-unused-vars
function browserSyncReloadTask(done) {
  browserSync.reload();
  done();
}

// export tasks
/* eslint no-multi-spaces: "off" */
exports.default           = gulp.series(sassDevTask, browserSyncTask);
exports.prod              = gulp.parallel(sassProdTask, scriptsTask);
exports.lint              = gulp.parallel(sassLintTask, scriptsTask);
exports.sassDev           = sassDevTask;
exports.sassProd          = sassProdTask;
exports.scripts           = scriptsTask;
exports.watch             = browserSyncTask;
