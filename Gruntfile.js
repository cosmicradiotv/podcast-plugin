module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    bower: {
      install: {
        options: {
          targetDir: './assets/vendor',
          layout: 'byComponent',
          cleanTargetDir: true,
        }
      }
    },
    replace: {
      img_urls: {
        src: ['./assets/vendor/mediaelement/css/*.css'],
        overwrite: true,
        replacements: [{
          from: 'url(',
          to: 'url(../img/'
        }]
      }
    }
  });

  // Load the plugin that provides the "uglify" task.
  grunt.loadNpmTasks('grunt-bower-task');
  grunt.loadNpmTasks('grunt-text-replace');

  // Default task(s).
  grunt.registerTask('default', ['bower:install','replace:img_urls']);

};