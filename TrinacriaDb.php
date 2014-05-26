<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');
/**
 * @file
 * Classe TrinacriaDb
 *
 * Wrapper pour PDO ;
 *
 * @since 1.0.0
 * @see PDO
 */

class TrinacriaDb extends PDO {
    const HOST = 'host';
    const DRIVER = 'driver';
    const USER = 'user';
    const PASSWORD = 'password';
    const NAME = 'name';

    // AAAA-MM-JJ HH:MM:SS
    const MYSQL_DATE_TIMESTAMP = 'Y-m-d H:i:s';
    const MYSQL_DATE_DATE = 'Y-m-d';

    public function  __construct($id) {
        require 'db.php';

        // $dbs defined in db.php
        if(!array_key_exists($id, $dbs)) {
            TrinacriaAppEngine::errorPage(
                __FILE__,
                __LINE__,
                __METHOD__,
                'INVALID_DB_ID'
            );
        } else {
            try {
                parent::__construct(
                    $dbs[$id][self::DRIVER]
                    .':dbname='.$dbs[$id][self::NAME]
                    .';host='.$dbs[$id][self::HOST],
                    $dbs[$id][self::USER],
                    $dbs[$id][self::PASSWORD]
                );

                $this->exec('SET NAMES \'utf8\';');
                return $this;
            } catch (PDOException $e) {
                if(DEBUG_MOD) {
                    $msg = 'DATABASE ERROR : ' . $e->getMessage();
                } else {
                    $msg = 'DATABASE ERROR : ' . $e->getCode();
                }

                TrinacriaAppEngine::errorPage(
                    __FILE__,
                    __LINE__,
                    __METHOD__,
                    $msg
                );
            }
        }
    }

    static public function cleanForSearch($a) {
        return str_ireplace('%','',$a);
    }
}
?>
