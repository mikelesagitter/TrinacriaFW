<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require_once 'TrinacriaUpload.php';
require_once 'TrinacriaValidate.php';

class TrinacriaImageTools extends TrinacriaUpload {
    const NO_ERROR = 0;
    const ERROR_NEW_FILE = 1;
    const ERROR_DESTINATION = 2;
    const ERROR_MIME_EXT = 3;
    const ERROR_SUFFIX = 4;
    const ERROR_SOURCE = 5;
    const ERROR_QUALITY = 6;
    const ERROR_DIMENSIONS = 7;

    const ORIGINAL_FILE_NAME = parent::ORIGINAL_FILE_NAME;
    const SIZE_1M = parent::SIZE_1M;

    const KEEP_ORIGINAL = true;
    const N_KEEP_ORIGINAL = false;
    
    const NO_EXTENSION = 10;
    
    const RESIZE_TO_FILL = 5;
    const RESIZE_TO_FIT = 10;
    
    const WIDTH = 'w';
    const HEIGHT = 'h';
    const RESIZE_METHOD = 'r';
    const QUALITY = 'q';
    const FORMAT = 'f';
    
    /*
     * @param keep : bool, keep original file
     */
    static public function uploadImg($file, $dir, $resize = array(),
        $keep = true, $maxSize = self::SIZE_1M, $newName = '', $check = 'mime',
        $algo = 'sha256')
    {
        //~ debug(__METHOD__);
        $upload = parent::uploadFile($file, $dir, 'images', $maxSize, $newName,
            $check, $algo);

        $result['source'] = $upload;
        $result['thumbnail'] = null;

        if(parent::NO_ERROR == $upload['error']) {
            if(is_array($resize) && 2 == count($resize) && is_int($resize[0])
                && is_int($resize[1])) {
                // Keep original file
                if(!is_bool($keep)) {
                    $keep = true;
                }

                $thumbnail = self::resize($dir, $upload, $resize, $keep);

                if($keep) {
                    $tmp = self::getDimensions($upload['file']);
                    $upload = array_merge($upload, $tmp);

                    $result = array(
                        'source' => $upload,
                        'thumbnail' => $thumbnail
                    );

                    return $result;
                } else {
                    return $thumbnail;
                }
            } else {
                return $result;
            }
        } else {
            return $result;
        }
    }
    
    /*
     * Resize image
     * @param dir : destination directory
     * @param file : image to resize
     * @param to : array(
     *      'w' => w,
     *      'h' => h,
     *      'r' => RESIZE_TO_FILL | RESIZE_TO_FIT
     *      'q' => q
     * )
     *  w : width
     *  h : height
     *  option : Fill or Fit dimensions
     *  q : quality
     *  
     * @param keep : keep original file ?
     * @param suffix : suffix for generated file
     * 
     * TODO: manage resize from EXIF, not from EXTENSION
     * TODO: manage if W / H is not mentionned
     */
    static public function resize($dir, $file, $to, $keep = true,
        $suffix = 'thumb') {

        // Returned Associative array
        $result = array();
        $result['function'] = __METHOD__;
        
        
        if(!is_array($to)) {
            $result['error'] = self::ERROR_DESTINATION;
            return $result;
        }

        // true when use manually ;
        // false when called by TrinacriaImageTools::uploadImg
        if(!is_array($file)) {
            if(!is_string($file)) {
                $result['error'] = self::ERROR_SOURCE;
                return $result;
            } else {
                /*
                 * old source ; can be used for PHP < 5.2.0
                 */
                /*
                $tmp = $file;

                $file = array();
                
                $file['file'] = $tmp;

                // Search file extension if exist
                $pos = strrchr($file['file'], '.');
                
                if(false !== $pos) {
                    $file['ext'] = strtolower(substr($pos,1));
                } else {
                    $file['ext'] = self::NO_EXTENSION;
                }
                
                $tmp = explode(DIRECTORY_SEPARATOR, $tmp);
                end($tmp);
                
                $file['filename'] = $tmp[key($tmp)];
                $file['filename'] = substr(
                    $file['filename'], 0, strrpos($file['filename'], '.')
                );

                unset($tmp, $pos);
                //*/
                
                $tmp = $file;
                
                $file = array();
                $file['file'] = $tmp;
                $file['filename'] = pathinfo($file['file'], PATHINFO_FILENAME);
                unset($tmp);
                
                /*
                if(isset($tmp['extension'])) {
                    $file['extension'] = $tmp['extension'];
                } else {
                    $file['extension'] = self::NO_EXTENSION;
                }
                */
            }
        }

        // Check exif
        $exif = exif_imagetype($file['file']);

        if(false === $exif) {
            $result['error'] = self::ERROR_MIME_EXT;
            return $result;
        }
            
        // Flag to know if we can do resize
        $canResize = false;
        // Store ORIGINAL WANTED DIMENSIONS
        $wanted = array();

        // Want specific WIDTH ?
        if(isset($to[self::WIDTH])) {
            $canResize = true;
            $wanted['w'] = $result['w'] = (int) $to[self::WIDTH];
        }

        // Want specific HEIGHT ?
        if(isset($to[self::HEIGHT])) {
            $canResize = true;
            $wanted['h'] = $result['h'] = (int) $to[self::HEIGHT];
        }
        
        // Want specific FORMAT ?
        if(isset($to[self::FORMAT])) {
            $canResize = true;
            $result['f'] = $to[self::FORMAT];
            
        } else {
            $result['f'] = $exif;
        }
        
        switch($result['f']) {
            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000:
                $file['extension'] = 'jpg';
                break;

            case IMAGETYPE_GIF:
                $file['extension'] = 'gif';
                break;

            case IMAGETYPE_PNG:
                $file['extension'] = 'png';
                break;
        }

        // We need at least one dimension
        if(!$canResize) {
            $result['error'] = self::ERROR_DIMENSIONS;
            return $result;
        }

        // Quality ? Only for JPG
        if(isset($to[self::QUALITY])) {
            $jpgQuality = $to[self::QUALITY];
            
            if(!TrinacriaValidate::number($jpgQuality, array(
                TrinacriaValidate::NUMBER_DECIMAL
                    => TrinacriaValidate::NUMBER_DECIMAL_DISABLED,
                TrinacriaValidate::NUMBER_MIN => 0,
                TrinacriaValidate::NUMBER_MAX => 100
            ))) {
                $result['error'] = self::ERROR_QUALITY;
                return $result;
            } else {
                $jpgQuality = (int) $jpgQuality;
            }
        } else {
            $jpgQuality = 100;
        }

        // Flag to know if resize is done or not
        $resizeDone = false;

        // Clean destination dir (test last character)
        if(DIRECTORY_SEPARATOR == substr($dir, -1, 1)) {
            $dir = substr($dir, 0, -1);
        }

        // Source file
        $source = $file['file'];

        // Source file's simensions
        $sizes = getimagesize($source);

        // (list is slower than manual assignment)
        // Source file's width
        $sWidth = $sizes[0];
        // Source file's height
        $sHeight = $sizes[1];
        unset($sizes);

        // Thumbnail
        $result['thumbnail'] = $file['filename'].'_'.$suffix.(
            $file['extension'] !== self::NO_EXTENSION ?
                '.'.$file['extension'] : ''
        );

        $thumbnail = $dir.DIRECTORY_SEPARATOR.$result['thumbnail'];

        // Resize only if necessary
        // If method = RESIZE_TO_FIT,
        // the thumbnail can be bigger than his source
        if($sWidth < $result['w'] && $sHeight < $result['h']) {
            $resizeDone = true;
            $result['w'] = $sWidth;
            $result['h'] = $sHeight;
        } else {
            // 2 methods are available :
            // Fit wanted dimensions
            // or Fill
            if(!isset($to[self::RESIZE_METHOD])) {
                $to[self::RESIZE_METHOD] = self::RESIZE_TO_FILL;
            }

            switch($to[self::RESIZE_METHOD]) {
                case self::RESIZE_TO_FIT:
                    // calcul ratio
                    $ratio = (int)($sHeight*$result['w']/$sWidth);

                    if($ratio > $result['h']) {
                        // Will fit for width
                        // Height will be cutted
                        // 
                        // $result['w'] is unchanged
                        $result['h'] = $ratio;
                    } else {
                        // Will fit for height
                        // Width will be cutted
                        //
                        // $result['h'] is unchanged
                        $result['w'] = (int)($sWidth*$result['h']/$sHeight);
                    }

                    // -> The thumbnail can be bigger than his source
                break;

                case self::RESIZE_TO_FILL:
                default:
                    // calcul ratio
                    $ratio = $sWidth/$sHeight;

                    if($result['w']/$result['h'] > $ratio) {
                        $result['w'] = (int)($result['h']*$ratio);
                    } else {
                        $result['h'] = (int)($result['w']/$ratio);
                    }
                break;
            }

            // Create resource
            $img = imagecreatetruecolor($result['w'], $result['h']);

            // Copy image in memory to manipulate it
            switch($exif) {
                case IMAGETYPE_JPEG:
                case IMAGETYPE_JPEG2000:
                    $imgtmp = imagecreatefromjpeg($source);
                    break;

                case IMAGETYPE_GIF:
                    $imgtmp = imagecreatefromgif($source);
                    break;

                case IMAGETYPE_PNG:
                    $imgtmp = imagecreatefrompng($source);
                    break;
            }

            // Create thumbnail
            imagecopyresampled($img, $imgtmp, 0, 0, 0, 0,
                $result['w'], $result['h'], $sWidth, $sHeight);
            
            imagedestroy($imgtmp);

            // If we want to FILL the destination,
            // we may need to cut the borders
            if(self::RESIZE_TO_FIT == $to[self::RESIZE_METHOD]) {
                $imgtmp = imagecreatetruecolor($wanted['w'], $wanted['h']);
                imagecopy($imgtmp, $img, 0, 0,
                    (int)(($result['w']-$wanted['w'])/2),
                    (int)(($result['h']-$wanted['h'])/2),
                    $result['w'], $result['h']);
                
                imagedestroy($img);
                $img = $imgtmp;
                
                $result['w'] = $wanted['w'];
                $result['h'] = $wanted['h'];
                
                unset($imgtmp, $wanted);
            }

            switch($result['f']) {
                case IMAGETYPE_JPEG:
                case IMAGETYPE_JPEG2000:
                    $created = imagejpeg($img, $thumbnail, $jpgQuality);
                    break;

                case IMAGETYPE_GIF:
                    $created = imagegif($img, $thumbnail);
                    break;

                case IMAGETYPE_PNG:
                    // Maximum compression of PNG ; 
                    // TODO : support for FILTER ?
                    // -> http://fr.php.net/imagepng
                    $created = imagepng($img, $thumbnail, 9);
                    break;
            }
            
            imagedestroy($img);

            if(!$created) {
                $result['error'] = self::ERROR_NEW_FILE;
                return $result;
            } else {
                $resizeDone = true;
                chmod($thumbnail, 0644);
            }
        }

        $result['filename'] = $file['filename'];
        //$result['ext'] = $file['extension'];
        $result['error'] = self::NO_ERROR;

        // if we dont keep original file
        if(self::N_KEEP_ORIGINAL === $keep) {
            // if file is not resized, rename source to thumbnail
            if($resizeDone) {
                // Delete source file
                unlink($source);

                // rename thumb to source
                rename($thumbnail, $source);
            }

            chmod($source, 0644);

            $result['file'] = $source;
            unset($result['thumbnail']);
        } else {
            $result['source'] = $source;
            $result['file'] = $thumbnail;
        }

        return $result;
    }

    static public function getDimensions($file) {
        $sizes = getimagesize($file);
        return array('w' => $sizes[0], 'h' => $sizes[1]);
    }
    
    static public function generateFileName($basename, $type) {
        $r = $basename;
        switch($type) {
            case IMAGETYPE_GIF:
                $r .= '.gif';
            break;
        
            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000:
                $r .= '.jpg';
            break;
        
            case IMAGETYPE_PNG:
                $r .= '.png';
            break;
        }
        
        return $r;
    }
}
?>
