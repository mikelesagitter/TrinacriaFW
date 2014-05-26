<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

/**
 * @class TrinacriaUser
 * Utilisateur "BASIQUE"
 *
 * Contient différentes informations concerant l'utilisateur,
 *
 * @since 1.0.0
 */
class TrinacriaUser {
    /**
     * Table où sont enregistrés les utilisateurs
     *
     * @since 1.0.0
     */
    const TABLE = 'users';

    /**
     * Champ : ID Facebook
     *
     * @since 1.0.0
     */
    const FIELD_FBUID = 'uid_facebook';
    /**
     * Champ : Prénom
     *
     * @since 1.0.0
     */
    const FIELD_FIRSTNAME = 'firstname';
    /**
     * Champ : Nom
     *
     * @since 1.0.0
     */
    const FIELD_LASTNAME = 'lastname';
    /**
     * Champ : Adresse e-mail
     *
     * @since 1.0.0
     */
    const FIELD_EMAIL = 'email';
    /**
     * Champ : Date d'anniversaire
     *
     * @since 1.0.0
     */
    const FIELD_BIRTHDAY = 'birthday';
    /**
     * Champ : Date d'inscription à l'application
     *
     * @since 1.0.0
     */
    const FIELD_REGISTER = 'date_register';
    /**
     * Champ : Date de la dernière connexion à l'application
     *
     * @since 1.0.0
     */
    const FIELD_UPDATE = 'date_last_login';
    /**
     * Champ : Photo de l'utilisateur
     *
     * Stockage sha1 de l'url de stockage de la photo sur les serveurs
     * Facebook.
     * Utilisé uniquement à titre indicatif
     *
     * @since 1.0.0
     */
    const FIELD_PICTURE_FILE = 'picture';

    /**
     * Champ : Date de mise à jour de la photo utilisateur
     *
     * @since 1.0.0
     */
    const FIELD_PICTURE_UPDATE = 'picture_update';

    /**
     * Status utilisateur : inconnu
     * On ne sait pas s'il a autorisé l'application ou non
     *
     * @since 1.0.0
     */
    const STATUS_UNKNOWN = 10;

    /**
     * Status utilisateur : connecté
     * L'utilisateur a autorisé l'application
     *
     * @since 1.0.0
     */
    const STATUS_CONNECTED = 20;

    /**
     * Status utilisateur : non connecté
     * L'utilisateur n'a pas autorisé l'application
     *
     * @since 1.0.0
     */
    const STATUS_NOT_CONNECTED = 30;

    /**
     * Flag d'enregistrement en BDD : nouvel utilisateur.
     * Renvoyé par store() après enregistrement en base
     *
     * @since 1.0.0
     * @see TrinacriaUser::store()
     */
    const USER_NEW = 50;

    /**
     * Flag d'enregistrement en BDD : utilisateur existant.
     * Renvoyé par store() après enregistrement en base
     *
     * @since 1.0.0
     * @see TrinacriaUser::store()
     */
    const USER_EXISTING = 60;

    /**
     * Flag indiquant que l'on ne veut pas enregistrer
     * l'utilisateur en base de données
     *
     * @since 1.0.0
     * @see TrinacriaUser::createFromFacebook
     */
    const DO_NOT_STORE = false;

    /**
     * Flag indiquant que l'on veut enregistrer
     * l'utilisateur en base de données
     *
     * @since 1.0.0
     * @see TrinacriaUser::createFromFacebook
     */
    const STORE = true;

    const GET_FRIENDS_NAME = 10;
    const GET_FRIENDS_ID = 20;
    const GET_FRIENDS_BOTH = 30;

    const NO_PICTURE = 10;

    //
    // Properties
    //

    /**
     * Est-ce que l'utilisateur aime la page ?
     *
     * @since 1.0.0
     */
    protected $like = null;

    /**
     * Est-ce que l'utilisateur veut jouer ?
     *
     * @since 1.0.0
     */
    protected $play = false;

    /**
     * Est-ce que l'utilisateur veut se connecter ?
     *
     * @since 1.0.0
     */
    protected $login = false;

    /**
     * Status de l'utilisateur
     *
     * @since 1.0.0
     * @see TrinacriaUser::STATUS_UNKNOWN
     * @see TrinacriaUser::STATUS_NOT_CONNECTED
     * @see TrinacriaUser::STATUS_CONNECTED
     */
    protected $OAuthStatus = self::STATUS_UNKNOWN;

    /**
     * ID Facebook
     * String
     *
     * @since 1.0.0
     */
    protected $id = 0;

    /**
     * Prénom
     * String
     *
     * @since 1.0.0
     */
    protected $firstname = '';

    /**
     * Nom de famille
     * String
     *
     * @since 1.0.0
     */
    protected $lastname = '';

    /**
     * Adresse e-mail
     * String
     *
     * @since 1.0.0
     */
    protected $email = '';

    /**
     * Date d'anniversaire
     * DateTime
     *
     * @since 1.0.0
     */
    protected $birthday = null;

    /**
     * Date d'inscription
     * DateTime
     *
     * @since 1.0.0
     */
    protected $register = null;
    // TODO
    protected $picture = null;

    //
    // Methodes
    //

    /**
     * @since 1.0.0
     *
     * @param string $id
     * ID Facebook
     *
     * @param string $firstname
     * Prénom
     *
     * @param string $lastname
     * Nom de famille
     *
     * @param string $email
     * Adresse e-email
     *
     * @param DateTime $birthday
     * Date d'anniversaire
     *
     * @param DateTime $register
     * Date d'inscription
     *
     * @param $picture
     * TODO
     */
    public function __construct($id, $firstname = '', $lastname = '',
        $email = '', $birthday = null, $register = null, $picture = null) {

        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->birthday = $birthday;
        $this->register = $register;
        $this->picture = $picture;

        // Mise à jour de l'ID via la méthode setID
        // pour mettre à jour le status également
        $this->setID($id);
    }

    /**
     * Mise à jour des données utilisateur via l'API Facebook
     *
     * @since 1.0.0
     *
     * @param TrinacriaFB $facebook
     * Instance facebook pour utiliser l'API
     *
     * @see TrinacriaFB
     */
    public function updateDatas($facebook) {
        // Récupération et mise à jour de l'ID Utilisateur
        $id = $facebook->getUser();
        $this->setID($id);

        // On ne peut utiliser l'API /me seulement si l'utilisateur
        // a autorisé l'application
        if(!empty($this->id)) {
            try {
                $me = $facebook->api('/me');

                $this->firstname = $me['first_name'];
                $this->lastname = $me['last_name'];
                $this->email = isset($me['email']) ? $me['email'] : '';
                $this->birthday = isset($me['birthday']) ?
                    new DateTime($me['birthday']) : null;
            } catch(FacebookApiException $e) {
                TrinacriaLogs::log(
                    sprintf(APP_LOGS, date('d-m-y')),
                    __FILE__,
                    __LINE__,
                    serialize(array(
                        $e->getType(),
                        $e->getMessage()
                    ))
                );

                // Affichage d'une page d'erreur
                TrinacriaAppEngine::errorPage(
                    __FILE__,
                    __LINE__,
                    __METHOD__,
                    'ERROR : Can\'t call /me'
                );
            }
        }
    }

    /**
     * Créer un TrinacriaUser à partir de l'API Facebook
     *
     * @since 1.0.0
     *
     * @param TrinacriaFB $facebook
     * Instance facebook pour utiliser l'API
     *
     * @param boolean $store
     * Flag pour indiquer si on doit ou non sauvegarder
     * les données utilisateurs en base.
     * Valeurs :
     * TrinacriaUser::STORE || TrinacriaUser::DO_NOT_STORE
     *
     * @see TrinacriaFB
     * @see TrinacriaUser::STORE
     * @see TrinacriaUser::DO_NOT_STORE
     *
     * @return TrinacriaUser
     */
    static public function createFromFacebook($facebook, $store) {
        // Récupération de l'ID Utilisateur
        $id = $facebook->getUser();

        // On ne peut utiliser l'API /me seulement si l'utilisateur
        // a autorisé l'application
        if(!empty($id)) {
            try {
                $me = $facebook->api('/me');
            } catch(FacebookApiException $e) {
                TrinacriaLogs::log(
                    sprintf(APP_LOGS, date('d-m-y')),
                    __FILE__,
                    __LINE__,
                    serialize(array(
                        $e->getType(),
                        $e->getMessage()
                    ))
                );

                // Affichage d'une page d'erreur
                TrinacriaAppEngine::errorPage(
                    __FILE__,
                    __LINE__,
                    __METHOD__,
                    'ERROR : Can\'t call /me'
                );
            }

            $u = new TrinacriaUser(
                $id,
                $me['first_name'],
                $me['last_name'],
                isset($me['email']) ? $me['email'] : '',
                isset($me['birthday']) ? new DateTime($me['birthday'])
                    : null
            );

            // Enregistrement en base si dérisé
            if($store == self::STORE) {
                $u->store($facebook);
            }
        } else {
            // Utilisateur vide si l'utilisateur n'a pas autorisé
            // l'application
            $u = new TrinacriaUser(0);
        }

        return $u;
    }

    /**
     * Est-ce que l'utilisateur aime la page ?
     *
     * @since 1.0.0
     *
     * @return true
     * @return false
     */
    public function likePage() {
        return $this->like;
    }

    /*
    public function wantPlay() {
        return $this->play;
    }

    public function waitLogin() {
        return $this->login;
    }
    */

    public function setLikePage($a) {
        $this->like = $a;
    }

    /*
    public function setWantPlay($a) {
        $this->play = $a;
    }

    public function setWaitLogin($a) {
        $this->login = $a;
    }
    */

    /**
     * Retourne l'identifiant de l'utilisateur
     *
     * @since 1.0.0
     *
     * @return 0 si l'utilisateur n'est pas connecté
     * @return String si l'utilisateur est connecté
     */
    public function getID() {
        return $this->id;
    }

    /**
     * Met à jour l'identifiant de l'utilisateur
     * et son status
     *
     * @since 1.0.0
     *
     * @param String $id
     * Identifiant de l'utilisateur (String)
     */
    public function setID($id) {
        $this->id = $id;

        if(empty($id)) {
            $this->OAuthStatus = self::STATUS_NOT_CONNECTED;
        } else {
            $this->OAuthStatus = self::STATUS_CONNECTED;
        }
    }

    /**
     * Est-ce que l'utilisateur est connecté ?
     *
     * @since 1.0.0
     *
     * @return true
     * @return false
     */
    public function isConnected() {
        return !empty($this->id);
    }

    /**
     * Retourne le prénom de l'utilisateur
     *
     * @since 1.0.0
     *
     * @return String
     */
    public function getFirstname() {
        return $this->firstname;
    }

    /**
     * Retourne le nom de l'utilisateur
     *
     * @since 1.0.0
     *
     * @return String
     */
    public function getLastname() {
        return $this->lastname;
    }

    /**
     * Retourne "Nom Prénom" de l'utilisateur
     *
     * @since 1.0.0
     *
     * @return String
     */
    public function getName() {
        return $this->firstname.' '.$this->lastname;
    }

    /**
     * Retourne l'email de l'utilisateur
     *
     * @since 1.0.0
     *
     * @return String
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Retourne le status de l'utilisateur en String
     * pour sortie en JavaScript
     *
     * @since 1.0.0
     *
     * @return String
     */
    public function statusToString() {
        $s = '';
        switch($this->OAuthStatus) {
            case self::STATUS_UNKNOWN:
                $s = 'unknown';
            break;

            case self::STATUS_CONNECTED:
                $s = 'connected';
            break;

            case self::STATUS_NOT_CONNECTED:
                $s = 'not_connected';
            break;
        }

        return $s;
    }

    /*
    public function getUnregisteredFriends($db, $friendsList) {
        $friendsIDs = array();
        $friends = array();
        if($friendsList !== null && !empty($friendsList['data'])) {
            foreach($friendsList['data'] as $friend) {
                $friendsIDs[] = $friend['id'];
            }

            $q = $db->query('SELECT uid_facebook FROM '.self::TABLE
                .' WHERE uid_facebook IN('.implode(',',$db->quote($friendsIDs))
                .');');

            if($q === false) {
                TrinacriaLogs::logSQL(sprintf(APP_LOGS, date('d-m-y')),
                    __FILE__, __LINE__, $q, $db);

                if(DEBUG_MOD) {
                    debug('File: '.__FILE__.'; Line: '.__LINE__);
                    debug($q->errorInfo());
                    debug($db->errorInfo());
                    exit();
                }
            } else {
                $datas = array();

                while(($data = $q->fetch(PDO::FETCH_NUM)) !== false) {
                    $datas[] = $data[0];
                }

                foreach($friendsList['data'] as $friend) {
                    if(!in_array($friend['id'], $datas)) {
                        $friends[] = $friend;
                    }
                }
            }

            $q->closeCursor();
        }

        return $friends;
    }
    //*/

    /*
    public function getNbRegisteredFriends($db) {
        $q = $db->prepare('SELECT friends FROM '.self::TABLE
            .' WHERE uid_facebook=:id;');

        $q->bindValue(':id', $this->id, PDO::PARAM_INT);

        if(!$q->execute()) {
            TrinacriaLogs::logSQL(sprintf(APP_LOGS, date('d-m-y')),
                __FILE__, __LINE__, $q, $db);

            if(DEBUG_MOD) {
                debug('File: '.__FILE__.'; Line: '.__LINE__);
                debug($q->errorInfo());
                debug($db->errorInfo());
                exit();
            }

            return 0;
        } else {
            $datas = $q->fetch(PDO::FETCH_NUM);
            return intval($datas[0]);
        }
    }
    //*/

    /**
     * Indique si l'utilisateur est déjà en base ou non
     *
     * @param TrinacriaDb $db
     * Instance de base de donnné
     *
     * @param string $id
     * ID de l'utilisateur (String)
     *
     * @see TrinacriaDb
     *
     * @since 1.0.0
     *
     * @return true
     * @return false
     */
    static protected function exist($db, $id) {
        $q = $db->prepare('SELECT COUNT(*) FROM '.self::TABLE
            .' WHERE uid_facebook=:id;');
        $q->bindValue(':id', $id, PDO::PARAM_STR);

        if(!$q->execute()) {
            TrinacriaLogs::logSQL(sprintf(APP_LOGS, date('d-m-y')),
                __FILE__, __LINE__, $q, $db);

            if(DEBUG_MOD) exit();

            return false;
        } else {
            $d = $q->fetch(PDO::FETCH_NUM);
            return ($d[0] == 1);
        }
    }

    /**
     * Sauvegarde les informations de l'utilisateur en base de données
     * ssi l'utilisateur à autorisé l'application
     *
     * @since 1.0.0
     *
     * @param TrinacriaFB $facebook
     * Instance TrinacriaFB pour communiquer avec l'API Facebook
     *
     * @param TrinacriaDb $db
     * Instance de base de données
     *
     * @see TrinacriaDb
     * @see TrinacriaFB
     *
     * @return TrinacriaUser::USER_NEW
     * @return TrinacriaUser::USER_EXISTING
     */
    public function store($facebook, $db = null) {
        //debug(__METHOD__);

        // L'utilisateur doit avoir autorisé l'application
        if(empty($this->id)) return false;

        // Si l'instance de BDD n'est pas fourni,
        // on en créer une
        if(null == $db) $db = new TrinacriaDb(DB);

        //
        // Récupération du champ "picture"
        //
        try {
            /*
            $d = $facebook->api('/'.$this->id.'?fields=picture');

            // Url de l'image
            // -> Url de la photo de profil de l'utilisateur
            // Cette dernière est stocké sur un des CDN de Facebook
            $pictureUrl = $d['picture'];
            // Découpage de l'url
            $pictureFragments = explode('/', $pictureUrl);
            // On génère un sha1 du nom de l'image.
            // On n'a pas besoin de son vrai nom (dont la longeur
            // varie)
            // L'empreinte fera 40 caractères hexadecimaux
            $picture = hash('sha1', end($pictureFragments), false);

            // Flag pour savoir si on va devoir télécharger l'image
            $downloadPicture = false;

            // Photo actuelle
            $currentPicture = '';

            unset($d);
            */

            // Si l'utilisateur existe déjà en BDD,
            // on compare sa dernière photo connue avec celle
            // donné par FB

            // Sinon, on télécharge la photo

            // Si l'utilisateur existe
            if(self::exist($db, $this->id)) {
                $r = self::USER_EXISTING;

                // Récupération de la photo en base
                /*
                $qCheck = $db->query('SELECT '
                    .self::FIELD_PICTURE_FILE
                    .' FROM '.self::TABLE
                    .' WHERE '
                    .self::FIELD_FBUID.'='.$db->quote($this->id));
                */

                // S'il y a un soucis,
                // en DEBUG on le verra
                // En prod, on "passera" sur l'erreur
                /*
                if(false === $qCheck) {
                    TrinacriaLogs::logSQL(sprintf(APP_LOGS, date('d-m-y')),
                        __FILE__, __LINE__, $q, $db);

                    if(DEBUG_MOD) exit();
                } else {*/
                    /*
                    $d = $qCheck->fetch(PDO::FETCH_NUM);

                    $currentPicture = $d[0];

                    $sql = '';

                    if($currentPicture != $picture) {
                        $sql = ','.self::FIELD_PICTURE_FILE.'=:picture,'
                        .self::FIELD_PICTURE_UPDATE.'=:picture_time';

                        $downloadPicture = true;
                    }
                    */

                    $q = $db->prepare('UPDATE '.self::TABLE.' SET '
                        .self::FIELD_FIRSTNAME.'=:firstname,'
                        .self::FIELD_LASTNAME.'=:lastname,'
                        .self::FIELD_EMAIL.'=:email,'
                        .self::FIELD_BIRTHDAY.'=:birthday'
                        //.$sql
                        .' WHERE '.self::FIELD_FBUID.'=:id;');

                    $dt = new DateTime();

                    /*
                    if($currentPicture != $picture) {
                        $q->bindValue(':picture', $picture, PDO::PARAM_STR);
                        $q->bindValue(':picture_time', $dt->format(
                            TrinacriaDb::MYSQL_DATE_TIMESTAMP), PDO::PARAM_STR);
                    }
                    */

                    //unset($d);
                    unset($dt);
                //}

                //$qCheck->closeCursor();
                //$qCheck = null;
            } else {
                $r = self::USER_NEW;

                // Prepare INSERT query
                $q = $db->prepare('INSERT INTO '.self::TABLE.'('
                    .self::FIELD_FBUID.','
                    .self::FIELD_FIRSTNAME.','
                    .self::FIELD_LASTNAME.','
                    .self::FIELD_EMAIL.','
                    .self::FIELD_BIRTHDAY.','
                    .self::FIELD_REGISTER
                    //.','.self::FIELD_PICTURE_FILE.','
                    //.self::FIELD_PICTURE_UPDATE
                    .') VALUES ('
                        .':id,:firstname,:lastname,:email,:birthday,:register'
                        //.',:picture,:picture_time'
                    .');'
                );

                $this->register = new DateTime();

                $q->bindValue(':register', $this->register->format(
                    TrinacriaDb::MYSQL_DATE_TIMESTAMP), PDO::PARAM_STR);

                /*
                $q->bindValue(':picture', $picture, PDO::PARAM_STR);
                $q->bindValue(':picture_time', $this->register->format(
                    TrinacriaDb::MYSQL_DATE_TIMESTAMP), PDO::PARAM_STR);

                $downloadPicture = true;
                */
            }

            // Bind des valeurs communes
            $q->bindValue(':id', $this->id, PDO::PARAM_INT);
            $q->bindValue(':firstname', $this->firstname, PDO::PARAM_STR);
            $q->bindValue(':lastname', $this->lastname, PDO::PARAM_STR);
            $q->bindValue(':email', $this->email, PDO::PARAM_STR);
            $q->bindValue(':birthday', $this->birthday->format(
                TrinacriaDb::MYSQL_DATE_DATE),
                PDO::PARAM_STR
            );

            //
            // Insert ou Update
            //
            if(false === $q->execute()) {
                TrinacriaLogs::logSQL(sprintf(APP_LOGS, date('d-m-y')),
                    __FILE__, __LINE__, $q, $db);

                if(DEBUG_MOD) exit();
            }
            /*
            else if($downloadPicture) {
                //
                // Téléchargement de la photo de l'utilisateur
                //

                $ch = curl_init();

                // On récupère la photo au format "Large"
                // Il n'existe pas, à l'heure actuelle, de photo
                // plus large
                //
                // https://developers.facebook.com/docs/reference/api/
                curl_setopt($ch, CURLOPT_URL,
                    TrinacriaFB::$DOMAIN_MAP['graph']
                    .$this->id.'/picture?type=large'
                );

                // Les urls génériques du Graph API
                // utilise des redirections vers les serveurs CDN
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

                // Réglages des TIME OUT
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);

                // Le nom du fichier sera l'ID de l'utilisateur
                // S'il existe, il sera écrasé
                $fp = fopen(PROFIL_PICTURE_DIR.$this->id, 'w');

                curl_setopt($ch, CURLOPT_FILE, $fp);

                $curlResult = curl_exec($ch);
                curl_close($ch);
                fclose($fp);

                if(false === $curlResult) {
                    TrinacriaLogs::logSQL(sprintf(APP_LOGS, date('d-m-y')),
                        __FILE__, __LINE__, $q, $db);

                    if(DEBUG_MOD)  exit();
                } else {
                    // Suppression des anciennes miniatures
                    // générées

                    require 'global.php';
                    // $photosSize is defined in global.php

                    // $v -> array(dimensions, image_type)
                    $f = '';
                    foreach($photosSize as $k => $v) {
                        $f = PROFIL_PICTURE_DIR
                            .TrinacriaImageTools::generateFileName(
                                $this->id.'_'.$v[0], $v[1])
                        ;

                        if(file_exists($f)) {
                            unlink($f);
                        }
                    }
                }
            }
            */
        } catch(FacebookApiException $e) {
            TrinacriaLogs::log(
                sprintf(APP_LOGS, date('d-m-y')),
                __FILE__,
                __LINE__,
                serialize(array(
                    $e->getType(),
                    $e->getMessage()
                ))
            );

            // Affichage d'une page d'erreur
            TrinacriaAppEngine::errorPage(
                __FILE__,
                __LINE__,
                __METHOD__,
                'ERROR : Can\'t get picture'
            );
        }

        return $r;
    }

    /**
     * Sauvegarde le status de l'utilisateur
     *
     * @since 1.0.0
     *
     * @param const $status
     * Nouveau status de l'utilisateur
     *
     * @see TrinacriaUser::STATUS_CONNECTED
     * @see TrinacriaUser::STATUS_NOT_CONNECTED
     * @see TrinacriaUser::STATUS_UNKNOWN
     */
    public function saveOAuthStatus($status) {
        $this->OAuthStatus = $status;
    }

    /**
     * Retourne le status de l'utilisateur
     *
     * @since 1.0.0
     *
     * @return TrinacriaUser::STATUS_CONNECTED
     * @return TrinacriaUser::STATUS_NOT_CONNECTED
     * @return TrinacriaUser::STATUS_UNKNOWN
     */
    public function getOAuthStatus() {
        return $this->OAuthStatus;
    }

    /**
     * Récupère la photo de l'utilisateur dans un format donné
     *
     * @param string $fbuid
     * Identifiant Facebook de l'utilisateur
     *
     * @param string $sd
     * Dimensions sources : small, normal, large, square
     *
     * @param array $td
     * Dimensions désirées
     *
     *     $td = array(<br />
     *         TrinacriaImageTools::WIDTH => Largeur en px,<br />
     *         TrinacriaImageTools::HEIGHT => Hauteur en px<br />
     *         TrinacriaImageTools::FORMAT => Format de sortie<br />
     *     )<br />
     *
     * @see TrinacriaImageTools
     *
     * @return TrinacriaUser::NO_PICTURE
     * @return array(fichier,date_modification_fichier)
     */
    static public function getPhoto($fbuid, $sd = 'normal', $td = array()) {
        // Si on ne veut pas redimensionner la photo,
        // on renvoie le format "source" directement
        if(empty($td)) {
            $img = '';

            switch($sd) {
                case 'small':
                case 'normal':
                case 'large':
                    $img = $sd;
                break;

                case 'square':
                default:
                    $img = 'square';
                break;
            }

            return TrinacriaFB::$DOMAIN_MAP['graph']
                .$fbuid.'/picture?return_ssl_resources=1&type='.$img;
        } else {
            // Génération d'une "miniature" si nécessaire
            $error = false;

            // Test et affichage des erreurs uniquement
            // en mod DEBUG
            if(DEBUG_MOD) {
                if(!is_array($td)) {
                    $error = true;
                    TrinacriaLogs::log(sprintf(APP_LOGS, date('d-m-y')),
                        __FILE__, __LINE__,
                        __METHOD__
                        .' ; $targetDimensions must be an array');
                }

                if(!array_key_exists(TrinacriaImageTools::WIDTH, $td)) {
                    $error = true;
                    TrinacriaLogs::log(sprintf(APP_LOGS, date('d-m-y')),
                        __FILE__, __LINE__,
                        __METHOD__
                        .' ; $targetDimensions need WIDTH');
                }

                if(!array_key_exists(TrinacriaImageTools::HEIGHT, $td)) {
                    $error = true;
                    TrinacriaLogs::log(sprintf(APP_LOGS, date('d-m-y')),
                        __FILE__, __LINE__,
                        __METHOD__.' ; $targetDimensions need HEIGHT');
                }

                if(!array_key_exists(TrinacriaImageTools::FORMAT, $td)) {
                    $error = true;
                    TrinacriaLogs::log(sprintf(APP_LOGS, date('d-m-y')),
                        __FILE__, __LINE__,
                        __METHOD__.' ; $targetDimensions need HEIGHT');
                }
            }

            // Génération du nom du fichier qui sera généré
            $f = TrinacriaImageTools::generateFileName(
                $fbuid.'_'.$td[TrinacriaImageTools::WIDTH]
                .'x'.$td[TrinacriaImageTools::HEIGHT],
                $td[TrinacriaImageTools::FORMAT]
            );

            // Si le fichier source existe bien
            if(file_exists(PROFIL_PICTURE_DIR.$fbuid)) {
                // Si la miniature n'existe pas
                if(!file_exists(PROFIL_PICTURE_DIR.$f) && !$error) {
                    // On la génère
                    $d = TrinacriaImageTools::resize(
                        PROFIL_PICTURE_DIR,
                        PROFIL_PICTURE_DIR.$fbuid,
                        // Dimensions désirées
                        $td,
                        // On garde le fichier source
                        TrinacriaImageTools::KEEP_ORIGINAL,
                        // Suffix
                        $td[TrinacriaImageTools::WIDTH]
                        .'x'.$td[TrinacriaImageTools::HEIGHT]
                    );
                }

                $r = array($f,filemtime(PROFIL_PICTURE_DIR.$f));
            } else {
                $r = self::NO_PICTURE;
            }

            return $r;
        }
    }

    /**
     * Récupère les amis Facebook
     *
     * @param TrinacriaFB $facebook
     * Instance TrinacriaFB pour communiquer avec l'API Facebook
     *
     * @param const $type
     * Type de retour attendu :<br />
     * TrinacriaUser::GET_FRIENDS_BOTH -> retourne un tableau avec en index
     * les users ID et en valeur les noms<br />
     * TrinacriaUser::GET_FRIENDS_ID -> retourne un tableau avec en valeur
     * les users ID<br />
     * TrinacriaUser::GET_FRIENDS_NAME -> retourne un tableau avec en valeur
     * les noms
     */
    public function getFriends($facebook, $type = self::GET_FRIENDS_BOTH) {
        // TODO : Gestion CACHE ?
        // - SQL
        // - Fichier
        // - RAM : Memecached
        $d = array();

        if(!empty($this->id)) {
            try {
                $friends = $facebook->api('/me/friends');

                switch($type) {
                    case self::GET_FRIENDS_ID:
                        foreach($friends['data'] as $friend) {
                            $d[] = $friend['id'];
                        }
                    break;

                    case self::GET_FRIENDS_NAME:
                        foreach($friends['data'] as $friend) {
                            $d[] = $friend['name'];
                        }
                    break;

                    case self::GET_FRIENDS_BOTH:
                        foreach($friends['data'] as $friend) {
                            $d[$friend['id']] = $friends['data'];
                        }
                    break;
                }
            } catch(FacebookApiException $e) {
                TrinacriaLogs::log(
                    sprintf(APP_LOGS, date('d-m-y')),
                    __FILE__,
                    __LINE__,
                    serialize(array(
                        $e->getType(),
                        $e->getMessage()
                    ))
                );

                // Affichage d'une page d'erreur
                TrinacriaAppEngine::errorPage(
                    __FILE__,
                    __LINE__,
                    __METHOD__,
                    'ERROR : Can\'t get friends'
                );
            }
        }

        return $d;
    }
}
?>
