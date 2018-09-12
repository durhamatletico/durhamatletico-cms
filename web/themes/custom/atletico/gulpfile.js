/**
 * @file
 * Tasks to process custom theme SCSS.
 */

/* eslint no-console: ["error", { allow: ["warn", "error"] }] */

// Include node packages.
const autoprefixer = require('autoprefixer');
const beeper = require('beeper');
const cleancss = require('gulp-clean-css');
const colors = require('ansi-colors');
const gulp  = require('gulp');
const notify = require('gulp-notify');
const postcss = require('gulp-postcss');
const plumber = require('gulp-plumber');
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');

// Define other variables.
const outputPath = 'stylesheets';
const scssRoot = 'sass/styles.scss';
const scssWild = 'sass/**/*.scss';

/**
 * Error notifications.
 *
 * See https://github.com/mikaelbr/gulp-notify/issues/81#issuecomment-100422179.
 */
const reportError = function (error) {
  const lineNumber = (error.lineNumber) ? 'LINE ' + error.lineNumber + ' -- ' : '';

  notify({
    title: 'Task Failed [' + error.plugin + ']',
    message: lineNumber + 'See console.',
    sound: 'Sosumi'
  }).write(error);

  // Audio alert.
  beeper();

  // Pretty error reporting.
  let report = '';
  const chalk = colors.white.bgRed;

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
