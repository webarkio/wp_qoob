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
                    '!build/**',
                    '!tmp/**'
                ],
                dest: 'tmp/<%= pkg.plugin_name %>/trunk/'
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

    //Run PHPUnit tests
    grunt.registerTask('phpunit', ['clean:phpunit','shell:phpunit']);
};