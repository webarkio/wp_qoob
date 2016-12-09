=== Qoob - Realtime Frontend Page Builder ===
Contributors: webarkio
Donate link: http://webark.com/qoob/#donate
Tags: page builder, builder, page, pages, visual, responsive, qoob, content, layout, realtime, frontend, frontend builder
Requires at least: 4.1
Tested up to: 4.7
Stable tag: 1.2.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Thank you for choosing Qoob - a high-performance page builder.
It offers a huge amount of tools and features absolutely FREE.


== Description ==

It is the easiest and the fastest website builder for WordPress. No need for coding skills. It is a perfect solution, that save you from any prior design and let you handle it without hiring a professional. Just upload your content and get a working cross-platform website, fully adjusted to all devices available on the market. Our [qoob](http://webark.com/qoob/) website was built with the help of "qoob" plugin.

= Advantages of Qoob =


= Blocks Builder =
You add to your page ready-made blocks. You do not need to think about the size, font, indentation. Everything is ready and works fine. You can easily create your first page in 30 seconds.

[youtube https://www.youtube.com/watch?v=nTvWrpHp80w]

= Live Editing =
You will be amazed to see how it is easy to change the contents of the blocks. We have tried to make this process as easy as possible. And the most amazing thing is that this happens in real time. You can see all changes without leaving the page.

[youtube https://www.youtube.com/watch?v=xkrxfAhzE9E]

= Alaways Looking Good =
QOOB automatically optimizes your website's pages for desktop, tablet and mobile. This ensures your visitors get the best viewing experience no matter what device they use.

[youtube https://www.youtube.com/watch?v=Bnzi-FQpLqk]

= Clean and Intuitive UI =
All the visual editors that you met before almost always do not fit the overall design. They add their own frame, buttons and other elements that, almost always fit into the overall design of the site. Qoob is the only editor that does not do it! This editing site content is simple and straightforward.

[youtube https://www.youtube.com/watch?v=GgnTbYsqOZQ]

= Realtime Editing Mobile Version =
You have not seen it. You can add, delete and edit the unit is in tablet modeor even on a mobile device.

[youtube https://www.youtube.com/watch?v=Mlimu8xuukc]

If you have any questions that are beyond the scope of this help file, please feel free to post me to <https://github.com/webarkio/wp_qoob/issues>. Thanks so much!

== Installation ==

= Installing Through The WordPress Admin =

1. Head over to Plugins > Add New in the admin
2. Search for "qoob"
3. Install & activate the plugin
4. Visit any page in your frontend
5. Click the "Edit with qoob" button on the top of the page and start building your page!

= Installing Through FTP =

1. Download the ZIP file and unpack it
2. Upload the qoob to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. You are now ready to use the qoob builder by going to any page and clicking on qoob

== Documentation ==

[Documentation](http://webark.com/qoob/docs) is available on qoob.

== Frequently Asked Questions ==

= How to create a page with qoob =
Qoob is the editor consisting of blocks. Work in Qoob simplifies the task of page creation.

1. Go to Page -> Add New
2. Click on button "qoob"

= How to add the blocks =

There are several ways for adding the block in qoob.

1. To add a block in the page, please, **click** on the block. Each new block is added to the end of the page, under the blocks that were added earlier. After that you can add blocks by **dragging**.
2. You find a block in the group and **move** it to the necessary place. This place will be highlighted.

= How to edit the blocks =
You can edit information in each block of the page. For this purpose **click** the necessary block on the right of the page. In the left you can see settings of this block.

= How to delete / move blocks =
1. Click the necessary block.
2. By pressing the **Delete / Move** button in the left bottom corner of settings you can change postion of the block or delete it from the page.

= How to change the media center =
1. **Click** the necessary block.
2. In the settings **click** the image, video or icon to choose it. To return to the previous media, click on it again.

== Screenshots ==

1. The qoob builder interface.

2. Sides of the qoob

== Changelog ==

= 1.2.1 =
* Change priority JS files for lib's
* Fix sortable fields
* When changing size viewport scrolling to edit block

= 1.2.0 =
* New demo blocks added
* New lib's system for storing blocks, using wp-options
* groups.json became lib.json and contain addition info about blocks (name and url)

= 1.1.8 =
* Refactoring fields localization

= 1.1.7 =
* Update localization

= 1.1.6 =
* Add localization

= 1.1.5 =
* Refactoring handlebars helper methods

= 1.1.4 =
* Refactoring PHPUnit tests

= 1.1.3 =
* 'Edit with qoob' button for pages in list

= 1.1.2 =
* Fix saving of qoob data through basic wp function update_post_meta()

= 1.1.1 =
* Migrating issue

= 1.1.0 =
* Working with database has been refactored. Custom table 'wp_pages' has gone. Now we are using basic features (page post type and custom field) for data storing.

= 1.0.1 =
* First release

== Upgrade Notice ==

- null