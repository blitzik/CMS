module.exports = function (grunt) {

    require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        concat: {
            front: {
                options: {
                    separator: ';'
                },

                src: [
                    'bower_components/jquery/dist/jquery.js',
                    'bower_components/nette-forms/src/assets/netteForms.js',
                    'bower_components/nette.ajax.js/nette.ajax.js',
                    'bower_components/bootstrap-sass/assets/javascripts/bootstrap.js',
                    'assets/js/main.js'
                ],
                dest: 'assets/js/concatenated/js.js'
            },
            shivAndRespond: {
                options: {
                    separator: ';'
                },

                src: [
                    'bower_components/html5shiv/dist/html5shiv.js',
                    'bower_components/respond/src/respond.js'
                ],
                dest: 'assets/js/concatenated/shivAndRespond.js'
            }
        },

        uglify: {
            build: {
                files: {
                    'assets/js/js.min.js': 'assets/js/concatenated/js.js'
                }
            },
            shivAndRespond: {
                files: {
                    'assets/js/shivAndRespond.min.js': 'assets/js/concatenated/shivAndRespond.js'
                }
            }
        },

        cssmin: {
            front: {
                files: {
                    'assets/css/front.min.css': 'assets/css/original/front.css'
                }
            },
            admin: {
                files: {
                    'assets/css/admin.min.css': 'assets/css/original/admin.css'
                }
            }
        },

        sass: {
            front: {
                files: {
                    'assets/css/original/front.css': [
                        'assets/css/SCSS/front.scss'
                    ]
                }
            },
            admin: {
                files: {
                    'assets/css/original/admin.css': 'assets/css/SCSS/admin.scss'
                }
            }
        },

        watch: {
            front: {
                files: [
                    'assets/css/SCSS/_grid.scss',
                    'assets/css/SCSS/_paginator.scss',
                    'assets/css/SCSS/_blog_front.scss',
                    'assets/css/SCSS/front.scss'
                ],
                tasks: ['sass:front', 'cssmin:front']
            }
        },

        copy: {
            front: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: ['libs/visual_paginator/_paginator.scss'],
                        dest: 'assets/css/SCSS/'
                    }
                ]
            },

            font_awesome: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: ['bower_components/font-awesome-sass/assets/fonts/font-awesome/*'],
                        dest: 'assets/fonts/font-awesome/'
                    }
                ]
            }
        }

    });

    grunt.registerTask('default', ['copy', 'sass', 'concat', 'cssmin', 'uglify']);
    grunt.registerTask('buildcss', ['sass', 'cssmin']);
    grunt.registerTask('buildjs', ['concat', 'uglify']);
    grunt.registerTask('watch_front_css', ['watch:front']);
};