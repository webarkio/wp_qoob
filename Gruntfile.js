'use strict';
module.exports = function(grunt) {

    // load all tasks
    require('load-grunt-tasks')(grunt, {scope: 'devDependencies'});

    // Project configuration
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        clean: {
            phpapi: ["docs/dest/phpapi"],
            jsapi: ["docs/dest/jsapi"],
            phpunit: ["docs/dest/tests"],
            htmldocs: ['docs/dest/*', 'docs/dest/**'],
            docs: ['docs/dest/*', 'docs/dest/**'],
            build: ['build/*', 'build/**']
        },
        compress: {
            dev: {
                options: {
                    archive: 'build/qoob-dev-<%= pkg.version %>.zip'
                },
                files: [
                    {expand: true, cwd: 'docs/dest/', src: ['**'], dest: 'documentation/'}, // includes files in path
                    {src: ['Licenses/**']},
                    {expand: true, cwd: '', src: ['**/*', '!build/**', '!node_modules/**', '!presentation/**', '!docs/dest/**'], dest: 'qoob/'} // includes files in path and its subdirs
                ]
            },
            stable: {
                options: {
                    archive: 'build/qoob-<%= pkg.version %>.zip'
                },
                expand: true,
                cwd: '',
                src: ['**/*', '!**/tests/**', '!node_modules/**', '!presentation/**', '!.git/**', '!build/**', '!docs/dest/**', '!dev/**', '!docs/**', '!readme.md', '!Gruntfile.js', '!jsdoc.json', '!package.json', '!tests/**', '!phpdoc.xml', '!phpunit.xml'],
                dest: 'qoob-<%= pkg.version %>/'
            },
            docs: {
                options: {
                    archive: 'build/qoob-docs-<%= pkg.version %>.zip'
                },
                expand: true,
                cwd: '',
                src: ['docs/dest/**'],
                dest: 'qoob-docs-<%= pkg.version %>/'
            }
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
        copy: {
            fast: {
                files: [{
                        expand: true,
                        cwd: '',
                        src: ['**/*', '!Licenses/**', '!presentation/**', '!**/tests/**', '!node_modules/**', '!.git/**', '!build/**', '!docs/**', '!dev/**', '!readme.md', '!Gruntfile.js', '!jsdoc.json', '!package.json', '!tests/**', '!phpdoc.xml', '!phpunit.xml'],
                        dest: 'build/builder/'
                    }]
            },
            style: {
                files: [
                    {
                        expand: true,
                        cwd: 'docs/src/css/',
                        src: ['**'],
                        dest: 'docs/dest/css/'
                    }
                ]
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
                ]
            }
        },
        concat: {
            options: {
                separator: ';'
            },
            dist: {
                src: ['qoob/js/*.js', 'qoob/js/libs/*.js', 'qoob/js/fields/*.js'],
                dest: 'qoob/js/min/<%= pkg.name %>.js'
            }
        },
        uglify: {
            dist: {
                files: {
                    'qoob/js/min/<%= pkg.name %>.min.js': ['<%= concat.dist.dest %>']
                }
            }
        },
        shell: {
            phpapi: {
                command: 'php node_modules/phpdocumentator/bin/phpdoc'
            },
            jsapi: {
                command: 'node node_modules/jsdoc/jsdoc.js -c jsdoc.json -d docs/dest/jsapi -t dev/jsdoc/template/jaguar'
            },
            phpunit: {
                command: 'php dev/phpunit.phar'
            },
            gitpull: {
                command: 'git pull'
            }
        }
    });

    //Load the shell plugin
    grunt.loadNpmTasks('grunt-shell');
    //Clean plugin
    grunt.loadNpmTasks('grunt-contrib-clean');

    //Load assemble
    grunt.loadNpmTasks('assemble');

    //Default tasks
    grunt.registerTask('default', ['build']);
    //Build project
    grunt.registerTask('build', [/*'shell:gitpull', */'clean:docs', 'phpunit', 'docs', 'clean:build', 'concat', 'uglify', 'compress:stable', 'compress:dev']);
    //Create documentation files
    grunt.registerTask('docs', ['htmldocs', 'phpapi', 'jsapi']);
    //Create only PHP API docs
    grunt.registerTask('phpapi', ['shell:phpapi']);
    //Create only JS API docs
    grunt.registerTask('jsapi', ['shell:jsapi']);
    //Create main HTML docs from hbs
    grunt.registerTask('htmldocs', ['assemble', 'copy:style', 'copy:js', 'copy:img']);
    //Run PHPUnit tests
    grunt.registerTask('phpunit', ['clean:phpunit', 'shell:phpunit']);
    //Build builder to theme
    grunt.registerTask('fast_build', ['clean:build', 'shell:gitpull', 'copy:fast']);
};
