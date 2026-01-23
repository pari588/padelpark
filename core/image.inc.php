<?php

//CALL VIA image.inc.php
//COREURL./image.inc.php?path=product/0012666001339326116.jpg&w=180&h=150&type=crop
//DIRECT IMAGE FILE CALL

//Reccomended By G sir For Use Below Method
//UPLOADURL./products/435_555_crop_100_75_150/0012666001339326116.jpg
// Params are as follows ( 1st two param are mandatory) : 435_555_ratio_100_75_150 = width_height_type_quality_x_y

define("HAR_AUTO_NAME", 1);

class resizeImage
{
    var $imgFile = "";
    var $imgWidth = 0;
    var $imgHeight = 0;
    var $reqWidth = 0;
    var $reqHeight = 0;
    var $propWidth = 0;
    var $propHeight = 0;
    var $imgType = "";
    var $mimeType = "";
    var $fCallback = array();
    var $imgError = "";
    var $fileName = "";
    var $folderPath = "";
    var $resizeType = "";
    var $quality = 0;
    var $posX = 0;
    var $posY = 0;
    var $thumbPath = "";

    function __construct($width, $height, $file, $resizeType = "ratio", $quality = 100)
    { // Constructer
        if (!$resizeType)
            $resizeType = "ratio";
        if (!$quality)
            $quality = 100;
        $this->imgFile = $file;
        $this->quality = intval($quality);
        $this->resizeType = htmlspecialchars($resizeType);

        $arrPath = explode("/", $file);
        $this->fileName = end($arrPath);
        $this->folderPath = UPLOADPATH . "/" . str_replace($this->fileName, "", $this->imgFile);
        $this->thumbPath = $this->folderPath . 'tmp/' . intval($width) . "_" . intval($height) . "_" . $this->resizeType . "_" . $this->fileName;

        if (!file_exists($this->thumbPath) || !is_file($this->thumbPath)) {

            if (empty($this->imgFile)) {
                $this->imgError = "Error loading " . $this->imgFile;
                return false;
            } else {
                $this->fileName = urldecode($this->fileName);
                $arrImage = @getimagesize($this->folderPath . $this->fileName);

                $this->imgWidth = $arrImage[0];
                $this->imgHeight = $arrImage[1];

                if ($this->imgWidth <= 0 || $this->imgHeight <= 0) {
                    $this->imgError = "Could not resize given image";
                    return false;
                } else {

                    if (!$width)
                        $width = $height * $this->imgWidth / $this->imgHeight;
                    if (!$height)
                        $height = $width * $this->imgHeight / $this->imgWidth;

                    if ($width <= 0)
                        $this->reqWidth = $this->imgWidth;
                    if ($height <= 0)
                        $this->reqHeight = $this->imgHeight;
                    else
                        $this->reqWidth = $width;
                    $this->reqHeight = $height;

                    $this->imgType = $arrImage[2];
                    $this->mimeType = $arrImage[6];
                }
            }
        }
    }

    function __getTheFunction()
    {
        switch ($this->imgType) {
            case 1: //jpeg gif					
                $this->fCallback["start"] = "imagecreatefromgif";
                $this->fCallback["end"] = "imagegif";
                break;

            case 2: //jpeg funciton					
                $this->fCallback["start"] = "imagecreatefromjpeg";
                $this->fCallback["end"] = "imagejpeg";
                break;

            case 3: //jpeg png					
                $this->fCallback["start"] = "imagecreatefrompng";
                $this->fCallback["end"] = "imagepng";
                $scaleQuality = round($this->quality / 100) * 9;
                $this->quality = 9 - $scaleQuality;
                break;
        }
    }

    function ratio()
    { // Resizes image ti fit inside given dimentions
        if ($this->imgWidth >= $this->imgHeight) {
            $this->propWidth = $this->reqWidth;
            $this->propHeight = ($this->imgHeight * $this->reqWidth) / $this->imgWidth;
        } else {
            $this->propHeight = $this->reqHeight;
            $this->propWidth = ($this->imgWidth * $this->reqHeight) / $this->imgHeight;
        }

        if ($this->propWidth > $this->reqWidth) {
            $this->propWidth = $this->reqWidth;
            $this->propHeight = ($this->imgHeight * $this->reqWidth) / $this->imgWidth;
        } else if ($this->propHeight > $this->reqHeight) {
            $this->propHeight = $this->reqHeight;
            $this->propWidth = ($this->imgWidth * $this->reqHeight) / $this->imgHeight;
        }

        $this->reqHeight = $this->propHeight;
        $this->reqWidth = $this->propWidth;
    }

    function crop()
    { // crops the image after resizing to the min possible size
        $perWidth = ($this->reqWidth / $this->imgWidth) * 100;
        $perHeight = ($this->reqHeight / $this->imgHeight) * 100;

        if ($perWidth > $perHeight) {
            $this->propHeight = ($perWidth * $this->imgHeight) / 100;
            $this->propWidth = $this->reqWidth;
        } else {
            $this->propWidth = ($perHeight * $this->imgWidth) / 100;
            $this->propHeight = $this->reqHeight;
        }

        $y = trim($_GET["y"]);
        $x = trim($_GET["x"]);

        if ("$x" != "" && "$x" != "x") {
            $this->posX = intval($x);
        } else {
            $this->posX = ($this->reqWidth - $this->propWidth) / 2;
        }

        if ("$y" != "" && "$y" != "y") {
            $this->posY = intval($y);
        } else {
            $this->posY = ($this->reqHeight - $this->propHeight) / 2;
        }
    }

    function __resize()
    {
        $resizeFunction = $this->resizeType;
        $tmpPath = $this->folderPath . "tmp/";
        $this->fileName = urldecode($this->fileName);
        if (file_exists($this->folderPath . $this->fileName) && is_file($this->folderPath . $this->fileName)) {

            if ((!file_exists($this->thumbPath) || !is_file($this->thumbPath))) {

                if (!file_exists($tmpPath)) {
                    mkdir($tmpPath, 0777);
                }

                $this->$resizeFunction();
                $this->__getTheFunction();

                $tmpImg = $this->fCallback["start"]($this->folderPath . $this->fileName);
                $newimg = @imagecreatetruecolor($this->reqWidth, $this->reqHeight);

                if ($this->imgType == 1) { // Code to keep transparency of image
                    $colorcount = imagecolorstotal($tmpImg);
                    if ($colorcount == 0)
                        $colorcount = 256;
                    imagetruecolortopalette($newimg, true, $colorcount);
                    imagepalettecopy($newimg, $tmpImg);
                    $transparentcolor = imagecolortransparent($tmpImg);
                    imagefill($newimg, 0, 0, $transparentcolor);
                    imagecolortransparent($newimg, $transparentcolor);
                }

                if ($this->imgType == 3) {
                    imagealphablending($newimg, false);
                    imagesavealpha($newimg, true);
                    $transparent = imagecolorallocatealpha($newimg, 255, 255, 255, 127);
                    imagefilledrectangle($newimg, 0, 0, $this->propWidth, $this->propHeight, $transparent);
                }

                @imagecopyresampled($newimg, $tmpImg, $this->posX, $this->posY, 0, 0, $this->propWidth, $this->propHeight, $this->imgWidth, $this->imgHeight);
                @imagedestroy($tmpImg);

                if ($this->imgType == 1) {
                    $this->fCallback["end"]($newimg, $this->thumbPath);
                } else {
                    $this->fCallback["end"]($newimg, $this->thumbPath, $this->quality);
                }
                @imagedestroy($newimg);
            }

            @header("Content-type: " . $this->mimeType);
            $content = @file_get_contents($this->thumbPath);
            if ($content != FALSE) {
                echo $content;
            }
        }
    }
}

require_once("../config.inc.php");
if (isset($_GET["imginc"]) && $_GET["imginc"] != "") {
    $dirPath = $_SERVER["REQUEST_URI"];
    $dirPath = str_replace($FOLDER . "/" . UPLOADURL . "/", "", $dirPath);
    if ($dirPath) {
        $arrPath = array_values(array_filter(explode("/", $dirPath)));
        $imgName = end($arrPath);
        
        if ($imgName != "") {
            $_GET["path"] = $arrPath[count($arrPath) - 3] . "/" . $imgName;
            
            $strParam = $arrPath[count($arrPath) - 2];
            if (isset($strParam) && $strParam != "") {
                //list($w, $h, $type, $quality, $x, $y) = explode("_", $strParam);

                $arrParam = explode("_", $strParam);

                if (isset($arrParam[0]) && trim($arrParam[0]) !== "") {
                    $_GET["w"] = $arrParam[0];
                }
                if (isset($arrParam[1]) && trim($arrParam[1]) !== "") {
                    $_GET["h"] = $arrParam[1];
                }
                if (isset($arrParam[2]) && trim($arrParam[2]) !== "") {
                    $_GET["type"] = $arrParam[2];
                }
                if (isset($arrParam[3]) && trim($arrParam[3]) !== "") {
                    $_GET["quality"] = $arrParam[3];
                }
                if (isset($arrParam[4]) && trim($arrParam[4]) !== "") {
                    $_GET["x"] = $arrParam[4];
                }
                if (isset($arrParam[5]) && trim($arrParam[5]) !== "") {
                    $_GET["y"] = $arrParam[5];
                }
            }
        }
    }
}

if (!isset($_GET["w"]))
    $_GET["w"] = 0;
if (!isset($_GET["h"]))
    $_GET["h"] = 0;
if (!isset($_GET["path"]))
    $_GET["path"] = "";
if (!isset($_GET["type"]))
    $_GET["type"] = "";
if (!isset($_GET["quality"]))
    $_GET["quality"] = 100;

$obj = new resizeImage($_GET["w"], $_GET["h"], $_GET["path"], $_GET["type"], $_GET["quality"]);
$obj->__resize();
