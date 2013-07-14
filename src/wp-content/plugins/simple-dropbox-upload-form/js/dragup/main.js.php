<?php 
header("Content-type: text/javascript");
$incPath = str_replace(substr_replace(dirname(__FILE__),"",0,strpos(dirname(__FILE__),"wp-content")),"",getcwd());

include($incPath.'/wp-load.php');
?>
/*
 * jQuery File Upload Plugin JS Example 6.7
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/*jslint nomen: true, unparam: true, regexp: true */
/*global $, window, document */

$(function () {
    'use strict';

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload();

    // Enable iframe cross-domain access via redirect option:
    $('#fileupload').fileupload(
        'option',
        'redirect',
        window.location.href.replace(
            /\/[^\/]*$/,
            '/cors/result.html?%s'
        )
    );

    if (window.location.hostname === 'blueimp.github.com') {
        // Demo settings:
        $('#fileupload').fileupload('option', {
            url: '//jquery-file-upload.appspot.com/',
            maxFileSize: 5000000,
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
            process: [
                {
                    action: 'load',
                    fileTypes: /^image\/(gif|jpeg|png)$/,
                    maxFileSize: 20000000 // 20MB
                },
                {
                    action: 'resize',
                    maxWidth: 1440,
                    maxHeight: 900
                },
                {
                    action: 'save'
                }
            ]
        });
        // Upload server status check for browsers with CORS support:
        if ($.support.cors) {
            $.ajax({
                url: '//jquery-file-upload.appspot.com/',
                type: 'HEAD'
            }).fail(function () {
                $('<span class="alert alert-error"/>')
                    .text('Upload server currently unavailable - ' +
                            new Date())
                    .appendTo('#fileupload');
            });
        }
    } else {
	    $('#fileupload').fileupload('option', {
            //maxFileSize: 5000000,
            maxFileSize: <?php echo (get_option('wpsdb_show_multi_size'))? (get_option('wpsdb_show_multi_size')*1024)*1024:1024*1024?>,
            //acceptFileTypes: /(\.|\/)(gif|jpe?g|png|psd)$/i,
            acceptFileTypes: /(\.|\/)(<?php $wpsdb_allow_ext = trim( get_option( 'wpsdb_allow_ext' ) );$wpsdb_allow_ext = str_replace(' ', '|', $wpsdb_allow_ext);echo $wpsdb_allow_ext;?>)$/i,
            singleFileUploads: true,
            sequentialUploads: true,
            autoUpload: true,
            <?php /*process: [
                {
                    action: 'load',
                    fileTypes: /^image\/(gif|jpeg|png)$/,
                    maxFileSize: 20000000 // 20MB
                },
                {
                    action: 'resize',
                    maxWidth: 1440,
                    maxHeight: 900
                },
                {
                    action: 'save'
                }
            ]*/?>
        });
        
        var upfiles = "";
        $('#fileupload')
        //.bind('fileuploaddrop', function (e, data) {$.each(data.files, function (index, file) {alert('Added file: ' + file.name);});})
        //.bind('fileuploaddrop', function (e, data) {$.each(data.files, function (index, file) { upfiles += file.name + ",";});})
        .bind('fileuploaddone', function (e, data) {$.each(data.files, function (index, file) { upfiles += file.name + ",";});})
        .bind('fileuploadchange', function (e, data) {/* ... */})
        
        //fail: function (e, data) {data.submit();}
        //.fileupload({fail: function (e, data) {alert('FAIL');}});
        ;
        
        
	   $('#fileupload')
        .bind('fileuploadstop', function (e, data) {
		   //window.location.href = 'http://hiphopsmurf.com';
             $('#multimages', top.document).val(upfiles);
             parent.document.forms["multi_image"].submit();
             //parent.tb_remove();
	    });
        // Upload server status check for browsers with CORS support:
        /*if ($.support.cors) {
            $.ajax({
                url: '//jquery-file-upload.appspot.com/',
                type: 'HEAD'
            }).fail(function () {
                $('<span class="alert alert-error"/>')
                    .text('Upload server currently unavailable - ' +
                            new Date())
                    .appendTo('#fileupload');
            });
        }*/
        // Load existing files:
        /*$('#fileupload').each(function () {
            var that = this;
            $.getJSON(this.action, function (result) {
                if (result && result.length) {
                    $(that).fileupload('option', 'done')
                        .call(that, null, {result: result});
                }
            });
        });*/
    }

});
