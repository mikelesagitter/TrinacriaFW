<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

function debug($var, $type = 3) {
    if(!defined('DEBUG_MOD') || !DEBUG_MOD) {
        return;
    }

    echo '<pre>';
    switch($type) {
        case 1:
            echo $var;
            break;
        case 2:
            print_r($var);
            break;
        case 3:
            var_dump($var);
            break;
        default:
            echo $var;
    }
    echo '</pre>';
}

class TrinacriaUtils {
    const AJAX = true;
    const UA_MSIE = 'MSIE';
    const UA_FIREFOX = 'Firefox';
    const UA_CHROME = 'Chrome';
    const UA_SAFARI = 'Safari';
    const UA_OPERA = 'Opera';

    // http://fr.php.net/session_destroy (Example #1)
    static public function fullSessionDestroy() {
        if(session_id() === '') return;

        $_SESSION = array();

        if(ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']
            );
        }

        session_destroy();
    }

    // Function to redirect or to echo with json
    // 2010-06-21
    static public function redirect($a, $ajax = false) {
        while(ob_get_level() > 0) ob_end_clean();

        if($ajax) {
            echo json_encode($a);
            exit();
        } else {
            header('Location: '.$a);
            exit();
        }
    }

    static public function redirectOAuthDialog($facebook, $ajax = false) {
        while(ob_get_level() > 0) ob_end_clean();

        $redirectTo = $facebook->getLoginUrl(array(
            //'redirect_uri' => FB_APP_PAGE,
            'redirect_uri' => FB_CANVAS_URL.'secure/oauth.php',
            'scope' => FB_SCOPE
        ));

        if($ajax) {
            echo json_encode(array('redirect' => $redirectTo));
            exit();
        } else {
            // Gzip compression
            if(!ob_start('ob_gzhandler')) ob_start();
            echo '<!doctype html><html lang="fr-FR"><head>
<meta charset="utf-8">
<title>Redirecting</title>
<script type="text/javascript">//<![CDATA[
if(window.top!=window.self){window.top.location=\''.$redirectTo.'\';}
else{window.location=\''.$redirectTo.'\';}
//]]></script></head><body></body></html>';
            ob_end_flush();
            exit();
        }
    }

    static public function redirectTab($ajax = false) {
        while(ob_get_level() > 0) ob_end_clean();

         if($ajax) {
            echo json_encode(array('redirect' => FB_APP_PAGE));
            exit();
        } else {
            // Gzip compression
            if(!ob_start('ob_gzhandler')) ob_start();
            echo '<!doctype html><html lang="fr-FR"><head>
<meta charset="utf-8">
<title>Redirecting</title>
<script type="text/javascript">//<![CDATA[
if(window.top!=window.self){window.top.location=\''.FB_APP_PAGE.'\';}
else{window.location=\''.FB_APP_PAGE.'\';}
//]]></script></head><body></body></html>';
            ob_end_flush();
            exit();
        }
    }

    //
    // http://www.php.net/manual/en/function.get-browser.php#101125
    //
    static public function getBrowser() {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        //$bname = 'Unknown';
        $platform = 'Unknown';
        $version = null;
        $ub = null;

        // First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
            //$bname = 'Internet Explorer';
            $ub = self::UA_MSIE;
        }
        //*
        elseif(preg_match('/Firefox/i',$u_agent)) {
            //$bname = 'Mozilla Firefox';
            $ub = self::UA_FIREFOX;
        } elseif(preg_match('/Chrome/i',$u_agent)) {
            //$bname = 'Google Chrome';
            $ub = self::UA_CHROME;
        } elseif(preg_match('/Safari/i',$u_agent)) {
            //$bname = 'Apple Safari';
            $ub = self::UA_SAFARI;
        } elseif(preg_match('/Opera/i',$u_agent)) {
            //$bname = 'Opera';
            $ub = self::UA_OPERA;
        }
        /*
        elseif(preg_match('/Netscape/i',$u_agent)) {
            $bname = 'Netscape';
            $ub = 'Netscape';
        }
        //*/

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . implode('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,'Version') < strripos($u_agent,$ub)){
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        // check if we have a number
        if(empty($version)) {
            $version = '?';
        }

        return array(
            'userAgentFull' => $u_agent,
            'userAgent' => $ub,
            //'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

    static public function isAjax() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    static public function remove_magic_quotes_gpc(&$a) {
        $a = stripslashes($a);
    }

    static public function disableMagicQuotes() {
        // SdZ
        // http://www.siteduzero.com/tutoriel-3-88620-votre-site-php-presque-
        // complet-architecture-mvc-et-bonnes-pratiques.html
        if(get_magic_quotes_runtime()) set_magic_quotes_runtime(false);

        if(1 == get_magic_quotes_gpc()) {
            array_walk_recursive($_GET,'TrinacriaUtils::remove_magic_quotes_gpc');
            array_walk_recursive($_POST,'TrinacriaUtils::remove_magic_quotes_gpc');
            array_walk_recursive($_COOKIE,'TrinacriaUtils::remove_magic_quotes_gpc');
        }
    }

    static public function unregisterGlobals() {
        // http://www.php.net/manual/en/security.globals.php#85447
        //
        // Unregister_globals: unsets all global variables set from a
        // superglobal array
        // --------------------
        // This is useful if you don't know the configuration of PHP on the
        // server the application will be run
        // Place this in the first lines of all of your scripts
        // Don't forget that the register_global of $_SESSION is done after
        // session_start() so after each session_start() put a
        // unregister_globals('_SESSION');

        if (!ini_get('register_globals')) {
            return false;
        }

        foreach(func_get_args() as $name) {
            foreach ($GLOBALS[$name] as $key=>$value) {
                if (isset($GLOBALS[$key])) {
                    unset($GLOBALS[$key]);
                }
            }
        }
    }

    // return uniqid without dot between id and time
    // 2010-02-04
    static public function uniqid() {
        // mUniqid()

        return str_ireplace('.', '', uniqid('', true));
    }

    public static function launchBrowserDetection() {
        if(!defined('APP_JS_JREJECT')) return;

        while(ob_get_level() > 0) ob_end_clean();

        // Gzip compression
        if(!ob_start('ob_gzhandler')) ob_start();
        echo '<!doctype html><html lang="fr-FR"><head>
<meta charset="utf-8">
<title>Browser Detection</title>
<link rel="stylesheet" href="'.CDN_CSS.'/jQuery/libs/jReject/jquery.reject.1.0.0a.css" />
</head>
<body id="browserDetection">
<script>'.APP_JS_JREJECT.'
jRejectOptions.imagePath = "'.CDN_CSS.'/jQuery/libs/jReject/images/";
jRejectOptions.close = false;
jRejectOptions.overlayBgColor = "#fff";
jRejectOptions.overlayOpacity = 1;
</script>
<script src="'.CDN_JS.'/jQuery/jquery.1.7.1.min.js"></script>
<script src="'.CDN_JS.'/jQuery/libs/jReject/jquery.reject.1.0.0a.js"></script>
<script>
$(function() {
    $.reject(jRejectOptions);
});
</script>
</body>
</html>';
        ob_end_flush();
        exit();
    }

    /**
     * Retourne un objet DateTime à partir d'un timestamp
     * A utiliser si PHP < 5.3.0
     *
     * @param int $timestamp
     * @return DateTime
     */
    static public function createDateTime($timestamp = 0) {
        //debug(__METHOD__);
        //debug('$timestamp');
        //debug($timestamp);

        $d = new DateTime();

        if(
            PHP_MAJOR_VERSION >= 5
            && PHP_MINOR_VERSION >= 3
            && PHP_RELEASE_VERSION >= 0
        ) {
            $d->setTimestamp($timestamp);
        } else {
            $d->setDate(
                date('Y',$timestamp),
                date('n',$timestamp),//date('m',$timestamp),
                date('j',$timestamp)//date('d',$timestamp)
            );

            $d->setTime(
                date('G',$timestamp),//date('H',$timestamp),
                date('i',$timestamp),
                date('s',$timestamp)
            );
        }

        //debug('$d');
        //debug($d);
        //debug('$d->format(TrinacriaDB::MYSQL_DATE_TIMESTAMP)');
        //debug($d->format(TrinacriaDB::MYSQL_DATE_TIMESTAMP));

        return $d;
    }

    /**
     * Retourne un objet DateTime à partir d'une date/heure formartée
     * A utiliser si PHP < 5.3.0
     *
     * @param int $timestamp
     * @return DateTime
     */
    static public function createDateTimeFromFormat($format, $time) {
        //debug(__METHOD__);
        if(
            PHP_MAJOR_VERSION >= 5
            && PHP_MINOR_VERSION >= 3
            && PHP_RELEASE_VERSION >= 0
        ) {
            //debug('PHP >= 5.3.0');
            return DateTime::createFromFormat($format, $time);
        } else {
            //debug('PHP < 5.3.0');
            //debug('strtotime($time)');
            //debug(strtotime($time));
            return self::createDateTime(strtotime($time));
        }
    }

    /**
     * Retourne un objet DateTime à partir d'une date/heure formartée
     * A utiliser si PHP < 5.3.0
     *
     * @param int $timestamp
     * @return DateTime
     */
    static public function dateGetTimestamp($oDateTime) {
        if(
            PHP_MAJOR_VERSION >= 5
            && PHP_MINOR_VERSION >= 3
            && PHP_RELEASE_VERSION >= 0
        ) {
            return $oDateTime->getTimestamp();
        } else {
            return mktime(
                $oDateTime->format('H'),
                $oDateTime->format('i'),
                $oDateTime->format('s'),
                $oDateTime->format('n'),
                $oDateTime->format('j'),
                $oDateTime->format('Y')
            );
        }
    }

    static public function isIE() {
        $d = self::getBrowser();
        return ($d['userAgent'] == 'MSIE');
    }

    /**
     *
     * @param string $file
     */
    static public function forceDownloadFile($file){
        $isIE = (isset($_SERVER['HTTP_USER_AGENT']) &&
            (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false));

        $fileName = pathinfo($file, PATHINFO_BASENAME);
        $fileSize = filesize($file);

        while(ob_get_level() > 0) ob_end_clean();

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');

        if($isIE) {
            header('Content-Disposition: attachment; filename='.$fileName
                .'; modification-date="'.date('r').'";');
        } else {
            header('Content-Disposition: attachment; filename="'.$fileName
                .'"; modification-date="'.date('r').'";');
        }

        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.sprintf('%d', $fileSize));
        header('Expires: 0');

        if($isIE) {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        } else {
            header('Pragma: no-cache');
        }

        flush();
        readfile($file);
        exit();
    }
}
?>
