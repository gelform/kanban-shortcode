=== Kanban: Shortcodes ===
Contributors:		gelform
Plugin Name:		Kanban: Shortcodes
Plugin URI:			https://kanbanwp.com/addons/shortcodes/
Description:		Embed your Kanban board on another page, or display a filtered to-do list.
Requires at least:	4.0
Tested up to:		4.7.3
Version:			0.0.5
Release Date:		March 21, 2017
Stable tag:         trunk
Author:				Gelform Inc
Author URI:			http://gelwp.com
License:			GPLv2 or later
License URI:		http://www.gnu.org/licenses/gpl-2.0.html
Text Domain:		kanban-shortcodes
Domain Path: 		/languages/
Tags:               kanban, embed, shortcode



Embed your Kanban board on another page, or display a to-do list.

== Description ==

This is an add-on plugn for [Kanban for WordPress](https://KanbanWP.com). Embed your Kanban board on another page, or display a filtered to-do list.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/kanban-shortcodes` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Add the shortcode `[kanban]` to any page where you would like the Kanban board to appear. An iframe is added to the page, displaying your board.

There are a few options for configuring the iFrame.

`[kanban css="..."]`

Use this option to add your own css. If you do not include it, the default css will be used.

All boards have a class of `.kanban-iframe`.

The default css is:
`
.kanban-iframe {
    border: 1px solid black;
    height: 400px;
    width: 100%;
}
`

`[kanban height="400px"]`

Use this option to set the height of the iFrame. The default is `400px`. This will only be used if the "css" option is not used.

`[kanban width="100%"]`

Use this option to set the width of the iFrame. The default is `100%`. This will only be used if the "css" option is not used.

`[kanban id="1"]`

Use this option to specify which board to default to. This requires the Multiple Board paid add-on.

== Frequently Asked Questions ==

None yet. Email your questions here: https://kanbanwp.com/contact-us/

== Changelog ==

For the changelog, please visit [https://kanbanwp.com/addons/shortcodes/](https://kanbanwp.com/addons/shortcodes/)

== Screenshots ==

1. Just add the chortcode `[kanban]` in the visual editor.
2. Your board rendered when you view the page.
