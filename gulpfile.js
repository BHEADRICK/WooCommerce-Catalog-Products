var gulp = require('gulp');
var Path = require('path');

gulp.task('deploy', function () {

  var zip = require('gulp-zip');
  var findParentDir = require('find-parent-dir');

  var dir;
  try {

    dir = findParentDir.sync(__dirname, '.git');

    dir = dir.split('/').pop();

  } catch(err) {
    console.error('error', err);
  }
  return gulp.src([ '**' ,'!.*', '!composer.json', '!package.json', '!gulpfile.js', '!node_modules/**' ])
    .pipe(zip(dir+'.zip'))
    .pipe(gulp.dest('../'));
});

