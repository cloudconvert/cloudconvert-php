/**
 * Gruntfile.js
 */
module.exports = function (grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        php: {
            dist: {
                options: {
                    port: 8080,
                    base: 'web',
                    open: true,
                    keepalive: true
                }
            }
        },
        phpcs: {
            application: {
                dir: ['src/']
            },
            options: {
                bin: 'vendor/bin/phpcs',
                standard: 'PSR2'
            }
        },
        phplint: {
            options: {
                swapPath: '/tmp'
            },
            all: ['src/*.php']
        },
        phpdocumentor: {
            dist: {
                options: {
                    directory: './src/',
                    bin: 'vendor/bin/phpdoc.php',
                    target: 'docs/'
                }
            }
        },
        clean: {
            phpdocumentor: 'docs/'
        },
        phpunit: {
            unit: {
                testsuite: 'tests'
            },
            integration: {
                testsuite: 'integration'
            },
            options: {
                bin: 'vendor/bin/phpunit',
                configuration: 'phpunit.xml',
                colors: true,
                testdox: true
            }
        },
        watch: {
            scripts: {
                files: ['src/*.php', 'src/**/*.php', 'tests/*.php', 'tests/**/*.php'],
                tasks: ['precommit']
            }
        },
        exec: {
            phar: {
                command: 'vendor/bin/phar-composer build cloudconvert/cloudconvert-php'
            }
        }

    });

    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-php');
    grunt.loadNpmTasks('grunt-phplint');
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-phpdocumentor');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-exec');
    grunt.registerTask('phpdocs', ['clean:phpdocumentor', 'phpdocumentor']);
    grunt.registerTask('precommit', ['phplint:all', 'phpcs', 'phpunit:unit']);
    grunt.registerTask('default', ['phplint:all', 'phpcs', 'phpunit:unit', 'phpdocs']);
    grunt.registerTask('server', ['php']);
    grunt.registerTask('phar', ['exec:phar']);
};