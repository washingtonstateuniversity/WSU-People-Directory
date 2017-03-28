var Promise = require( "es6-promise" ).polyfill();

module.exports = function( grunt ) {
    grunt.initConfig( {
        pkg: grunt.file.readJSON( "package.json" ),

        stylelint: {
            src: [ "src/css/*.css" ]
        },

        postcss: {
            options: {
                processors: [
                    require( "autoprefixer" )( {
                        browsers: [ "> 1%", "ie 8-11", "Firefox ESR" ]
                    } )
                ]
            },
            admin_page: {
                src: "src/css/admin-page.css",
                dest: "css/admin-page.css"
            },
            admin_people: {
                src: "src/css/admin-people.css",
                dest: "css/admin-people.css"
            },
            admin_person: {
                src: "src/css/admin-person.css",
                dest: "css/admin-person.css"
            },
            frontend_people: {
                src: "src/css/people.css",
                dest: "css/people.css"
            },
            frontend_person: {
                src: "src/css/person.css",
                dest: "css/person.css"
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
                src: [ "Gruntfile.js", "src/js/*.js" ],
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
                src: [ "src/js/*.js" ],
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
            admin_page: {
                src: "src/js/admin-page.js",
                dest: "js/admin-page.min.js"
            },
            admin_people: {
                src: "src/js/admin-people.js",
                dest: "js/admin-people.min.js"
            },
            admin_people_sync: {
                src: "src/js/admin-people-sync.js",
                dest: "js/admin-people-sync.min.js"
            },
            admin_person: {
                src: "src/js/admin-person.js",
                dest: "js/admin-person.min.js"
            },
            frontend_people: {
                src: "src/js/people.js",
                dest: "js/people.min.js"
            }
        }
    } );

    grunt.loadNpmTasks( "grunt-contrib-jshint" );
    grunt.loadNpmTasks( "grunt-contrib-uglify" );
    grunt.loadNpmTasks( "grunt-jscs" );
    grunt.loadNpmTasks( "grunt-phpcs" );
    grunt.loadNpmTasks( "grunt-postcss" );
    grunt.loadNpmTasks( "grunt-stylelint" );

    // Default task(s).
    grunt.registerTask( "default", [ "postcss", "stylelint", "phpcs", "jscs", "jshint", "uglify" ] );
};
