/**
 * @file
 * Tasks to process custom theme SCSS.
 */

/* eslint no-console: ["error", { allow: ["warn", "error"] }] */

// Include node packages.
var autoprefixer = require('autoprefixer');
var beeper = require('beeper');
var cleancss = require('gulp-clean-css');
var colors = require('ansi-colors');
var gulp  = require('gulp');
var notify = require('gulp-notify');
var postcss = require('gulp-postcss');
var plumber = require('gulp-plumber');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');

// Define other variables.
var outputPath = 'stylesheets';
var scssRoot = 'sass/styles.scss';
var scssWild = 'sass/**/*.scss';

/**
 * Error notifications.
 *
 * See https://github.com/mikaelbr/gulp-notify/issues/81#issuecomment-100422179.
 */
var reportError = function (error) {
  var lineNumber = (error.lineNumber) ? 'LINE ' + error.lineNumber + ' -- ' : '';

  notify({
    title: 'Task Failed [' + error.plugin + ']',
    message: lineNumber + 'See console.',
    sound: 'Sosumi'
  }).write(error);

  // Audio alert.
  beeper();

  // Pretty error reporting.
  var report = '';
  var chalk = colors.white.bgRed;

  report += chalk('TASK:') + ' [' + error.plugin + ']\n';
  report += chalk('PROB:') + ' ' + error.message + '\n';
  if (error.lineNumber) {
    report += chalk('LINE:') + ' ' + error.lineNumber + '\n';
  }
  if (error.fileName) {
    report += chalk('FILE:') + ' ' + error.fileName + '\n';
  }
  console.error(report);

  // Prevent the 'watch' task from stopping.
  this.emit('end');
};

/**
 * Task: scss.
 *
 * Compile, minify, and relocate CSS.
 */
gulp.task('sass', function () {
  return gulp.src(scssRoot)
    .pipe(sourcemaps.init())
    .pipe(plumber({
      errorHandler: reportError
    }))

    // Convert SCSS into CSS.
    .pipe(sass({
      outputStyle: 'nested',
      precision: 10
    }))

    // Apply vendor prefixes.
    .pipe(postcss([ autoprefixer({ browsers: ['last 2 versions'] }) ]))

    // Minify CSS.
    .pipe(cleancss())

    // Show errors.
    .on('error', reportError)

    // Write sourcemaps.
    .pipe(sourcemaps.write())

    // Save CSS.
    .pipe(gulp.dest(outputPath));
});

/**
 * Task: watch.
 *
 * Re-compile CSS on SCSS changes.
 */
gulp.task('watch', ['sass'], function () {
  gulp.watch(scssWild, ['sass']);
});

/**
 * Task: default.
 *
 * Process SCSS.
 */
gulp.task('default', ['sass']);
