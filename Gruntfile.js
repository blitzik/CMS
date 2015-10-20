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
                    'bower_components/bootstrap-sass/assets/javascripts/bootstrap.js',
                    'assets/js/main.js'
                ],
                dest: 'assets/js/js.js'
            },
            shivAndRespond: {
                src: [
                    'bower_components/html5shiv/dist/html5shiv.js',
                    'bower_components/respond/src/respond.js'
                ],
                dest: 'assets/js/shivAndRespond.js'
            }
        },

        uglify: {
            build: {
                files: {
                    'assets/js/js.min.js': 'assets/js/js.js'
                }
            },
            shivAndRespond: {
                files: {
                    'assets/js/shivAndRespond.min.js': 'assets/js/shivAndRespond.js'
                }
            }
        },

        cssmin: {
            build: {
                files: {
                    'assets/css/front.min.css': 'assets/css/original/front.css'
                }
            }
        },

        sass: {
            build: {
                files: {
                    'assets/css/original/front.css': 'assets/css/SCSS/front.scss'
                }
            }
        },

        watch: {
            sass: {
                files: ['assets/css/SCSS/*.scss'],
                tasks: ['sass', 'cssmin']
            }
        }

    });

    grunt.registerTask('default', []);
    grunt.registerTask('buildcss', ['sass', 'cssmin']);
    grunt.registerTask('buildjs', ['concat', 'uglify']);
    grunt.registerTask('watchcss', ['watch']);
};