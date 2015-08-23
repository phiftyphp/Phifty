# Changelog

## Version 3.0

### Incompatible changes

- Renamed Phifty\Service to Phifty\ServiceProvider
- Renamed all Service classes with suffix `ServiceProvider`
- Updated config key `Services` to `ServiceProviders`

Version 2.9

- Added bin/phifty-bootstrap

Version 2.8

1. Rename 'User' bundle to 'UserBundle'
2. Remove old 'Pages'
3. Remove old 'Product'

Version 2.7.11

1. Add Product Chooser for related product.
2. FileBundle improvement.

Version 2.7.5

1. Rename `plugins` to `bundles`, we simply change the name, need to refactor the bundle stuff out.
2. Define `PH_APP_ROOT` in bin/phifty and webroot/index.php, index.php should be updated to the application.
3. Rename 'assets' to 'Assets', Rename 'templates' to 'Templates'
4. Improve the bundle generator.
5. Improve locale parsing, updating command.

    find bundles -type d -name "template" -exec rename 's/template/Templates/' {} \;

6. Clean up PhiftyController createView nad view methods
7. CRUDHandler
    1. Automatically build CRUDAction from CRUDHandler
    2. Add page.html and index.html to CRUD templates
    3. Add index region to CRUDHandler
8. Move RequirementCommand to CheckCommand.
9. Use new AdminUI menu system.
10. Add flip effect to list region.
11. Add animate.css support.


Version 2.4.0 - Sun Nov 18 18:31:38 2012

1. Improve AdminUI panel js.
2. Improve AdminUI menu style for other browsers.
3. Upgrade jquery and jquery-ui version.
4. Fix for IE7.
5. Add sitemapkit plugin.
6. SEO plugin
7. Quicksearch field
8. Image cropper improvement.

Version 2.2.1 - Tue Oct 30 17:46:07 2012

1. Add jQuery 1.8.2 core version.

Version 2.2.0 - Thu Oct 18 17:38:20 2012

### merged branches

1. feature/statusplugin - status mixin model and filter.
2. feature/seoplugin    - seo mixin model and editor.
3. fix/editor-close     - fix front-end record saving behavior.

## Major Version 2.1

Version 2.1.26 - Thu Oct 18 13:37:35 2012

- Bug fixes (site/modelshop)

Version 2.1.25 - Mon Oct 15 12:52:21 2012

- Added Recipe detailed item editor.
- Added News images and resources.
- Updated action messages

Version 2.1.24 - Sat Oct 13 17:02:50 2012

- Ajax login
- Javascript image cropper plugin

Version 2.1.23 - Wed Oct 10 18:43:01 2012

Released branches

- feature/nestedform
- feature/tabpanel
- feature/tcpdf
- feature/eventbundle
- feature/crud-chooser

Added options:

    RecipeBundle:
        with_product_link: 1
        with_type: 1
        with_description: 1

site/sparlar - Mon Oct  1 20:09:13 2012

- Rename parent column to parent\_id.
- Add "parent" relationship.
- Add "image", "thumb" columns to product category.
- Remove CRUD.Title, please use CRUD.Object.getListTitle or
  CRUD.Object.getEditTitle instead.
- News Event: change table name to `news_events`.
- Auto-Scroll when closing region.

site/vstock - Sun Sep 30 09:19:48 2012

- Add current user role, check instsanceof from user\model\user
- Add method\_exists checking for current user model (backward compatible support)

Version 2.1.22 - Sat Sep 29 16:09:52 2012

- Add transition chooser (javascript).
- Add crud chooser.
- Import tchooser.coffee
- More service tests.
- Add ViewService.

Version 2.1.21 - Mon Sep 24 13:18:36 2012

- Support Panel Region.
- Fixed image upload of Action.
- Improve region-js

Version 2.1.20 - Sat Sep 22 03:09:59 2012

- Fixed Image column class.
- Fixed Image compression level.
- Improved button style.
- Fixed TinyMCE bug of Action-js (update editor content to textarea field before submitting)

Version 2.1.19 - Thu Sep 20 16:04:53 2012

- Kendo ACL fix.
- Classloader sequence fix.
- Translation update.
- Region.before fix.
- Add `no_form` option to Action Widget.

Version 2.1.18 - Wed Sep 19 21:00:47 2012

- Merge changes from site/drshine it system.

Version 2.1.17 - Wed Sep 19 16:30:37 2012

- IE fix
- StatsQuery fix

Version 2.1.16 - Wed Sep 19 01:23:05 2012

- Fix remote tracker js

Version 2.1.15 - Wed Sep 19 01:13:18 2012

- Add remote logging controller for Tracker

Version 2.1.14 - Tue Sep 18 21:14:15 2012

- Improved Page Tracker
    
- Let Tracker plugin can record access log for different model
- Enhacned front-end javascript and be adapted to different record type
- framework.ci.yml is updated.

Version 2.1.13 - Mon Sep 17 17:39:24 2012

- Improve ContactUs plugin, supports attachment
- Fix plugins/ImageData/Model/Image.php
- Fix Pages's PagesController
- Update product.coffee for config options
- Fix webroot/index.php (for IE)

Version 2.1.12 - Sun Sep 16 12:56:48 2012

- Refactor CRUD js (record-edit-btn, record-delete-btn)
- Improve Pages Editor
- Refactor TinyMCE initialize js

Version 2.1.11 - Sun Sep 16 00:15:28 2012

- Refactor Product js to product.coffee
- Move action-js, webtoolkit-aim, region-js to Core assets.
- Re-write action, action plugins with coffeescript.
- Re-write product js with coffeescript.
- Provide ajax-action class for CRUD template.

Version 2.1.10 - Thu Sep 13 01:13:42 2012

- Add validation support.
- Support LazyRecord validation.
- Integrate with ValidationKit.
- Use the latest LazyRecord.
- Improve BannerSlider management UI.

Version 2.1.9 - Mon Sep 10 15:38:22 2012

- Source field file upload fix (Action)

Version 2.1.8 - Sun Sep  9 19:49:43 2012

- Remove prefixPath attribute method from plugin actions.
- Remove validExtensions attribute method from plugin actions.
  since it's default.
- Add image prefix path to ImageFileInput and ThumbImageFileInput to 
  FormKit widgets.
- Provide server command (To use PHP built-in http server)
- Refactor selenium test helpers

Version 2.1.7 - Sun Sep 16 00:18:11 2012

branch `improvement/schema_files`
- ignored and removed schemaproxy files

branch `site/sodastream`
- added `with_lang` option to News/Product plugins
- refactro product category template

Version 2.1.5 - 日  8/19 23:09:45 2012

Released branches:

- ready/feature/barcode
- ready/feature/configkit
- ready/feature/generator
- ready/improvement/schema_files

Improvements:

- generator improvement
    - phifty new schema
    - phifty new controller
    - phifty new action
- import barcode library
- improved config loader (configkit)
- ignore schema files (in .gitignore)

Version 2.1.4 - 二  8/14 23:54:20 2012

Released branches

  ready/feature/hotelmg
  ready/feature/mixin
  ready/feature/tour
  ready/improvement/testing
  site/ctravel
  site/ibiyaya
  site/modelshop

- Added HotelBundle.
- Added TourBundle.
- Added no_wrapper option to @CRUD/edit
- Added skips options to AdminUI\Action\View\StackView
- Fixed News plugin `with_icon` option.
- Added debugtool function (`get_class_file`)
- Added migration note of site/ctravel and site/ibiyaya
- Mixin Schemas (I18N and Metadata)
- Support Selenium Testing
- Added Selenium Tests: NewsTest, ProductTest, NewsCategoryTest 
- Added Selenium Testing helper


Version 2.1.3 - 日  8/12 16:53:50 2012

- Add images option to News plugin.
- Redesign layout of News panel.
- Add original size image file for Product images.
- Move CurrentUser to Phifty\Security\CurrentUser
- Update current user service config

  CurrentUserService:
    Class: \Phifty\Security\CurrentUser
    Model: \User\Model\User


- kernel()->web is deprecated.
- CurrentUser config is migrated to service config.
- build.xml is now ready for Jenkins-CI and Apache ANT
- scripts/install-deps.sh is updated.
- phifty.php is moved to bin/phifty from scripts/phifty.php
- bootstrap script is moved to src/Phifty/Bootstrap.php

Version 2.1.2 - 六  3/17 13:53:37 2012

- Rename plugins CRUD to {crud id}/edit.html 
- Rename widgets to assets
- Change CRUD.Edit.Record => CRUD.Record 
- Move CRUD templates out, into plugins/CRUD/
- Use SessionKit for session.
- Enabled file field validation.
- Added crud_controll block
- Added Excel Import Action and Controller
- Added MemcacheService

二 12/27 14:38:33 2011

- Remove kernel()->currentUser() usage.

日 12/11 18:54:52 2011

Inquiry Cart 
    - floating cart div
    - bottom right bubble message
    - can add product name to the cart list.
    - contact form

Page Importer
    - render page template.
    - get content via dom
    - insert content into database.


Js I18N
    - Fix i18n env variable export for Javascript
    - js i18n parser 
    - js i18n export to json with language code

六 11/26 03:24:25 2011

News plugin:
- remove icon image column.
- add thumb column.
- add image column.
- rename with_icon option to with_image.

六 11/ 5 11:51:28 2011

- Change route api and refactor route dispatcher.

** model changes

- news plugin: is_cover column.
- usecase plugin: lang columns.
- product plugin: hide, token column.

五 10/28 19:30:28 2011

- Change plugin paths:  core to CoreBundle/,  core/lib/Core.php CoreBundle/Core.php
- Fix tinyMCE bugs

日 10/23 22:01:04 2011

- Rename model->data to model->_data
- provide model->getData method
- Change model->label to protected.

三  9/28 13:50:17 2011

### Feature-iconnews branch

- Change plugin config setter, getter: {{plugin}}::getInstance()->config( "key" );
  - Add {{plugin}}::getInstance()->configHash
  - Since static variable doesn't work for subclasses...
- Use Exporter to export vars to template
	- remove phifty scope.
- Add locale command to merge po files from framework.
- Import Twig Text extension.
- Fix AdminUI menu, div blocks can't be show in IE.
- Add User CRUD Pages, Password Change, Edit, Update, Create.
- Improve UniversalClassLoader.

### Config improvement
- load config/app.yml and config/app_site.yml first.

    appname => app_id
    sitename => app_name
    
- then load config/dev.yml or config/prod.yml or config/testing.yml


0.3
	* change validParis label => key   to key => lable.
	* use inflator, table name should plural (***important***)
	* change microapp structure.

		app/Model
		app/Controller
		app/...
		app/...

0.2
	* Action js refactor
	* Action PHP Class refactor
