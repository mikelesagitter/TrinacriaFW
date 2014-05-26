<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

// Wrapper for Facebook API
class TrinacriaFB extends Facebook {
    const CALL_PHP = false;
    const CALL_JS = true;
    
    // *************************************************************************
    // FACEBOOK API CONSTANTS
    // *************************************************************************

    // -------------------------------------------------------------------------
    // Constants for Dialogs
    // -------------------------------------------------------------------------

    //
    // All Dialogs
    // https://developers.facebook.com/docs/reference/dialogs/
    //

    const DIALOG_FEED = 'feed';
    
    // * Required
    // Application identifier
    const DIALOG_APP_ID = 'app_id';
    
    // * Required
    // The URL to redirect to after the user clicks a button on the Dialog. 
    const DIALOG_REDIRECT_URI = 'redirect_uri';
    
    // Default is page
    // Display mode in which to render the Dialog.
    // Can be page, popup, iframe, touch, or wap.
    // If you specify iframe, you must have a valid access_token.
    // To get a valid access_token, please see the Authentication guide;
    const DIALOG_DISPLAY = 'display';

    // If this is set to true, the error code and error description
    // will be displayed in the event of an error. 
    const DIALOG_SHOW_ERROR = 'show_error';

    //
    // Feed Dialog
    // https://developers.facebook.com/docs/reference/dialogs/feed/
    //

    // The ID or username of the user posting the message.
    // If this is unspecified, it defaults to the current user.
    // If specified, it must be the ID of
    // the user or of a page that the user administers.
    const DIALOG_FEED_FROM = 'from';

    // The ID or username of the profile that this story will be published to.
    // If this is unspecified, it defaults to the the value of from.
    const DIALOG_FEED_TO = 'to';

    // The link attached to this post
    const DIALOG_FEED_LINK = 'link';

    // The URL of a picture attached to this post.
    // The picture must be at least 50px by 50px
    // and have a maximum aspect ratio of 3:1
    const DIALOG_FEED_PICTURE = 'picture';

    // The URL of a media file (e.g., a SWF or video file)
    // attached to this post.
    // If both source and picture are specified, only source is used.
    const DIALOG_FEED_SOURCE = 'source';

    // The name of the link attachment.
    const DIALOG_FEED_NAME = 'name';

    // The caption of the link (appears beneath the link name).
    const DIALOG_FEED_CAPTION = 'caption';

    // The description of the link (appears beneath the link caption).
    const DIALOG_FEED_DESCRIPTION = 'description';

    // A JSON object of key/value pairs which will appear
    // in the stream attachment beneath the description,
    // with each property on its own line.
    // Keys must be strings, and values can be either strings
    // or JSON objects with the keys text and href.
    const DIALOG_FEED_PROPERTIES = 'properties';

    // A JSON array of action links which will appear
    // next to the "Comment" and "Like" link under posts.
    // Each action link should be represented as
    // a JSON object with keys name and link.
    const DIALOG_FEED_ACTIONS = 'actions';

    // A text reference for the category of feed post.
    // This category is used in Facebook Insights to help you
    // measure the performance of different types of post
    const DIALOG_FEED_REF = 'ref';

    // Return Datas

    // The ID of the posted story, if the user chose to publish.
    const DIALOG_FEED_R_POST_ID = 'post_id';

    //
    // Requests Dialog
    // https://developers.facebook.com/docs/reference/dialogs/requests/
    //

    const DIALOG_REQUEST = 'apprequests';

    // * Required
    // The request the receiving user will see.
    // It appears as a question posed by the sending user.
    // The maximum length is 255 characters.
    const DIALOG_REQUEST_MESSAGE = 'message';

    // A user ID or username. This may or may not be a friend of the user.
    // If this is specified, the user will not have a choice of recipients.
    // If this is omitted, the user will see a friend selector and will
    // be able to select a maximum of 50 recipients.
    // (Due to URL length restrictions, the maximum number of recipients
    // is 25 in IE7 and also in IE8+ when using a non-iframe dialog.)
    const DIALOG_REQUEST_TO = 'to';

    // Optional, default is '', which shows a selector that includes the ability
    // for a user to browse all friends, but also filter to friends using
    // the application and friends not using the application.
    // Can also be all, app_users and app_non_users.
    // This controls what set of friends the user sees if a friend
    // selector is shown.
    // If all, app_users, or app_non_users is specified,
    // the user will only be able to see users in that list and will not
    // be able to filter to another list
    //  Additionally, an application can suggest custom filters as dictionaries
    // with a name key and a user_ids key, which respectively have values
    // that are a string and a list of user ids.
    // - name is the name of the custom filter that will show in the selector.
    // - user_ids is the list of friends to include, in the order
    // they are to appear.
    //
    // Example #1
    // [{name: 'Neighbors', user_ids: [1, 2, 3]}, {name: 'Other Set', user_ids: [4,5,6]}]
    // Example #2
    // ['app_users']
    //
    const DIALOG_REQUEST_FILTERS = 'filters';

    // A array of user IDs that will be excluded from the dialog, for example:
    // exclude_ids: [1, 2, 3]
    const DIALOG_REQUEST_EXCLUDE_IDS = 'exclude_ids';

    // If a user is excluded from the dialog,
    // the user will not show in the friend selector.
    const DIALOG_REQUEST_MAX_RECIPIENTS = 'max_recipients';

    // An integer that specifies the maximum number of friends that can
    // be chosen by the user in the friend selector.
    const DIALOG_REQUEST_DATA = 'data';

    // Optional, additional data you may pass for tracking.
    // This will be stored as part of the request objects created.
    // The maximum length is 255 characters.
    const DIALOG_REQUEST_TITLE = 'title';

    // Optional, the title for the friend selector dialog.
    // Maximum length is 50 characters.

    // Return Datas

    // The request Object ID.
    // To get the full request ID,
    // concatenate this with a UID from the to field: ‘<request_object_id>_<user_id>’
    const DIALOG_REQUEST_R_REQUEST = 'request';
    
    // An array of the recipient user IDs for the request that was created.
    const DIALOG_REQUEST_R_TO = 'to';

    //
    // /FACEBOOK API CONSTANTS
    //

    //
    // TODO: Comment buildJSDialogCall
    //
    static private function buildJSDialogCall($type, $params, $callback = '') {
        $js = 'FB.ui({method: \''.$type.'\'';

        foreach($params as $k => $v) {
            $js .= ',';
            
            if(is_bool($v)) {
                $js .= $k.': '.($v ? 'true': 'false');
            } else if(is_array($v)) {
                // type : $v[0]
                // content : $v[1]

                switch($v[0]) {
                    // value is JavaScript variable
                    // put it as it
                    case 'var':
                        $js .= $k.': '.$v[1];
                    break;

                    default:
                        $js .= $k.': "'.$v[1].'"';
                    break;
                }
            } else {
                $js .= $k.': "'.$v.'"';
            }
        }

        $js .= '}';

        if(!empty($callback)) {
            $js .= ',function(response){'.$callback.'(response);}';
        }
        
            
        return $js.');';
    }

    public function dialog($type, $params, $js = false,
        $jsCallback = '') {

        $r = null;

        $params[self::DIALOG_APP_ID] = FB_APP_ID;

        // Common parameters
        $p = array(
            //self::DIALOG_APP_ID,
            self::DIALOG_DISPLAY,
            self::DIALOG_REDIRECT_URI,
            self::DIALOG_SHOW_ERROR
        );

        switch($type) {
            case self::DIALOG_FEED:
                $p = array_merge($p, array(
                    self::DIALOG_FEED_FROM,
                    self::DIALOG_FEED_TO,
                    self::DIALOG_FEED_LINK,
                    self::DIALOG_FEED_PICTURE,
                    self::DIALOG_FEED_SOURCE,
                    self::DIALOG_FEED_NAME,
                    self::DIALOG_FEED_CAPTION,
                    self::DIALOG_FEED_DESCRIPTION,
                    self::DIALOG_FEED_PROPERTIES,
                    self::DIALOG_FEED_ACTIONS,
                    self::DIALOG_FEED_REF
                ));

                foreach($params as $k => $v) {
                    if(!in_array($k, $p)) {
                        unset($params[$k]);
                    }
                }

                if($js) {
                    $r = self::buildJSDialogCall($type, $params, $jsCallback);
                } else {
                    try {
                        $r = $this->api(
                            $this->getUser().'/feed',
                            'POST',
                            $params
                        );
                    } catch (FacebookApiException $e) {
                        TrinacriaLogs::log(sprintf(APP_LOGS, date('d-m-y')),
                            __FILE__, __LINE__, serialize($e));

                        if(DEBUG_MOD) exit();
                    }
                }
            break;

            case self::DIALOG_REQUEST:
                $p = array_merge($p, array(
                    self::DIALOG_REQUEST_MESSAGE,
                    self::DIALOG_REQUEST_TO,
                    self::DIALOG_REQUEST_FILTERS,
                    self::DIALOG_REQUEST_EXCLUDE_IDS,
                    self::DIALOG_REQUEST_MAX_RECIPIENTS,
                    self::DIALOG_REQUEST_DATA,
                    self::DIALOG_REQUEST_TITLE
                ));

                foreach($params as $k => $v) {
                    if(!in_array($k, $p)) {
                        unset($params[$k]);
                    }
                }

                if($js) {
                    $r = self::buildJSDialogCall($type, $params, $jsCallback);
                } else {
                    // TODO: build HTML for DIALOG_REQUEST
                }
            break;

            default:
            break;
        }

        return $r;
    }
    
    public function getSignedRequest() {
        return parent::getSignedRequest();
    }
}
?>
