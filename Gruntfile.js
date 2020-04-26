module.exports = function(grunt) {
	'use strict';

	var w4plVersion = '';
	var pkgJson = grunt.file.readJSON('package.json');

	require('matchdep').filterDev('grunt-*').forEach( grunt.loadNpmTasks );

	grunt.getPluginVersion = function() {
		var p = 'w4-post-list.php';
		if (w4plVersion == '' && grunt.file.exists(p)) {
			var source = grunt.file.read(p);
			var found = source.match(/Version:\s(.*)/);
			w4plVersion = found[1];
		}
		return w4plVersion;
	};

	grunt.initConfig({
		pkg: '<json:package.json>',
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
		uglify: {
			main: {
				expand: true,
				ext: '.min.js',
				src: [
					'assets/js/*.js',
					// Exclusions
					'!assets/js/*.min.js',
				]
			}
		},
		cssmin: {
			main: {
				expand: true,
				ext: '.min.css',
				src: [
					'assets/css/*.css',
					// Exclusions
					'!assets/css/*.min.css',
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
							replacement: 'Version: ' + pkgJson.version
						}, {
							pattern: 'plugin_version = \'' + grunt.getPluginVersion() + '\'',
							replacement: 'plugin_version = \'' + pkgJson.version + '\''
						}
					]
				}
			}
		},
		clean: {
			options: {
				force: true
		    },
			build: [ '../SVN-w4-post-list-test/trunk/*' ]
	  	},
		copy: {
			svn: {
				files: [{
					expand: true,
					src: [
						'admin/**',
						'assets/**',
						'build/**',
						'includes/**',
						'languages/**',
						'public/**',
						'index.php',
						'w4-blocks.php',
						'w4-post-list.php',
						'readme.txt',
						'screenshot-1.png',
						'screenshot-2.png',
						'screenshot-3.png'
					],
					dest: '../SVN-w4-post-list/trunk/'
				}]
			}
		}
	});

	grunt.registerTask( 'update-version', [ 'string-replace' ] );
	grunt.registerTask( 'minify', [ 'cssmin:main', 'uglify:main' ] );
	grunt.registerTask( 'build', [ 'makepot', 'update-version', 'minify' ] );
	grunt.registerTask( 'deploy-svn', [ 'clean', 'copy:svn' ] );
};
