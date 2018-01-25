/**
 * Class for work with ajax methods
 *  
 * @version 0.0.3
 * @class  QoobWordpressDriver
 */
//module.exports.QoobWordpressDriver = QoobWordpressDriver;
function QoobWordpressDriver(options) {
    this.options = options || {};
    this.pages = this.options.pages || null;
    this.page = this.options.page || '';
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
        error: function(xmlHttpRequest, textStatus) {
            if (xmlHttpRequest.readyState == 0 || xmlHttpRequest.status == 0) {
                return; // it's not really an error
            } else {
                // Do normal error handling
                console.error(textStatus);
            }
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
QoobWordpressDriver.prototype.mainMenu = function(menu) {
    var self = this;
    
    menu.push({
        "id": "save-template",
        "label": {"save_as_template": "Save as template"},
        "action": "",
        "icon": ""
    }, {
        "id": "show-frontend",
        "label": {"showOnFrontend": "Show on frontend"},
        "action": function() {
            window.open(self.getIframePageUrl().replace('&qoob=true', '').replace('?qoob=true', ''), '_blank');
        },
        "icon": ""
    });

    return menu;
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
        "label": {"upload": "Upload"},
        "action": function(imageField) {
            imageField.$el.find('.input-file').remove();
            imageField.$el.append('<input type="file" class="input-file" name="image">');

            imageField.$el.find('.input-file').trigger('click');

            imageField.$el.find('.input-file').change(function() {
                var file = imageField.$el.find('input[type=file]').val(),
                    container = imageField.$el.find('.field-image-container');

                // 2 MB limit
                if (jQuery(this).prop('files')[0].size > 2097152) {
                    container.addClass('upload-error');
                } else {
                    if (file.match(/.(jpg|jpeg|png|gif)$/i)) {
                        var formData = new FormData();
                        formData.append('image', jQuery(this)[0].files[0], jQuery(this)[0].files[0].name);
                        formData.append("action", "qoob_add_new_image");
                        self.upload(formData, function(error, url) {
                            if ('' !== url) {
                                imageField.changeImage(url);
                                imageField.$el.find('input[type=file]').val('');
                                if (container.hasClass('empty') || container.hasClass('upload-error')) {
                                    container.removeClass('empty upload-error');
                                }
                            }
                        });
                    } else {
                        console.error('file format is not appropriate');
                    }
                }
            });
        },
        "icon": ""
    }, {
        "id": "wml",
        "label": {"WordPressMediaLibrary": "WordPress Media library"},
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
    }, {
        "id": "reset",
        "label": {"resetToDefault": "Reset to default"},
        "action": function(imageField) {
            imageField.changeImage(imageField.options.defaults);

            var container = imageField.$el.find('.field-image-container');

            if ('' === imageField.options.defaults) {
                if (!container.hasClass('empty')) {
                    container.addClass('empty');
                }
            } else {
                container.removeClass('empty upload-error');
            }
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
        "label": {"upload": "Upload"},
        "action": function(videoField) {
            videoField.$el.find('.input-file').remove();
            videoField.$el.append('<input type="file" class="input-file" name="video">');

            videoField.$el.find('.input-file').trigger('click');

            videoField.$el.find('.input-file').change(function() {
                var parent = jQuery(this),
                    container = videoField.$el.find('.field-video-container'),
                    file = jQuery(this).val();

                if (container.hasClass('empty') || container.hasClass('upload-error')) {
                    container.removeClass('empty upload-error upload-error__size upload-error__format');
                }

                // 8 MB limit
                if (jQuery(this).prop('files')[0].size > 8388608) {
                    container.addClass('upload-error upload-error__size');
                } else {
                    if (file.match(/.(mp4|ogv|webm)$/i)) {
                        var formData = new FormData();
                        formData.append("action", "qoob_add_new_video");
                        formData.append('video', jQuery(this)[0].files[0], jQuery(this)[0].files[0].name);
                        self.upload(formData, function(error, url) {
                            if ('' !== url) {
                                var src = { 'url': url, preview: '' };
                                videoField.changeVideo(src);
                                parent.val('');

                                if (!container.hasClass('empty-preview')) {
                                    container.addClass('empty-preview');
                                }
                            }
                        });
                    } else {
                        container.addClass('upload-error upload-error__format');
                    }
                }
            });
        },
        "icon": ""
    }, {
        "id": "wml",
        "label": {"WordPressMediaLibrary": "WordPress Media library"},
        "action": function(videoField) {
            self.openUploadDialog(function(error, url) {
                if ('' !== url) {
                    var src = { 'url': url, preview: '' };
                    videoField.changeVideo(src);

                    var container = videoField.$el.find('.field-video-container');

                    if (!container.hasClass('empty-preview')) {
                        container.addClass('empty-preview');
                    }
                }
            });
        },
        "icon": ""
    }, {
        "id": "reset",
        "label": {"resetToDefault": "Reset to default"},
        "action": function(videoField) {
            var container = videoField.$el.find('.field-video-container');

            videoField.changeVideo(videoField.options.defaults);
            if (container.hasClass('empty') ||
                container.hasClass('empty-preview') ||
                container.hasClass('upload-error')) {
                container.removeClass('empty empty-preview upload-error');
            }
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

/**
 * Upload video
 * @param {Array} dataFile
 * @param {uploadCallback} cb - A callback to run.
 */
QoobWordpressDriver.prototype.uploadVideo = function(dataFile, cb) {
    var formData = new FormData();
    formData.append('video', dataFile[0], dataFile[0].name);
    formData.append("action", "qoob_add_new_video");

    this.upload(formData, function(error, url) {
        cb(error, url);
    });
};
