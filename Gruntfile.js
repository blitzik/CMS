module.exports = function (grunt) {

    require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        concat: {
            mutual_js: {
                options: {
                    separator: ';'
                },

                src: [
                    'bower_components/jquery/dist/jquery.js',
                    'bower_components/nette-forms/src/assets/netteForms.js',
                    'bower_components/nette.ajax.js/nette.ajax.js',
                    'bower_components/nette.ajax.js/extensions/spinner.ajax.js',
                    //'bower_components/bootstrap-sass/assets/javascripts/bootstrap.js',
                    //'www/assets/js/my_js/image_management.js',
                    //'www/assets/js/my_js/webfont.js',
                    'www/assets/js/my_js/main.js'
                ],
                dest: 'www/assets/js/original/js.js'
            },

            edit_page: {
                options: {
                    separator: ';'
                },

                src: [
                    'www/assets/js/original/jquery.datetimepicker.js',
                    'www/assets/js/my_js/editPageDatetimepicker.js',
                    'www/assets/js/my_js/editPage.js',
                    'www/assets/js/my_js/tagsPicking.js',
                    'www/assets/js/original/autosize.js'
                ],
                dest: 'www/assets/js/original/editPage.js'
            },

            comments: {
                options: {
                    separator: ';'
                },

                src: [
                    'www/assets/js/original/autosize.js',
                    'www/assets/js/my_js/comments.js'
                ],
                dest: 'www/assets/js/original/comments.js'
            }
        },

        uglify: {
            mutual: {
                files: {
                    'www/assets/js/js.min.js': 'www/assets/js/original/js.js'
                }
            },

            edit_page: {
                files: {
                    'www/assets/js/editPage.min.js': 'www/assets/js/original/editPage.js'
                }
            },

            comments: {
                files: {
                    'www/assets/js/comments.js': 'www/assets/js/original/comments.js'
                }
            },

            tags_picking: {
                files: {
                    'www/assets/js/tagsPicking.min.js': 'www/assets/js/my_js/tagsPicking.js'
                }
            }
        },

        cssmin: {
            front: {
                files: {
                    'www/assets/css/front.min.css': 'www/assets/css/original/front.css'
                }
            },

            admin: {
                files: {
                    'www/assets/css/admin.min.css': 'www/assets/css/original/admin.css'
                }
            },

            print: {
                files: {
                    'www/assets/css/print.min.css': 'www/assets/css/original/print.css'
                }
            },

            datetime_picker: {
                files: {
                    'www/assets/css/jquery.datetimepicker.min.css': 'www/assets/css/original/jquery.datetimepicker.css'
                }
            }
        },

        sass: {
            front: {
                files: {
                    'www/assets/css/original/front.css': [
                        'www/assets/css/SCSS/front/front.scss'
                    ]
                }
            },

            admin: {
                files: {
                    'www/assets/css/original/admin.css': 'www/assets/css/SCSS/admin/admin.scss'
                }
            },

            print: {
                files: {
                    'www/assets/css/original/print.css': 'www/assets/css/SCSS/front/print.scss'
                }
            }
        },

        watch: {
            front: {
                files: [
                    'www/assets/css/SCSS/_grid.scss',
                    'www/assets/css/SCSS/_common.scss',
                    'www/assets/css/SCSS/_paginator.scss',
                    'www/assets/css/SCSS/front/_blog_front.scss',
                    'www/assets/css/SCSS/front/front.scss'
                ],
                tasks: ['sass:front', 'cssmin:front']
            },

            admin: {
                files: [
                    'www/assets/css/SCSS/_grid.scss',
                    'www/assets/css/SCSS/_common.scss',
                    'www/assets/css/SCSS/admin/_my-variables.scss',
                    'www/assets/css/SCSS/admin/_blog_admin.scss',
                    'www/assets/css/SCSS/admin/admin.scss'
                ],
                tasks: ['sass:admin', 'cssmin:admin']
            }
        },

        copy: {
            paginator: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: ['libs/visual_paginator/_paginator.scss'],
                        dest: 'www/assets/css/SCSS/'
                    }
                ]
            },

            font_awesome: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: ['bower_components/font-awesome-sass/assets/fonts/font-awesome/*'],
                        dest: 'www/assets/fonts/font-awesome/'
                    }
                ]
            },

            datetime_picker: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: ['bower_components/datetimepicker/jquery.datetimepicker.css'],
                        dest: 'www/assets/css/original/'
                    },
                    {
                        expand:true,
                        flatten: true,
                        src: ['bower_components/datetimepicker/jquery.datetimepicker.js'],
                        dest: 'www/assets/js/original/'
                    }
                ]
            },

            jquery_autosize: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: ['bower_components/autosize/dist/autosize.js'],
                        dest: 'www/assets/js/original/'
                    }
                ]
            }
        }

    });

    grunt.registerTask('default', ['copy', 'sass', 'concat', 'cssmin', 'uglify']);

    grunt.registerTask('build_front_css', ['sass:front', 'cssmin:front']);
    grunt.registerTask('build_admin_css', ['sass:admin', 'cssmin:admin']);

    grunt.registerTask('build_front_print', ['sass:print', 'cssmin:print']);

    grunt.registerTask('build_js', ['concat:mutual_js', 'uglify:mutual']);

    grunt.registerTask('build_comments_js', ['concat:comments', 'uglify:comments']);

    grunt.registerTask('build_admin_css_js', ['sass:admin', 'cssmin:admin', 'cssmin:datetime_picker', 'concat:mutual_js', 'concat:edit_page', 'uglify:mutual', 'uglify:edit_page']);
    grunt.registerTask('build_admin_page_js', ['concat:edit_page', 'uglify:edit_page']);

    grunt.registerTask('watch_front_css', ['watch:front']);
    grunt.registerTask('watch_admin_css', ['watch:admin']);
};