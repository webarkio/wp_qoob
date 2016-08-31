'use strict';
module.exports = function(grunt) {
    // load all tasks
    require('load-grunt-tasks')(grunt, {
        scope: 'devDependencies'
    });

    // Project configuration
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        clean: {
            build: ['build/*'],
            tmp: ['tmp/'],
            docs: ['docs/dest/*', 'docs/dest/**'],
            phpunit: ["tests/phpunit/log/**"],
        },
        assemble: {
            options: {
                layout: "default.hbs",
                layoutdir: 'docs/src/layouts',
                data: 'docs/src/data/*.json',
                flatten: true
            },
            pages: {
                files: {
                    'docs/dest/': ['docs/src/*.hbs']
                }
            }
        },
        compress: {
            stable: {
                options: {
                    archive: 'build/wp_qoob_<%= pkg.version %>.zip'
                },
                expand: true,
                src: [
                    '**/*',
                    '!tests/**',
                    '!**/tests/**',
                    '!node_modules/**',
                    '!presentation/**',
                    '!.git/**',
                    '!build/**',
                    '!docs/dest/**',
                    '!docs/**',
                    '!Gruntfile.js',
                    '!package.json',
                    '!assets/screenshots/**',
                    '!**/package.json',
                    '!jsdoc.json'
                ],
                dest: 'wp_qoob/'
            }
        },
        shell: {
            gitpull: {
                command: 'git pull'
            },
            phpunit: {
                command: 'php tests/phpunit/phpunit.phar --configuration tests/phpunit/phpunit.xml'
            },
            api: {
                command: 'node node_modules/jsdoc/jsdoc.js -c jsdoc.json -d docs/dest/api -t docs/jsdoc/template/jaguar'
            }
        },
        concat: {
            options: {
                separator: ';\n'
            },
            dist: {
                src: ['assets/js/qoob-wordpress-driver.js', 'qoob/js/libs/bootstrap.min.js', 'qoob/js/libs/bootstrap-progressbar.js',
                    'qoob/js/libs/bootstrap-select.min.js', 'qoob/js/libs/handlebars.js', 'qoob/js/libs/handlebars-helper.js',
                    'qoob/js/libs/jquery-ui-droppable-iframe.js', 'qoob/js/libs/jquery.wheelcolorpicker.js', 'qoob/js/models/**.js', 'qoob/js/views/**.js',
                    'qoob/js/views/fields/**.js', 'qoob/js/extensions/**.js', 'qoob/js/controllers/qoob-controller.js', 'qoob/js/**.js', 'assets/js/control-edit-page.js'
                ],
                dest: 'qoob/qoob.min.js'
            }
        },
        mkdir: {
            build: {
                options: {
                    create: ['tmp']
                }
            }
        },
        svn_checkout: {
            make_local: {
                repos: [{
                    path: ['tmp'],
                    repo: 'https://plugins.svn.wordpress.org/qoob/'
                }]
            }
        },
        copy: {
            svn_assets: {
                options: {
                    mode: true
                },
                expand: true,
                cwd: 'assets/screenshots/',
                src: '**',
                dest: 'tmp/<%= pkg.plugin_name %>/assets/',
                flatten: true,
                filter: 'isFile'
            },
            svn_trunk: {
                options: {
                    mode: true
                },
                //setup file list for copying/ not copying for SVN
                src: [
                    '**',
                    '!assets/screenshots/**', // will be copied in copy:svn_assets below
                    '!node_modules/**',
                    '!.git/**',
                    '!Gruntfile.js',
                    '!package.json',
                    '!.gitignore',
                    '!.gitmodules',
                    '!tests/**',
                    '!**/test/**',
                    '!build/**',
                    '!tmp/**'
                ],
                dest: 'tmp/<%= pkg.plugin_name %>/trunk/'
            },
            style: {
                files: [
                    {
                        expand: true,
                        cwd: 'docs/src/css/',
                        src: ['**'],
                        dest: 'docs/dest/css/'
                    }
                ],
            },
            fonts: {
                files: [{
                        expand: true,
                        cwd: 'docs/src/fonts/',
                        src: ['**'],
                        dest: 'docs/dest/fonts/'
                    }]
            },
            js: {
                files: [
                    {
                        expand: true,
                        cwd: 'docs/src/js/',
                        src: ['**'],
                        dest: 'docs/dest/js/'
                    }
                ]
            },
            img: {
                files: [
                    {
                        expand: true,
                        cwd: 'docs/src/img/',
                        src: ['**'],
                        dest: 'docs/dest/img/'
                    }
                ],
            }
        },
        push_svn: {
            options: {
                remove: true
            },
            main: {
                src: 'tmp/<%= pkg.plugin_name %>',
                dest: 'https://plugins.svn.wordpress.org/qoob/',
                tmp: 'build/make_svn'
            }
        }
    });
    // Load concating js plugin
    grunt.loadNpmTasks('grunt-contrib-concat');
    //Load the shell plugin
    grunt.loadNpmTasks('grunt-shell');
    //Clean plugin
    grunt.loadNpmTasks('grunt-contrib-clean');
    // checkout svn
    grunt.loadNpmTasks('grunt-svn-checkout');
    // copy files
    grunt.loadNpmTasks('grunt-contrib-copy');
    // push svn
    grunt.loadNpmTasks('grunt-push-svn');
    // load assemble
    grunt.loadNpmTasks('grunt-assemble');

    //Build builder to theme tgm plugin
    grunt.registerTask('build', ['clean:build', 'shell:gitpull', 'concat', 'compress:stable']);

    // Deploy to trunk
    grunt.registerTask('deploy', ['build', 'mkdir', 'svn_checkout', 'copy:svn_assets', 'copy:svn_trunk', 'push_svn', 'clean:tmp']);

    //Deploy docs
    grunt.registerTask('docs', ['clean:docs', 'assemble', 'copy:style', 'copy:fonts', 'copy:js', 'copy:img', 'api']);

    //Create only JS API docs
    grunt.registerTask('api', ['shell:api']);

    //Run PHPUnit tests
    grunt.registerTask('phpunit', ['clean:phpunit','shell:phpunit']);
};