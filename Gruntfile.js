module.exports = function (grunt) {

    require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        concat: {
            options: {
                separator: ';'
            },
            front: {
                src: [
                    'bower_components/jquery/dist/jquery.js',
                    'bower_components/nette-forms/src/assets/netteForms.js',
                    'bower_components/nette.ajax.js/nette.ajax.js',
                    'assets/js/main.js'
                ],
                dest: 'assets/js/js.js'
            }
        },

        uglify: {
            build: {
                files: {
                    'assets/js/js.min.js': 'assets/js/js.js'
                }
            }
        },

        cssmin: {

        },

        sass: {

        }

    });

    grunt.registerTask('default', []);
    grunt.registerTask('buildjs', ['concat', 'uglify']);
};