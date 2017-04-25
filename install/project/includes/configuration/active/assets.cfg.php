<?php

/* Relative File Paths for Web Accessible Directories
 *
 * Please specify the file path RELATIVE FROM THE DOCROOT for all the following web-accessible directories
 * in your QCubed-based web application.
 *
 * For some directories (e.g. the Examples site), if you are no longer using it, you STILL need to
 * have the constant defined.  But feel free to define the directory constant as blank (e.g. '') or null.
 *
 * Note that constants must have a leading slash and no ending slash, and they MUST reside within
 * the Document Root.
 *
 * (We take advantage of the __SUBDIRECTORY__ constant defined above to help simplify this section.
 * Note that this is NOT required.  Feel free to use or ignore.)
 */


define ('__VENDOR_ASSETS__', __SUBDIRECTORY__ . '/vendor');

// Destination for generated form drafts and panel drafts. Relative to __DOCROOT__.
define ('__FORMS__', __SUBDIRECTORY__ . '/project/forms');

define ('__FORM_LIST_ITEMS_PER_PAGE__', 20);

// __DOCROOT__ relative location of QCubed-specific Web Assets (JavaScripts, CSS, Images, and PHP Pages/Popups)
// Note: These locations are for use by the framework only. You should put your own files in __APP*_ASSETS__ directories defined below
define ('__QCUBED_ASSETS__', __SUBDIRECTORY__ . '/vendor/qcubed/qcubed/assets');
//define ('__QCUBED_ASSETS__', __SUBDIRECTORY__ . '/vendor/qcubed/qcubed/assets/_core');
define ('__PROJECT_ASSETS__', __SUBDIRECTORY__ . '/project/assets');

define ('__JS_ASSETS__', __QCUBED_ASSETS__ . '/js');
define ('__CSS_ASSETS__', __QCUBED_ASSETS__ . '/css');
define ('__IMAGE_ASSETS__', __QCUBED_ASSETS__ . '/images');
define ('__PHP_ASSETS__', __QCUBED_ASSETS__ . '/php');


// Location of asset files for your application
define ('__APP_JS_ASSETS__', __PROJECT_ASSETS__ . '/js');
define ('__APP_CSS_ASSETS__', __PROJECT_ASSETS__ . '/css');
define ('__APP_IMAGE_ASSETS__', __PROJECT_ASSETS__ . '/images');
define ('__APP_PHP_ASSETS__', __PROJECT_ASSETS__ . '/php');

define ('__PLUGIN_ASSETS__',  __SUBDIRECTORY__ . '/vendor/qcubed/plugin');
define ('__IMAGE_CACHE__', __APP_IMAGE_ASSETS__ . '/cache');
//define ('__QCUBED_UPLOAD__', __DOCROOT__ . __QCUBED_ASSETS__ . '/upload');

define ('__APP_CACHE_ASSETS__', __PROJECT_ASSETS__ . '/cache');
define ('__APP_CACHE__', __DOCROOT__ . __APP_CACHE_ASSETS__);

define ('__APP_IMAGE_CACHE_ASSETS__', __APP_CACHE_ASSETS__ . '/images');
define ('__APP_IMAGE_CACHE__', __DOCROOT__ . __APP_IMAGE_CACHE_ASSETS__);

define ('__APP_UPLOAD_ASSETS__', __PROJECT_ASSETS__ . '/upload');
define ('__APP_UPLOAD__', __DOCROOT__ . __APP_UPLOAD_ASSETS__);


// If you want to use the local jQuery files, specify the paths relative to __JS_ASSETS__
// or just uncomment the 2 lines below.
define ('__JQUERY_BASE__',  'jquery/jquery.js');
define ('__JQUERY_EFFECTS__',   'jquery/jquery-ui.custom.js');

// The core qcubed javascript file to be used.
// In production or as a performance tweak, you may want to use the compressed "_qc_packed.js" library
define ('__QCUBED_JS_CORE__',  'qcubed.js');
//define ('__QCUBED_JS_CORE__',  '_qc_packed.js');

define ('__JQUERY_CSS__', 'jquery-ui-themes/ui-qcubed/jquery-ui.custom.css');

// Location of the QCubed-specific web-based development tools, like codegen.php
define ('__DEVTOOLS_ASSETS__', __PHP_ASSETS__ . '/_devtools');

// Location of the Examples site
define ('__EXAMPLES__', __PHP_ASSETS__ . '/examples');

// Location of .po translation files
define ('__QI18N_PO_PATH__', __INCLUDES__ . '/i18n');

