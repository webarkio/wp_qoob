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
                console.error("Error in 'QoobWordpressDriver.savePageData'. Sent data from server is fail.");
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
 * Add new library
 * @param {Array} new lib
 * @param {loadQoobDataCallback} cb - A callback to run.
 */
QoobWordpressDriver.prototype.saveLibrariesData = function(libraries, cb) {
    jQuery.ajax({
        url: this.options.ajaxUrl + '?action=qoob_save_libraries_data',
        type: 'POST',
        data: JSON.stringify(libraries),
        processData: false,
        contentType: "application/json; charset=utf-8",
        dataType: 'json',
        error: function(jqXHR, textStatus) {
            cb(textStatus);
        },
        success: function(response) {
            if (response.success) {
                cb(null, response.success);
            } else {
                if (response.error) {
                    console.error(response.error);
                }
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
QoobWordpressDriver.prototype.mainMenu = function(staticMenu) {
    var customData = [{
        "id": "save-template",
        "label": "Save as template",
        "action": "",
        "icon": ""
    }];

    return jQuery.extend(staticMenu, customData);
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
            if (!imageField.$el.find('.input-file').length) {
                imageField.$el.find('.image-control').append('<input type="file" class="input-file" name="image">');
            }

            imageField.$el.find('input[type=file]').click();

            imageField.$el.find('input[type=file]').change(function() {
                var file = imageField.$el.find('input[type=file]').val();
                if (file.match(/.(jpg|jpeg|png|gif)$/i)) {
                    var formData = new FormData();
                    formData.append( "image", imageField.$el.find('input[type=file]')[0].files[0]);
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
        "action": function(imageField){
            imageField.changeImage(imageField.options.defaults);
            if (imageField.$el.find('.edit-image').hasClass('empty')) {
                imageField.$el.find('.edit-image').removeClass('empty');
            }
        },
        "icon": ""
    }, {
        "id": "wml",
        "label": "WordPress Media library",
        "action": function(imageField){
            self.openUploadDialog(function(error, url){
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