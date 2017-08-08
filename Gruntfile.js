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
            admin_user_profile: {
                src: "src/css/admin-user-profile.css",
                dest: "css/admin-user-profile.css"
            },
            frontend_card_shortcode: {
                src: "src/css/person-card.css",
                dest: "css/person-card.css"
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
            admin_card_shortcode: {
                src: "src/js/admin-card-shortcode.js",
                dest: "js/admin-card-shortcode.min.js"
            },
            admin_edit_profile: {
                src: "src/js/admin-edit-profile.js",
                dest: "js/admin-edit-profile.min.js"
            },
            admin_edit_profile_secondary: {
                src: "src/js/admin-edit-profile-secondary.js",
                dest: "js/admin-edit-profile-secondary.min.js"
            },
            admin_edit_page: {
                src: "src/js/admin-edit-page.js",
                dest: "js/admin-edit-page.min.js"
            },
            admin_people: {
                src: "src/js/admin-people.js",
                dest: "js/admin-people.min.js"
            },
            admin_people_sync: {
                src: "src/js/admin-people-sync.js",
                dest: "js/admin-people-sync.min.js"
            },
            admin_user_profile: {
                src: "src/js/admin-user-profile.js",
                dest: "js/admin-user-profile.min.js"
            },
            frontend_people: {
                src: "src/js/people.js",
                dest: "js/people.min.js"
            }
        },

        watch: {
            styles: {
                files: [ "src/css/*.css", "src/js/*.js" ],
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
