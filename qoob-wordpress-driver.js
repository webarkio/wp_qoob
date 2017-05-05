/**
 * Class for work with ajax methods
 *  
 * @version 0.0.1
 * @class  QoobWordpressDriver
 */
//module.exports.QoobWordpressDriver = QoobWordpressDriver;
function QoobWordpressDriver(options) {
    this.options = options;
    //    this.assets = [{"type":"js","name":"media-models", "src":"/wp-includes/js/media-models.js"}];
}

/**
 * Get url iframe
 * 
 * @returns {String}
 */
QoobWordpressDriver.prototype.getIframePageUrl = function() {
    return this.options.iframeUrl;
};

/**
 * Go to the admin view of the edited page
 * 
 * @returns {String}
 */
QoobWordpressDriver.prototype.exit = function() {
    window.location.href = 'post.php?post=' + this.options.pageId + '&action=edit';
};

/** 
 * @callback savePageDataCallback
 */

/**
 * Save page data
 * 
 * @param {integer} pageId
 * @param {Array} data DOMElements and JSON
 * @param {savePageDataCallback} cb - A callback to run.
 */
QoobWordpressDriver.prototype.savePageData = function(data, cb) {
    var dataToSend = JSON.stringify({
        pageId: this.options.pageId,
        data: data
    });
    jQuery.ajax({
        url: this.options.ajaxUrl + '?action=qoob_save_page_data',
        type: 'POST',
        data: dataToSend,
        processData: false,
        contentType: "application/json; charset=utf-8",
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                cb(null, response.success);
            } else {
                cb(response.error, response.success);
                if (response.error) {
                    console.error(response.error);
                } else {
                    console.error("Error in 'QoobWordpressDriver.savePageData'. Sent data from server is fail.");
                }
            }
        },
        error: function(xrh, error) {
            console.error(error);
        }
    });
};

/**
 * Callback for get page data
 * 
 * @callback loadPageDataCallback
 */

/**
 * Get page data
 * 
 * @param {integer} pageId
 * @param {loadPageDataCallback} cb - A callback to run.
 */
QoobWordpressDriver.prototype.loadPageData = function(cb) {
    jQuery.ajax({
        url: this.options.ajaxUrl,
        type: 'POST',
        data: {
            action: 'qoob_load_page_data',
            page_id: this.options.pageId,
            lang: 'en'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                cb(null, response.data);
            } else {
                console.error("Error in 'QoobWordpressDriver.loadPageData'. Returned data from server is fail.");
                if (response.error) {
                    console.error(response.error);
                }
            }
        },
        error: function(xrh, error) {
            console.error(error);
        }
    });
};

/**
 * Get qoob libs
 * 
 * @param {loadQoobDataCallback} cb - A callback to run.
 */
QoobWordpressDriver.prototype.loadLibrariesData = function(cb) {
    jQuery.ajax({
        url: this.options.ajaxUrl,
        type: 'POST',
        data: {
            action: 'qoob_load_libraries_data'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.libs) {
                cb(null, response.libs);
            } else {
                console.error("Error in 'QoobWordpressDriver.loadLibrariesData'. Returned data from server is fail.");
                if (response.error) {
                    console.error(response.error);
                }
            }
        },
        error: function(xrh, error) {
            console.error(error);
        }
    });
};

/**
 * Save page template
 * 
 * @param {savePageTemplateCallback} cb - A callback to run.
 */
QoobWordpressDriver.prototype.savePageTemplate = function(data, cb) {
    jQuery.ajax({
        url: this.options.ajaxUrl + '?action=qoob_save_page_template',
        type: 'POST',
        data: JSON.stringify(data),
        processData: false,
        contentType: "application/json; charset=utf-8",
        dataType: 'json',
        success: function(response) {
            cb(null, response.success);
        }
    });
};

/**
 * Load page templates
 * 
 * @param {loadPageTemplatesCallback} cb - A callback to run.
 */
QoobWordpressDriver.prototype.loadPageTemplates = function(cb) {
    jQuery.ajax({
        dataType: "json",
        url: this.options.ajaxUrl,
        type: 'POST',
        data: {
            action: 'qoob_load_page_templates'
        },
        error: function(jqXHR, textStatus) {
            console.error("Error in 'QoobWordpressDriver.loadPageTemplates'. Returned data from server is fail.");
            cb(textStatus);
        },
        success: function(response) {
            if (response.success && response.templates) {
                cb(null, JSON.parse(response.templates));
            }
        }
    });
};

/**
 * Load main menu
 * @param {Array} staticMenu
 * @returns {Array}
 */
QoobWordpressDriver.prototype.mainMenu = function(staticMenu) {
    var self = this;
    var customData = [{
        "id": "save-template",
        "label": "Save as template",
        "action": "",
        "icon": ""
    }, {
        "id": "show-frontend",
        "label": "Show on frontend",
        "action": function() {
            window.open(self.getIframePageUrl().replace('&qoob=true', '').replace('?qoob=true', ''), '_blank');
        },
        "icon": ""
    }];

    return staticMenu.concat(customData);
};

/**
 * Upload image
 * @param {Array} data
 * @param {uploadCallback} cb - A callback to run.
 */
QoobWordpressDriver.prototype.upload = function(data, cb) {
    jQuery.ajax({
        url: this.options.ajaxUrl,
        type: 'POST',
        data: data,
        processData: false,
        contentType: false,
        error: function(jqXHR, textStatus) {
            cb(textStatus)
            console.error(textStatus);
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data.success) {
                cb(null, data.url);
            } else {
                if (data.error) {
                    console.error(data.message);
                    cb(true);
                }
            }
        }
    });
};

/**
 * Show dialog media WP
 * @param {openUploadDialogCallback} cb - A callback to run.
 */
QoobWordpressDriver.prototype.openUploadDialog = function(cb) {
    //Create media upload frame
    var mcFrame = wp.media({
        multiple: false // Set to true to allow multiple files to be selected  
    });
    //On submit - save submitted url
    mcFrame.on('select', function() {
        // Get media attachment details from the frame state
        var attachment = mcFrame.state().get('selection').first().toJSON();
        if (attachment) {
            cb(null, attachment.url);
        } else {
            cb(true);
            console.error('Please select an image to upload!');
        }

    }.bind(this));
    //Open media frame
    mcFrame.open();
};

/**
 * Custom field image action
 * @param {Array} actions
 * @returns {Array}
 */
QoobWordpressDriver.prototype.fieldImageActions = function(actions) {
    var self = this;
    var customActions = [{
        "id": "upload",
        "label": "Upload",
        "action": function(imageField) {
            imageField.$el.find('.image-control').find('.input-file').remove();
            imageField.$el.find('.image-control').append('<input type="file" class="input-file" name="image">');

            imageField.$el.find('.input-file').trigger('click');

            imageField.$el.find('.input-file').change(function() {
                var file = imageField.$el.find('input[type=file]').val();
                if (file.match(/.(jpg|jpeg|png|gif)$/i)) {
                    var formData = new FormData();
                    formData.append("image", imageField.$el.find('input[type=file]')[0].files[0]);
                    formData.append("action", "qoob_add_new_image");
                    self.upload(formData, function(error, url) {
                        if ('' !== url) {
                            imageField.changeImage(url);
                            if (imageField.$el.find('.edit-image').hasClass('empty')) {
                                imageField.$el.find('.edit-image').removeClass('empty');
                            }
                        }
                    });
                } else {
                    console.error('file format is not appropriate');
                }
            });
        },
        "icon": ""
    }, {
        "id": "reset",
        "label": "Reset to default",
        "action": function(imageField) {
            imageField.changeImage(imageField.options.defaults);

            if ('' === imageField.options.defaults) {
                if (!imageField.$el.find('.edit-image').hasClass('empty')) {
                    imageField.$el.find('.edit-image').addClass('empty');
                }
            } else {
                imageField.$el.find('.edit-image').removeClass('empty');
            }
        },
        "icon": ""
    }, {
        "id": "wml",
        "label": "WordPress Media library",
        "action": function(imageField) {
            self.openUploadDialog(function(error, url) {
                if ('' !== url) {
                    imageField.changeImage(url);
                    if (imageField.$el.find('.edit-image').hasClass('empty')) {
                        imageField.$el.find('.edit-image').removeClass('empty');
                    }
                }
            });
        },
        "icon": ""
    }];

    var glueActions = actions.concat(customActions);

    return glueActions;
};

/**
 * Custom field video action
 * @param {Array} actions
 * @returns {Array}
 */
QoobWordpressDriver.prototype.fieldVideoActions = function(actions) {
    var self = this;
    var customActions = [{
        "id": "upload",
        "label": "Upload",
        "action": function(videoField) {
            videoField.$el.find('.video-control').find('.input-file').remove();
            videoField.$el.find('.video-control').append('<input type="file" class="input-file" name="video">');

            videoField.$el.find('input.input-file').trigger('click');

            videoField.$el.find('input.input-file').change(function() {
                var s = this;
                var file = jQuery(this).val();

                if (file.match(/.(mp4)$/i)) {
                    var formData = new FormData();
                    formData.append('video', jQuery(this)[0].files[0]);
                    formData.append("action", "qoob_add_new_video");
                    self.upload(formData, function(error, url) {
                        if ('' !== url) {
                            var src = { 'url': url, preview: '' };
                            videoField.changeVideo(src);
                            jQuery(s).val('');
                            if (!videoField.$el.find('.edit-video').hasClass('empty')) {
                                videoField.$el.find('.edit-video').addClass('empty');
                            }
                        }
                    });
                } else {
                    console.error('file format is not appropriate');
                }
            });
        },
        "icon": ""
    }, {
        "id": "reset",
        "label": "Reset to default",
        "action": function(videoField) {
            videoField.changeVideo(videoField.options.defaults);
            if (videoField.$el.find('.edit-video').hasClass('empty')) {
                videoField.$el.find('.edit-video').removeClass('empty');
            }
        },
        "icon": ""
    }, {
        "id": "wml",
        "label": "WordPress Media library",
        "action": function(videoField) {
            self.openUploadDialog(function(error, url) {
                console.log(url);
                if ('' !== url) {
                    var src = { 'url': url, preview: '' };
                    videoField.changeVideo(src);
                    if (!videoField.$el.find('.edit-video').hasClass('empty')) {
                        console.log('tut');
                        videoField.$el.find('.edit-video').addClass('empty');
                    }
                }
            });
        },
        "icon": ""
    }];

    var glueActions = actions.concat(customActions);

    return glueActions;
};

/**
 * Upload image
 * @param {Array} dataFile
 * @param {uploadCallback} cb - A callback to run.
 */
QoobWordpressDriver.prototype.uploadImage = function(dataFile, cb) {
    var formData = new FormData();
    formData.append('image', dataFile[0]);
    formData.append("action", "qoob_add_new_image");

    this.upload(formData, function(error, url) {
        cb(error, url);
    });
};
