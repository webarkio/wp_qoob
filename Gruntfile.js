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
            build: ['build/**'],
            tmp: ['tmp/**'],
            phpunit: ["tests/phpunit/log/**"],
        },
        compress: {
            stable: {
                options: {
                    archive: 'build/qoob.<%= pkg.version %>.zip'
                },
                expand: true,
                src: [
                    '**/*',
                    '!tests/**',
                    '!**/tests/**',
                    '!node_modules/**',
                    '!**/node_modules/**',
                    '!presentation/**',
                    '!.git/**',
                    '!**/.git/**',
                    '!build/**',
                    '!docs/dest/**',
                    '!**/docs/dest/**',
                    '!docs/**',
                    '!**/docs/**',
                    '!Gruntfile.js',
                    '!**/Gruntfile.js',
                    '!package.json',
                    '!**/package.json',
                    '!assets/screenshots/**',
                    '!jsdoc.json',
                    '!**/jsdoc.json',
                    '!test/**',
                    '!**/test/**',
                    '!.gitignore',
                    '!**/.gitignore'
                ],
                dest: 'qoob/'
            }
        },
        shell: {
            gitpull: {
                command: 'git pull origin master'
            },
            phpunit: {
                command: 'php tests/phpunit/phpunit.phar --configuration tests/phpunit/phpunit.xml'
            },
            qoob_build: {
                command: [
                    'cd qoob',
                    'grunt build',
                    'cd ..'
                ].join('&&')
            }
        },
        mkdir: {
            build: {
                options: {
                    create: ['tmp']
                }
            },
			tags: {
				options: {
					create: ['tmp/qoob/tags/<%= pkg.version %>']
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
					'!**/node_modules/**',
                    '!.git/**',
                    '!**/.git/**',
                    '!Gruntfile.js',
                    '!**/Gruntfile.js',
                    '!package.json',
                    '!**/package.json',
                    '!.gitignore',
                    '!**/.gitignore',
                    '!.gitmodules',
                    '!**/.gitmodules',
                    '!tests/**',
                    '!**/tests/**',
                    '!test/**',
                    '!**/test/**',
                    '!build/**',
                    '!tmp/**'
                ],
                dest: 'tmp/<%= pkg.plugin_name %>/trunk/'
            },
			tags: {
				expand: true,
				cwd: 'tmp/<%= pkg.plugin_name %>/trunk/',
                src: ['**'],
                dest: 'tmp/<%= pkg.plugin_name %>/tags/<%= pkg.version %>/'
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

    grunt.loadNpmTasks('grunt-shell');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-svn-checkout');
    grunt.loadNpmTasks('grunt-push-svn');

    //Build builder to theme tgm plugin
    grunt.registerTask('build', ['clean:build', 'shell:gitpull', 'shell:qoob_build', 'compress:stable']);

    // Deploy to trunk
    grunt.registerTask('deploy', ['shell:gitpull', 'shell:qoob_build', 'mkdir:build', 'svn_checkout', 'mkdir:tags', 'copy:svn_assets', 'copy:svn_trunk', 'copy:tags', 'push_svn', 'clean:tmp', 'clean:build']);

    //Run PHPUnit tests
    grunt.registerTask('phpunit', ['clean:phpunit','shell:phpunit']);
};