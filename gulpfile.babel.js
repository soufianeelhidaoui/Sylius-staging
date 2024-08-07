import chug from 'gulp-chug';
import gulp from 'gulp';
import yargs from 'yargs';

const { argv } = yargs
  .options({
    rootPath: {
      description: '<path> path to web assets directory',
      type: 'string',
      requiresArg: true,
      required: false,
    },
    nodeModulesPath: {
      description: '<path> path to node_modules directory',
      type: 'string',
      requiresArg: true,
      required: false,
    },
  });

const config = [
  '--rootPath',
  argv.rootPath || '../../../../../../../public/assets',
  '--nodeModulesPath',
  argv.nodeModulesPath || '../../../../../../../node_modules',
];

export const buildAdmin = function buildAdmin() {
  return gulp.src('vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/gulpfile.babel.js', { read: false })
    .pipe(chug({ args: config, tasks: 'build' }));
};
buildAdmin.description = 'Build admin assets.';

export const watchAdmin = function watchAdmin() {
  return gulp.src('vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/gulpfile.babel.js', { read: false })
    .pipe(chug({ args: config, tasks: 'watch' }));
};
watchAdmin.description = 'Watch admin asset sources and rebuild on changes.';

export const build = gulp.parallel(buildAdmin);
build.description = 'Build assets.';

export const watch = gulp.parallel(watchAdmin);
watch.description = 'Watch asset sources and rebuild on changes.';

gulp.task('admin', buildAdmin);
gulp.task('admin-watch', watchAdmin);

export default build;
