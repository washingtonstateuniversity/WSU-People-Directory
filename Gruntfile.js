module.exports = function( grunt ) {
    grunt.initConfig( {
        pkg: grunt.file.readJSON( "package.json" ),

        stylelint: {
            src: [ "css/src/*.css" ]
        },

        postcss: {
            options: {
                processors: [
                    require( "autoprefixer" )( {
                        browsers: [ "> 1%", "ie 8-11", "Firefox ESR" ]
                    } )
                ]
            },
            dist: {
                cwd: "css/src/",
                src: "*.css",
                dest: "css/",
                expand: true
            }
        },

        phpcs: {
            plugin: {
                src: [ "./*.php", "./includes/*.php", "./templates/*.php" ]
            },
            options: {
                bin: "vendor/bin/phpcs --extensions=php --ignore=\"*/vendor/*,*/node_modules/*\"",
                standard: "phpcs.ruleset.xml"
            }
        },

        jscs: {
            scripts: {
                src: [ "Gruntfile.js", "js/src/*.js" ],
                options: {
                    preset: "jquery",
                    requireCamelCaseOrUpperCaseIdentifiers: false, // We rely on name_name too much to change them all.
                    maximumLineLength: 250
                }
            }
        },

        jshint: {
            grunt_script: {
                src: [ "Gruntfile.js" ],
                options: {
                    curly: true,
                    eqeqeq: true,
                    noarg: true,
                    quotmark: "double",
                    undef: true,
                    unused: false,
                    node: true     // Define globals available when running in Node.
                }
            },
            people_scripts: {
                src: [ "js/src/*.js" ],
                options: {
                    bitwise: true,
                    curly: true,
                    eqeqeq: true,
                    forin: true,
                    freeze: true,
                    noarg: true,
                    nonbsp: true,
                    quotmark: "double",
                    undef: true,
                    unused: true,
                    browser: true, // Define globals exposed by modern browsers.
                    jquery: true   // Define globals exposed by jQuery.
                }
            }
        },

        uglify: {
            dist: {
                files: [ {
                    expand: true,
                    cwd: "js/src/",
                    src: "*.js",
                    dest: "js",
                    ext: ".min.js"
                } ]
            }
        },

        watch: {
            styles: {
                files: [ "css/src/*.css", "js/src/*.js" ],
                tasks: [ "default" ],
                option: {
                    livereload: 8000
                }
            }
        },

        connect: {
            server: {
                options: {
                    open: "http://localhost:8000/style-guide/directory-table.html",
                    port: 8000,
                    hostname: "localhost"
                }
            }
        }
    } );

    grunt.loadNpmTasks( "grunt-contrib-connect" );
    grunt.loadNpmTasks( "grunt-contrib-jshint" );
    grunt.loadNpmTasks( "grunt-contrib-uglify" );
    grunt.loadNpmTasks( "grunt-contrib-watch" );
    grunt.loadNpmTasks( "grunt-jscs" );
    grunt.loadNpmTasks( "grunt-phpcs" );
    grunt.loadNpmTasks( "grunt-postcss" );
    grunt.loadNpmTasks( "grunt-stylelint" );

    // Default task(s).
    grunt.registerTask( "default", [ "postcss", "stylelint", "jscs", "jshint", "uglify", "phpcs" ] );
    grunt.registerTask( "serve", [ "connect", "watch" ] );
};
