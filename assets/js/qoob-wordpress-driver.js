/**
 * Class for work with ajax methods
 *  
 * @version 0.0.1
 * @class  QoobWordpressDriver
 */
//module.exports.QoobWordpressDriver = QoobWordpressDriver;
function QoobWordpressDriver() {}

/**
 * Get url iframe
 * 
 * @returns {String}
 */
QoobWordpressDriver.prototype.getIframePageUrl = function(pageId) {
    return ajax.iframe_url;
};

/**
 * Go to the admin view of the edited page
 * 
 * @returns {String}
 */
QoobWordpressDriver.prototype.exit = function(pageId) {
    window.location.href = 'post.php?post=' + pageId + '&action=edit';
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
QoobWordpressDriver.prototype.savePageData = function(pageId, data, cb) {
    jQuery(document).ready(function($) {
        if (ajax.logged_in && ajax.qoob == true) {
            var dataToSend = JSON.stringify({
                page_id: pageId,
                blocks: data
            });
            $.ajax({
                url: ajax.url + '?action=qoob_save_page_data',
                type: 'POST',
                data: dataToSend,
                processData: false,
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                success: function(response) {
                    cb(null, response.success);
                }
            });
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
QoobWordpressDriver.prototype.loadPageData = function(pageId, cb) {
    jQuery(document).ready(function($) {
        if (ajax.logged_in && ajax.qoob == true) {
            $.ajax({
                url: ajax.url,
                type: 'POST',
                data: {
                    action: 'qoob_load_page_data',
                    page_id: pageId,
                    lang: 'en'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        cb(null, response.data);
                    } else {
                        cb(response.success);
                    }
                }
            });
        }
    });
};

/**
 * Get fields template
 * 
 * @param {loadFieldsTmplCallback} cb - A callback to run.
 */
QoobWordpressDriver.prototype.loadQoobTemplates = function(cb) {
    jQuery(document).ready(function($) {
        if (ajax.logged_in && ajax.qoob == true) {
            $.ajax({
                url: ajax.url,
                type: 'POST',
                data: {
                    action: 'qoob_load_tmpl'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        cb(null, response.qoobTemplate);
                    } else {
                        cb(response.success);
                    }
                }
            });
        }
    });
};

/**
 * Callback for get qoob data
 * 
 * @callback loadQoobDataCallback
 */

/**
 * Get qoob libs
 * 
 * @param {loadQoobDataCallback} cb - A callback to run.
 */
QoobWordpressDriver.prototype.loadLibsInfo = function(cb) {
    jQuery(document).ready(function($) {
        if (ajax.logged_in && ajax.qoob == true) {
            $.ajax({
                url: ajax.url,
                type: 'POST',
                data: {
                    action: 'qoob_load_libs_info'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        cb(null, response.data);
                    } else {
                        cb(response.success);
                    }
                },
                error: function(xrh, error) {
                    //FIXME: 
                }
            });
        }
    });
};
