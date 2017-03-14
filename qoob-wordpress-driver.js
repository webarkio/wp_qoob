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
        page_id: this.options.pageId,
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
 * Load template
 * 
 * @param {loadPageTemplatesCallback} cb - A callback to run.
 */
QoobWordpressDriver.prototype.loadPageTemplates = function(cb) {
    cb(null);
    // jQuery.ajax({
    //     dataType: "json",
    //     url: this.templatesDataUrl,
    //     error: function(jqXHR, textStatus) {
    //         cb(textStatus);
    //     },
    //     success: function(data) {
    //         cb(null, data);
    //     }
    // });
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


QoobWordpressDriver.prototype.openUploadDialog = function(cb) {

}

QoobWordpressDriver.prototype.upload = function(cb) {
    //Create media upload frame
    var mcFrame = wp.media({
        // title: "title",// this.storage.__('media_title', 'Select or Upload Media Of Your Chosen Persuasion'),
        // button: {
        //     text: "text" //this.storage.__('media_text_button', 'Use this media')
        // },
        multiple: false // Set to true to allow multiple files to be selected  
    });
    //On submit - save submitted url
    mcFrame.on('select', function() {
        // Get media attachment details from the frame state
        var attachment = mcFrame.state().get('selection').first().toJSON();
        cb(null, attachment.url);
    }.bind(this));
    //Open media frame
    mcFrame.open();

};
