/**
 * Class for work with ajax methods
 *  
 * @version 0.0.1
 * @class  WordpressDriver
 */
//module.exports.WordpressDriver = WordpressDriver;
function WordpressDriver() {
}

/**
 * Get url iframe
 * 
 * @returns {String}
 */
WordpressDriver.prototype.getIframePageUrl = function (pageId) {
    var postId = jQuery('#post_ID').val();
    return '/?page_id=' + postId + '&qoob=true';
};

/**
 * Go to the admin view of the edited page
 * 
 * @returns {String}
 */
WordpressDriver.prototype.exit = function (pageId) {
    if (!jQuery('.autosave input').prop("checked")) {
        var alert_exit = confirm("Are you sure you want to exit without save?");
        if (!alert_exit) {
            return false;
        }
    }

    var url = '/wp-admin/post.php?post=' + jQuery('#post_ID').val() + '&action=edit';
    window.location.href = url;
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
WordpressDriver.prototype.savePageData = function (pageId, data, cb) {
    jQuery(document).ready(function ($) {
        if (ajax.logged_in && ajax.qoob == true) {
            $.ajax({
                url: ajax.url,
                type: 'POST',
                data: {
                    action: 'save_page_data',
                    page_id: pageId,
                    blocks: data
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        cb(null, response.success);
                    } else {
                        cb(response);
                    }
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
WordpressDriver.prototype.loadPageData = function (pageId, cb) {
    jQuery(document).ready(function ($) {
        if (ajax.logged_in && ajax.qoob == true) {
            $.ajax({
                url: ajax.url,
                type: 'POST',
                data: {
                    action: 'load_page_data',
                    page_id: pageId,
                    lang: 'en'
                },
                dataType: 'json',
                success: function (response) {
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
WordpressDriver.prototype.loadBuilderTmpl = function (cb) {
    jQuery(document).ready(function ($) {
        if (ajax.logged_in && ajax.qoob == true) {
            $.ajax({
                url: ajax.url,
                type: 'POST',
                data: {
                    action: 'load_builder_tmpl'
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        cb(null, response.buildertemplate);
                    } else {
                        cb(response.success);
                    }
                }
            });
        }
    });
};

/**
 * Callback for get builder data
 * 
 * @callback loadBuilderDataCallback
 */

/**
 * Get builder data
 * 
 * @param {loadBuilderDataCallback} cb - A callback to run.
 */
WordpressDriver.prototype.loadBuilderData = function (cb) {
    jQuery(document).ready(function ($) {
        if (ajax.logged_in && ajax.qoob == true) {
            $.ajax({
                url: ajax.url,
                type: 'POST',
                data: {
                    action: 'load_builder_data'
                },
                dataType: 'json',
                success: function (response) {
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
 * Callback for get page data
 * 
 * @callback loadTemplateCallback
 */

/**
 * Get template for Id
 * 
 * @param {integer} templateId
 * @param {loadTemplateCallback} cb - A callback to run.
 */
WordpressDriver.prototype.loadItem = function (itemId, cb) {
        jQuery(document).ready(function ($) {
        if (ajax.logged_in && ajax.qoob == true) {
            $.ajax({
                url: ajax.url,
                type: 'POST',
                data: {
                    action: 'load_item',
                    item_id: itemId
                },
                cache: false,
                dataType: 'html',
                success: function (template) {
                    if (template != '') {
                        cb(null, template);
                    } else {
                        cb(false);
                    }
                }
            });
        }
    });
};

/**
 * Callback for get settings block for Id
 * 
 * @callback loadSettingsCallback
 */

/**
 * Get settings block for Id
 * 
 * @param {integer} templateId
 * @param {loadSettingsCallback} cb - A callback to run.
 */
WordpressDriver.prototype.loadSettings = function (templateId, cb) {
    jQuery(document).ready(function ($) {
        if (ajax.logged_in && ajax.qoob == true) {
            $.ajax({
                url: ajax.url,
                type: 'POST',
                data: {
                    action: 'load_settings',
                    template_id: templateId
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        cb(null, response.config);
                    } else {
                        cb(response.success);
                    }
                }
            });
        }
    });
};
