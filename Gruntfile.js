'use strict';
module.exports = function (grunt) {

    // load all tasks
    require('load-grunt-tasks')(grunt, {scope: 'devDependencies'});

    // Project configuration
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        clean: {
            build: ['build/builder/*', 'build/builder/**', 'build/**']
        },
        compress: {
            dev: {
                options: {
                    archive: 'build/qoob.wordpress-dev.zip'
                },
                expand: true,
                cwd: '', 
                src: ['**/*', '!node_modules/**'], 
                dest: 'qoob.wordpress/'
            },
            stable: {
                options: {
                    archive: 'build/qoob.wordpress.zip'
                },
                expand: true,
                cwd: '',
                src: [
                    '**/*',
                    '!test/**',
                    '!**/test/**',
                    '!node_modules/**',
                    '!presentation/**',
                    '!.git/**',
                    '!build/**',
                    '!docs/dest/**',
                    '!docs/**',
                    '!Gruntfile.js',
                    '!package.json',
                    '!**/package.json'
                ],
                dest: 'qoob.wordpress/'
            }
        },
        shell: {
            gitpull: {
                command: 'git pull'
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
                    'qoob/js/views/fields/**.js', 'qoob/js/extensions/**.js', 'qoob/js/controllers/qoob-controller.js', 'qoob/js/**.js', 'assets/js/control-edit-page.js'],
                dest: 'qoob/qoob.min.js'
            }
        }
    });
    // Load concating js plugin
    grunt.loadNpmTasks('grunt-contrib-concat');
    //Load the shell plugin
    grunt.loadNpmTasks('grunt-shell');
    //Clean plugin
    grunt.loadNpmTasks('grunt-contrib-clean');

    //Build builder to theme tgm plugin
    grunt.registerTask('build', ['clean:build', 'shell:gitpull', 'concat', 'compress:stable', 'compress:dev']);
};
