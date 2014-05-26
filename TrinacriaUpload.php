<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

class TrinacriaUpload {
    //
    // Les erreurs commencent à 20
    // pour ne pas interférés avec celles natives de PHP
    //
    // http://php.net/manual/fr/features.file-upload.errors.php
    //
    const ERROR_DATAS_SOURCE = 20;
    const ERROR_DESTINATION = 21;
    const ERROR_MIME_EXT = 22;
    const ERROR_MOVE_UPLOAD_FILE = 23;
    const ERROR_CHMOD = 24;
    const ERROR_SIZE_TOO_BIG = 25;
    /**
     * 1 Mio en Bits
     *
     * @since 1.0.0
     * @see http://fr.wikipedia.org/wiki/Octet#Multiples_normalis.C3.A9s
     */
    const SIZE_1M = 1048576;


    /**
     * Constante pour le paramètre $check
     * <br />
     * Vérifie l'EXTENSION
     *
     * @since 1.0.0
     */
    const CHECK_EXT = 'ext';
    /**
     * Constante pour le paramètre $check
     * <br />
     * Vérifie le MIME TYPE et l'EXTENSION
     *
     * @since 1.0.0
     */
    const CHECK_MIME = 'mime';
    /**
     * Constante pour le paramètre $check
     * <br />
     * Vérifie les EXIF, et le MIME TYPE et l'EXTENSION
     *
     * @since 1.0.0
     */
    const CHECK_EXIF = 'exif';


    const TYPE_IMAGES = 1;
    const TYPE_FILES = 2;

    const ORIGINAL_FILE_NAME = 7;


    /**
     * Types de fichiers acceptés
     *
     * @since 1.0.0
     * @see http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     * @var array
     */
    static private $datas = array(
        self::TYPE_IMAGES => array(
            'gif' => array(
                'mimes' => 'image/gif',
                'exif' => IMAGETYPE_GIF
            ),
            'jpg' => array(
                'mimes' => array('image/jpg', 'image/jpeg', 'image/pjpg',
                    'image/pjpeg'),
                'exif' => IMAGETYPE_JPEG
            ),
            'jpeg' => array(
                'mimes' => array('image/jpg', 'image/jpeg', 'image/pjpg',
                    'image/pjpeg'),
                'exif' => IMAGETYPE_JPEG
            ),
            'png' => array(
                'mimes' => 'image/png',
                'exif' => IMAGETYPE_PNG
            )
        ),
        self::TYPE_FILES => array(
            'pdf' => array(
                'mimes' => 'application/pdf'
            )
        )
    );


    /*
     * Upload a file
     *
     * @param array $file : $_FILES
     * @param string $dir : directory
     * @param string $type : images / files
     * @param string $newName : new name for file
     * @param string $algo : algo from hash_algos()
     * @param string $check : CHECK_EXT, CHECK_MIME,CHECK_EXIF
     * @return integer
     *  O upload done without problem
     *  1 erreur from file datas source
     *  2 erreur for destination
     *  3 erreur for mime/ext
     *  4 erreur from move_upload_file
     *  5 erreur from chmod
     *  6 size too big
     */
    static public function uploadFile($file, $dir, $type = '',
        $maxSize = self::SIZE_1M, $newName = '', $check = self::CHECK_MIME,
        $algo = 'sha256')
    {
        $result = array();
        $result['function'] = 'TrinacriaUpload::uploadFile';

        if(!is_array($file)) {
            $result['error'] = self::ERROR_DATAS_SOURCE;
            return $result;
        }

        if(UPLOAD_ERR_OK !== $file['error']) {
            $result['error'] = $file['error'];
            return $result;
        }

        if(empty($dir) || !is_dir($dir)) {
            $result['error'] = self::ERROR_DESTINATION;
            return $result;
        }

        if(DIRECTORY_SEPARATOR !== substr($dir, -1, 1)) {
            $dir .= DIRECTORY_SEPARATOR;
        }

        if($file['size'] > $maxSize) {
            $result['error'] = self::ERROR_SIZE_TOO_BIG;
            return $result;
        }

        $result['mime'] = $file['type'];

        $result['ext'] = strtolower(substr(strrchr($file['name'], '.'),1));

        // if check mime is require
        // check if mime and extensions are good
        if(!empty($check)) {
            switch($check) {
                case self::CHECK_EXT:
                    $checked = self::checkExt($type, $result['ext']);
                    break;

                case self::CHECK_MIME:
                    $checked = self::checkMime($type, $result['mime'],
                        $result['ext']);
                    break;

                case self::CHECK_EXIF:
                    $checked = self::checkExif($type, $file['tmp_name'],
                        $result['ext'], $result['mime']);
                    break;

                default:
                    $checked = false;
                    break;
            }
        } else {
            $checked = true;
        }

        if(!$checked) {
            $result['error'] = self::ERROR_MIME_EXT;
            return $result;
        }

        if(empty($newName)) {
            if(!in_array($algo, hash_algos())) {
                $result['filename'] = hash_hmac_file('sha256',
                    $file['tmp_name'], TrinacriaUtils::uniqid());
            } else {
                $result['filename'] = hash_hmac_file($algo,
                    $file['tmp_name'], TrinacriaUtils::uniqid());
            }
        } else {
            if(self::ORIGINAL_FILE_NAME === $newName) {
                $result['filename'] = substr($file['name'], 0, strrpos($file['name'], '.'));
            }

            $result['filename'] = replaceSpecialsChars($result['filename']);
        }

        $result['file'] = $dir.$result['filename'].'.'.$result['ext'];

        if(!move_uploaded_file($file['tmp_name'], $result['file'])) {
            $result['error'] = self::ERROR_MOVE_UPLOAD_FILE;
        } else {
            if(!chmod($result['file'], 0666)) {
                $result['error'] = self::ERROR_CHMOD;
            } else {
                $result['error'] = UPLOAD_ERR_OK;
            }
        }

        return $result;
    }

    static public function checkExt($type, $ext) {
        if(!empty($type) && !empty(self::$datas[$type])) {
            if(array_key_exists($ext, self::$datas[$type])) {
                return true;
            } else {
                return false;
            }
        } else {
            $match = false;

            reset(self::$datas);
            $k = key(self::$datas);

            while($k !== null && !$match) {
                if(array_key_exists($ext, self::$datas[$k])) {
                    $match = true;
                } else {
                    next(self::$datas);
                    $k = key(self::$datas);
                }
            }

            return $match;
        }
    }

    static public function checkMime($type, $mime, $ext) {
        if(!self::checkExt($type, $ext)) {
            return false;
        } else {
            if(!empty($type) && !empty(self::$datas[$type])) {
                $mimes = self::$datas[$type][$ext]['mimes'];
                if( (!is_array($mimes) && $mime == $mimes) || (is_array($mimes) && in_array($mime, $mimes)) ) {
                    return true;
                } else {
                    return false;
                }
            } else {
                $match = false;

                reset(self::$datas);
                $k = key(self::$datas);

                while($k !== null && !$match) {
                    $mimes = self::$datas[$k][$ext]['mimes'];
                    if( (!is_array($mimes) && $mime == $mimes) || (is_array($mimes) && in_array($mime, $mimes)) ) {
                        $match = true;
                    } else {
                        next(self::$datas);
                        $k = key(self::$datas);
                    }
                }

                return $match;
            }
        }
    }

    static public function checkExif($type, $file, $ext, $mime) {
        if(!self::checkMime($type, $mime, $ext)) {
            return false;
        } else {
            if(!empty($type) && !empty(self::$datas[$type])) {
                if(!empty(self::$datas[$type][$ext]['exif']) && exif_imagetype($file) == self::$datas[$type][$ext]['exif']) {
                    return true;
                } else {
                    return false;
                }
            } else {
                $match = false;

                reset(self::$datas);
                $k = key(self::$datas);

                while($k !== null && !$match) {
                    if(!empty(self::$datas[$k][$ext]['exif']) && exif_imagetype($file) == self::$datas[$k][$ext]['exif']) {
                        $match = true;
                    } else {
                        next(self::$datas);
                        $k = key(self::$datas);
                    }
                }

                return $match;
            }
        }
    }
}
?>
