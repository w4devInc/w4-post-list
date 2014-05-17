// JavaScript Document
(function() {
	tinymce.create('tinymce.plugins.W4PL', {
		init : function(ed, url){
			// Register commands
			ed.addCommand('mceW4PL', function() {
				ed.windowManager.open({
					title : 'W4 Post List',
					file : url + '/index.php',
					width : 820 + parseInt(ed.getLang('w4pl.delta_width', 0)),
					height : 550 + parseInt(ed.getLang('w4pl.delta_height', 0)),
					inline : 1
				},{
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('w4pl', {title : 'W4 Post List', cmd : 'mceW4PL', image: url + '/w4pl.png' });

			// Add a node change handler, selects the button in the UI when a image is selected
			//ed.onNodeChange.add(function(ed, cm, n) {
			//	cm.setActive('w4pl', n.nodeName == 'IMG');
			//});
		},

		getInfo : function() {
			return {
				longname : 'W4 Post List',
				author : 'Shazzad Hossain Khan',
				authorurl : 'http://w4dev.com/about',
				infourl : 'http://w4dev.com/w4-plugin/w4-post-list',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('w4pl', tinymce.plugins.W4PL);
})();