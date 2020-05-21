module.exports = function(grunt) {
	'use strict';

	var w4plVersion = '';

	require('matchdep').filterDev('grunt-*').forEach( grunt.loadNpmTasks );

	grunt.getPluginVersion = function() {
		var p = 'w4-post-list.php';
		if ( w4plVersion == '' && grunt.file.exists(p) ) {
			var source = grunt.file.read(p);
			var found = source.match(/Version:\s(.*)/);
			w4plVersion = found[1];
		}
		return w4plVersion;
	};

	grunt.initConfig({
		pkgJson: grunt.file.readJSON( 'package.json' ),
		meta: {
			dev: {
				less: 'src/less'
			},
			prod: {
				css: 'assets/css'
			}
		},
		makepot: {
			all: {
				options: {
					cwd: '.',
					exclude: ['.git/.*', 'assets/.*', 'languages/.*', 'node_modules/.*', 'src/.*'],
					mainFile: 'w4-post-list.php',
					domainPath: 'languages',
					potComments: 'Copyright 2010-{year} W4 Post List.',
					type: 'wp-plugin',
					updateTimestamp: true,
					potHeaders: {
						'language-team': 'W4 Post List <sajib1223@gmail.com>',
						'report-msgid-bugs-to': 'https://wordpress.org/support/plugin/w4-post-list/',
						'Project-Id-Version': 'W4 Post List',
						'language': 'en_US',
						'plural-forms': 'nplurals=2; plural=(n != 1);',
						'x-generator' : 'grunt-wp-i18n'
					}
				}
			}
		},
		less: {
			dist: {
				files: [{
                    expand: true,
                    cwd: '<%= meta.dev.less %>',
                    src: ['*.less'],
                    dest: '<%= meta.prod.css %>',
                    ext: '.css',
                }]
			}
		},
        postcss: {
            options: {
                map: false,
                processors: [
					require('autoprefixer')({browsers: 'last 2 versions'}),
                ]
            },
		    dist: {
		    	src: '<%= meta.prod.css %>/*.css'
		    }
        },
		watch: {
			less: {
				files: ['<%= meta.dev.less %>/*.less'],
				tasks: ['less:dist'],
			}
		},
		cssmin: {
			main: {
				expand: true,
				ext: '.min.css',
				src: [
					'assets/css/*.css',
					'!assets/css/*.min.css',
				]
			}
		},
		uglify: {
			main: {
				expand: true,
				ext: '.min.js',
				src: [
					'assets/js/*.js',
					'!assets/js/*.min.js',
				]
			}
		},
		'string-replace': {
			inline: {
				files: {
				'./': ['w4-post-list.php', 'includes/class-w4-post-list.php']
				},
				options: {
					replacements: [
						{
							pattern: 'Version: ' + grunt.getPluginVersion(),
							replacement: 'Version: <%= pkgJson.version %>'
						}, {
							pattern: 'plugin_version = \'' + grunt.getPluginVersion() + '\'',
							replacement: 'plugin_version = \'<%= pkgJson.version %>\''
						}
					]
				}
			}
		},
		clean: {
			options: {
				force: true
		    },
			trunk: [ '../SVN-w4-post-list/trunk/*' ],
			css: [ '<%= meta.prod.css %>/*.css' ],
	  	},
		copy: {
			svn: {
				files: [{
					expand: true,
					src: [
						'admin/**',
						'assets/**',
						'includes/**',
						'languages/**',
						'vendor/**',
						'index.php',
            'blocks.php',
						'appsero.php',
						'w4-post-list.php',
						'readme.txt'
					],
					dest: '../SVN-w4-post-list/trunk/'
				}]
			}
		}
	});

	grunt.registerTask( 'update-version', [ 'string-replace' ] );
	grunt.registerTask( 'css', [ 'clean:css', 'less:dist', 'postcss:dist' ] );
	grunt.registerTask( 'assets', [ 'css', 'cssmin:main', 'uglify:main' ] );

	grunt.registerTask( 'build', [ 'makepot', 'update-version', 'assets' ] );
	grunt.registerTask( 'deploy', [ 'clean:trunk', 'build', 'copy:svn' ] );
};
