<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

class TrinacriaAppEngine {
    const REQUEST_IDS = 'request_ids';
    const SAVE_SESSION = true;

    const USER_ENGINE = false;
    const ADMIN_ENGINE = true;

    const FROM_API = 10;
    const FROM_SIGNED_REQUEST = 20;

    const TABLE_OAUTH_ERRORS = 'oauth_errors';
    const OAUTH_ERRORS_FIELD_ID = 'error_id';
    const OAUTH_ERRORS_FIELD_DATE = 'error_date';
    const OAUTH_ERRORS_FIELD_ERROR = 'error_error';
    const OAUTH_ERRORS_FIELD_ERROR_REASON = 'error_reason';
    const OAUTH_ERRORS_FIELD_ERROR_DESCRIPTION = 'error_description';

    const ORIGIN_OAUTH_PROCESS = 10;

    const MSG_CLEAN = true;
    const MSG_ERROR = 0;
    const MSG_SUCCESS = 1;
    const MSG_NOTICE = 2;
    const MSG_WARNING = 3;


    /**
     * Sortie du contenu en HTML (page HTML complète
     * avec header / footer)
     *
     * @since 1.0.0
     */
    const OUTPUT_HTML = 10;

    /**
     * Sortie du contenu en JSON
     *
     * @since 1.0.0
     */
    const OUTPUT_JSON = 20;

    /**
     * Sortie du contenu en HTML (uniquement le "body",
     * sans header / footer)
     *
     * @since 1.0.0
     */
    const OUTPUT_HTML_STRIPPED = 30;

    // obj
    private $user = null;
    // array
    private $elements = null;
    // str
    private $origin = null;
    // array
    private $msg = null;

    private function __construct() {
        $this->user = null;
        $this->elements = array();
        $this->msg = array(
            self::MSG_ERROR => array(),
            self::MSG_SUCCESS => array(),
            self::MSG_NOTICE => array(),
            self::MSG_WARNING => array()
        );
        $this->origin = null;
    }

    static public function startEngine($type = self::USER_ENGINE) {
        // TODO: Manage Facebook Requests

        // - Will fine tune cookies to avoid any risk of crushing with
        // other applications using this framework
        // - Then, will start session
        // - Then, will clean up magic_quotes, register_global
        if(!defined('CONFIG_COOKIES')) {
            throw new Exception(__METHOD__.
                ' : Need CONFIG_COOKIES ; see config file !');
        } else {
            $c = unserialize(CONFIG_COOKIES);

            if(!isset($c['lifetime'])) {
                throw new Exception(__METHOD__.' :: need "lifetime" key');
            }
            if(!isset($c['name'])) {
                throw new Exception(__METHOD__.' :: need "name" key');
            }

            if(!isset($c['path'])) {
                throw new Exception(__METHOD__.' :: need "path" key');
            }

            if(!isset($c['domain'])) {
                throw new Exception(__METHOD__.' :: need "domain" key');
            }

            if(!isset($c['secure'])) {
                throw new Exception(__METHOD__.' :: need "secure" key');
            }

            if(!isset($c['httponly'])) {
                throw new Exception(__METHOD__.' :: need "httponly" key');
            }

            // Disable and Clean effect of magic quotes
            TrinacriaUtils::disableMagicQuotes();

            // Config cookie and Initialize Session
            self::initializeSession();

            // Clean effect of REGISTER_GLOBAL
            TrinacriaUtils::unregisterGlobals('_SESSION','_POST','_GET','_COOKIE',
                '_REQUEST','_SERVER','_ENV','_FILES');

            if(TrinacriaUtils::isIE()) {
                header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
            }

            $k = APP_SESSIONS_KEY;

            // Engine for User
            if(self::USER_ENGINE === $type) {
                if(!empty($_SESSION[$k])) {
                    // If there is already datas in session
                    // we check if user come from OAuth redirection
                    $app = $_SESSION[$k];

                    switch($app->getOrigin()) {
                        case self::ORIGIN_OAUTH_PROCESS:
                            // DO NOTHING ;
                            // don't erase session
                        break;

                        default:
                            // User come from other way ;
                            // if a signed_request have been posted,
                            // the user has clicked on facebook application tab
                            //
                            // User haven't to click on tab, except at the begining
                            // So, we purge & recreate the session

                            if(!empty($_POST['signed_request'])) {
                                TrinacriaUtils::fullSessionDestroy();
                                self::initializeSession();

                                $_SESSION[$k] = new TrinacriaAppEngine();
                            }
                        break;
                    }
                } else {
                    $_SESSION[$k] = new TrinacriaAppEngine();
                }

                // Will redirect to
                $_SESSION[$k]->appToTab();
            } else {
                // Engine for ADMIN
                $k .= '_admin';

                if(empty($_SESSION[$k])) $_SESSION[$k] = new TrinacriaAppEngine();
            }

            return $_SESSION[$k];
        }
    }

    static private function initializeSession() {
        $c = unserialize(CONFIG_COOKIES);

        // Config cookie
        session_name($c['name']);

        $c['lifetime'] = intval($c['lifetime']);

        session_set_cookie_params($c['lifetime'], $c['path'], $c['domain'],
            $c['secure'], $c['httponly']);

        session_start();
        setcookie(session_name(),session_id(),time()+$c['lifetime']);
    }

    public function initJS($facebook = null, $user = null) {
        // - Define global javascript variable
        // - Call Facebook JS SDK
        // - Create default behavior when like/unlike page
        //
        // In order to modify / erase this,
        // put content in "design/js/init.php"
        echo '<script>
//<![CDATA[
var FB_APP_PAGE = "'.FB_APP_PAGE.'";
var FB_APP_ID = "'.FB_APP_ID.'";
var FB_PAGE_ID = "'.FB_PAGE_ID.'";
var FB_CANVAS_URL = "'.FB_CANVAS_URL.'";
var CDN_CSS = "'.CDN_CSS.'";
var CDN_JS = "'.CDN_JS.'";
var PICTO_URL = "'.PICTO_URL.'";
var IMAGES_URL = "'.IMAGES_URL.'";

var userStatus = "'.$this->user->statusToString().'";
var likePage = '.($this->user->likePage() ? 'true':'false').';
var needLike = '.(FB_USER_NEED_LIKE ? 'true':'false').';

var redirectTo = function(to){
    if(window.top!=window.self) window.top.location=to;
    else window.location=to;
};

'.APP_JS_JREJECT.'
jRejectOptions.imagePath = CDN_CSS+"jQuery/libs/jReject/images/";
jRejectOptions.close = false;
jRejectOptions.overlayBgColor = "#fff";
jRejectOptions.overlayOpacity = 1;

var FBEdgeCreateCallback = function(d){
    redirectTo(FB_APP_PAGE);
};

var FBEdgeRemoveCallback = function(d){
    redirectTo(FB_APP_PAGE);
};';

if(!empty($facebook) && !empty($user)) {
    echo 'var FBRedirectToLogin = function() {
        redirectTo("'.$this->getFacebookLoginUrl($facebook, FB_SCOPE).'");
    };';
}

if(is_file('design/js/init.php') && is_readable('design/js/init.php')) {
    require 'design/js/init.php';
}

echo "\n".'$(document).ready(function() {
    $("body").append(\'<div id="fb-root"></div>\');

    window.fbAsyncInit = function(){
        FB.init({
            appId: FB_APP_ID,
            cookie: true,
            status: true,
            xfbml: true,
            channelUrl: CDN_JS + "Facebook/channel.php"
        });

        FB.Event.subscribe("edge.create",function(response){
            FBEdgeCreateCallback(response);
        });

        FB.Event.subscribe("edge.remove",function(response){
            FBEdgeRemoveCallback(response);
        });

        FB.Canvas.setAutoGrow();
    };

    $.getScript(document.location.protocol + "//connect.facebook.net/fr_FR/all.js");

    $.reject(jRejectOptions);
});//]]></script>';
    }

    static public function errorPage($f, $l, $m, $msg) {
        while(ob_get_level() > 0) ob_end_clean();
        // Gzip compression
        if(!ob_start('ob_gzhandler')) ob_start();
?>
<!doctype html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="fr-FR"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8" lang="fr-FR"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9" lang="fr-FR"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" lang="fr-FR"><!--<![endif]-->
<head>
<meta charset="utf-8" />
<title>ERROR</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
</head>
<body>
<div id="top">
<div class="alert alert-error">
    <h1>Une erreur est survenue</h1>
    <h2>Détails :</h2>
    <div class="msg"><?php echo $msg; ?></div>
    <?php if(DEBUG_MOD) { ?>
    <dl>
        <dt>File</dt>
        <dd><?php echo $f; ?></dd>

        <dt>Line</dt>
        <dd><?php echo $l; ?></dd>

        <dt>Méthode</dt>
        <dd><?php echo $m; ?></dd>
    </dl>
    <?php } ?>
</div>
</div>
</body>
</html>
<?
        ob_end_flush();
        exit();
    }

    // Test for redirection if app is tabbed
    private function appToTab() {
        if(APPLICATION_TYPE === false && !empty($_SERVER['HTTP_REFERER'])) {
            // true if user go on apps.facebook.com/APP_ID
            // via notification (apprequests dialog)

            if(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)
                == 'apps.facebook.com') {

                if(!empty($_REQUEST['request_ids'])) {
                    $this->set(REQUEST_IDS, $_REQUEST['request_ids']);
                } else {
                    TrinacriaUtils::fullSessionDestroy();
                }

                TrinacriaUtils::redirectTab();
                exit();
            }
        }
    }

    public function getFacebook() {
        return (new TrinacriaFB(array(
            'appId'  => FB_APP_ID,
            'secret' => FB_SECRET_ID
        )));
    }

    // Store variable
    public function set($k,$v) {
        $this->elements[$k] = $v;
    }

    // Get variable
    public function get($k, $defaultValue = null) {
        return isset($this->elements[$k]) ? $this->elements[$k] : $defaultValue;
    }

    // Delete variable
    public function delete($k) {
        unset($this->elements[$k]);
    }

    public function createUser($user) {
        $this->user = $user;
        return $this->user;
    }

    public function getUser() {
        return $this->user;
    }

    public function setOrigin($a) {
        $this->origin = $a;
    }

    public function getOrigin() {
        return $this->origin;
    }

    static public function getFacebookLoginUrl($facebook, $scope) {
        return $facebook->getLoginUrl(array(
            'scope' => $scope,
            'redirect_uri' => FB_CANVAS_URL.'secure/oauth.php'
        ));
    }

    static public function addOAuthError($db, $error, $reason, $description) {
        $q = $db->prepare('INSERT INTO '.self::TABLE_OAUTH_ERRORS
            .'('
                .self::OAUTH_ERRORS_FIELD_DATE.','
                .self::OAUTH_ERRORS_FIELD_ERROR.','
                .self::OAUTH_ERRORS_FIELD_ERROR_REASON.','
                .self::OAUTH_ERRORS_FIELD_ERROR_DESCRIPTION
            .') VALUES (:date,:error,:reason,:description)'
        );

        $d = new DateTime();

        $q->bindValue(':date', $d->format(TrinacriaDb::MYSQL_DATE_TIMESTAMP), PDO::PARAM_STR);
        $q->bindValue(':error', $error, PDO::PARAM_STR);
        $q->bindValue(':reason', $reason, PDO::PARAM_STR);
        $q->bindValue(':description', $description, PDO::PARAM_STR);

        $result = $q->execute();

        if(!$result) {
            TrinacriaLogs::logSQL(sprintf(APP_LOGS, date('d-m-y')),
                __FILE__, __LINE__, $q, $db);

            if(DEBUG_MOD) exit();
        }

        $q = null;
        return $result;
    }

    static public function getLikeStatus($facebook,
            $method = self::FROM_SIGNED_REQUEST) {

        $d = false;

        if(self::FROM_SIGNED_REQUEST === $method) {
            $signedRequest = $facebook->getSignedRequest();
            $d = $signedRequest['page']['liked'];
        } else {
            try {
                $r = $facebook->api(
                    $facebook->getUser().'/feed',
                    'POST',
                    $params
                );
            } catch (FacebookApiException $e) {
                TrinacriaLogs::log(sprintf(APP_LOGS, date('d-m-y')),
                    __FILE__, __LINE__, serialize($e));

                if(DEBUG_MOD) exit();
            }
        }

        return $d;
    }



    private function addMsg($type, $msg) {
        switch($type) {
            case self::MSG_ERROR:
            case self::MSG_SUCCESS:
            case self::MSG_NOTICE:
            case self::MSG_WARNING:
                $this->msg[$type][] = $msg;
            break;

            default:
                TrinacriaLogs::log(sprintf(APP_LOGS, date('d-m-y')),
                    __FILE__, __LINE__, 'UNSUPPORTED MESSAGE TYPE');

                if(DEBUG_MOD) exit();
            break;
        }
    }

    private function getMsg($type) {
        switch($type) {
            case self::MSG_ERROR:
            case self::MSG_SUCCESS:
            case self::MSG_NOTICE:
            case self::MSG_WARNING:
                return $this->msg[$type];
            break;

            default:
                TrinacriaLogs::log(sprintf(APP_LOGS, date('d-m-y')),
                    __FILE__, __LINE__, 'UNSUPPORTED MESSAGE TYPE');

                if(DEBUG_MOD) exit();
            break;
        }
    }

    private function cleanMsg($type) {
        switch($type) {
            case self::MSG_ERROR:
            case self::MSG_SUCCESS:
            case self::MSG_NOTICE:
            case self::MSG_WARNING:
                $this->msg[$type] = array();
            break;

            default:
                TrinacriaLogs::log(sprintf(APP_LOGS, date('d-m-y')),
                    __FILE__, __LINE__, 'UNSUPPORTED MESSAGE TYPE');

                if(DEBUG_MOD) exit();
            break;
        }
    }

    private function outputMsg($type, $clean = false){
        switch($type) {
            case self::MSG_ERROR:
                if(count($this->msg[$type]) == 0) return;

                // CSS from Twitter Bootstrap
                foreach($this->msg[$type] as $msg) {
                    echo '<div class="alert alert-error">'.$msg.'</div>';
                }

                if($clean) $this->cleanErrors();
            break;
            case self::MSG_SUCCESS:
                if(count($this->msg[$type]) == 0) return;

                // CSS from Twitter Bootstrap

                foreach($this->msg[$type] as $msg) {
                    echo '<div class="alert alert-success">'.$msg.'</div>';
                }

                if($clean) $this->cleanSuccess();
            break;
            case self::MSG_NOTICE:
                if(count($this->msg[$type]) == 0) return;

                // CSS from Twitter Bootstrap

                foreach($this->msg[$type] as $msg) {
                    echo '<div class="alert alert-info">'.$msg.'</div>';
                }

                if($clean) $this->cleanNotices();
            break;
            case self::MSG_WARNING:
                if(count($this->msg[$type]) == 0) return;

                // CSS from Twitter Bootstrap

                foreach($this->msg[$type] as $msg) {
                    echo '<div class="alert">'.$msg.'</div>';
                }

                if($clean) $this->cleanWarnings();
            break;

            default:
                TrinacriaLogs::log(sprintf(APP_LOGS, date('d-m-y')),
                    __FILE__, __LINE__, 'UNSUPPORTED MESSAGE TYPE');

                if(DEBUG_MOD) exit();
            break;
        }
    }

    public function addError($msg) {
        $this->addMsg(self::MSG_ERROR, $msg);
    }

    public function getErrors() {
        return $this->getMsg(self::MSG_ERROR);
    }

    public function cleanErrors() {
        $this->cleanMsg(self::MSG_ERROR);
    }

    public function outputErrors($clean = false) {
        $this->outputMsg(self::MSG_ERROR, $clean);
    }

    public function addSuccess($msg) {
        $this->addMsg(self::MSG_SUCCESS, $msg);
    }

    public function getSuccess() {
        return $this->getMsg(self::MSG_SUCCESS);
    }

    public function cleanSuccess() {
        $this->cleanMsg(self::MSG_SUCCESS);
    }

    public function outputSuccess($clean = false) {
        $this->outputMsg(self::MSG_SUCCESS, $clean);
    }

    public function addNotice($msg) {
        $this->addMsg(self::MSG_NOTICE, $msg);
    }

    public function getNotice() {
        return $this->getMsg(self::MSG_NOTICE);
    }

    public function cleanNotices() {
        $this->cleanMsg(self::MSG_NOTICE);
    }

    public function outputNotices($clean = false) {
        $this->outputMsg(self::MSG_NOTICE, $clean);
    }

    public function addWarning($msg) {
        $this->addMsg(self::MSG_WARNING, $msg);
    }

    public function getWarning() {
        return $this->getMsg(self::MSG_WARNING);
    }

    public function cleanWarnings() {
        $this->cleanMsg(self::MSG_WARNING);
    }

    public function outputWarnings($clean = false) {
        $this->outputMsg(self::MSG_WARNING, $clean);
    }


    // Used for write message ON DEMANDE
    static public function writeMessage($type, $msg){
        switch($type) {
            case self::MSG_ERROR:
                echo '<div class="alert alert-error">'.$msg.'</div>';
            break;
            case self::MSG_SUCCESS:
                echo '<div class="alert alert-success">'.$msg.'</div>';
            break;
            case self::MSG_NOTICE:
                echo '<div class="alert alert-info">'.$msg.'</div>';
            break;
            case self::MSG_WARNING:
                echo '<div class="alert">'.$msg.'</div>';
            break;

            default:
                TrinacriaLogs::log(sprintf(APP_LOGS, date('d-m-y')),
                    __FILE__, __LINE__, 'UNSUPPORTED MESSAGE TYPE');

                if(DEBUG_MOD) exit();
            break;
        }
    }

    static public function writeSucces($msg) {
        self::writeMessage(self::MSG_SUCCESS, $msg);
    }

    static public function writeError($msg) {
        self::writeMessage(self::MSG_ERROR, $msg);
    }
}
?>
