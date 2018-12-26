<?php

  // Config
  define("SITENAME", "Your Site");      // Set the name of your site here
  $dataDirectory = "data/";             // Set the path of the directory you want to list here (NEEDS TO BE RELATIVE AND ACCESSIBLE!)
  define("NOHEADER", false);            // Set to true to skip displaying the banner-image
  define("BANNER", "banner.jpg");
  define("APPNAME", "PHPHangar");       // Name of the App if you want to change it
  define("ATTRIBUTION", "<a href=\"https://github.com/Unkn0wnCat/PHPHangar\" target=\"_blank\">PHPHangar</a>");   // Footer-Link, you are free to change this, but I would really appreciate if you left it in




  // Script
  define('ROOTPATH', __DIR__);

  $dataDirectory = str_replace("\\", "/", $dataDirectory);

  $subDirectory = "";

  if(isset($_GET['dir'])) {
    $subDirectory = $_GET['dir'];
    $subDirectory = str_replace("\\", "/", $subDirectory);
    $subDirectory = str_replace("..", "", $subDirectory, $counter1);
    if($counter1 > 0) {
      header("Location: ?dir=");
      die(".. not allowed in path");
    }
  }

  $folders = "";
  $up = "";

  if(str_replace(array("/", " "), "", $subDirectory) != "") {
    $up = '<div class="dirup"><img alt="dirup" src="images/arrow_up.gif" width="16px" height="16px"><a href="?dir='.popPath(removeDoubleSlash($subDirectory)).'"> ..</a></div>';
    $folders = $up;
  }
  $files   = "";

  $GLOBALS['debug'] = "";

  $readme = false;

  if ($folderHandle = @opendir($dataDirectory . $subDirectory)) {
    while (false !== ($entry = readdir($folderHandle))) {
        if ($entry != "." && $entry != "..") {
          if($entry == ".readme.html") {
            $fileInfo = pathinfo($dataDirectory . $subDirectory . "/" . $entry);
            $readme = removeDoubleSlash($fileInfo['dirname'] . '/' . $fileInfo['basename']);
          }
          if($entry[0] == '.') {
            continue;
          }
          if(is_dir($dataDirectory . $subDirectory . "/" . $entry)) {
            $folders .= '<div class="content"><img alt="Ordner" src="images/folder.gif" width="16px" height="16px"> <a href="?dir=' . removeDoubleSlash($subDirectory . "/" . $entry) . '">'.$entry.'</a></div>';
          } else {
            $fileInfo = pathinfo($dataDirectory . $subDirectory . "/" . $entry);
              $icon = getFileIcon($fileInfo['dirname'] . '/' . $fileInfo['basename']);
              $files .= '<div class="content"><img alt="File" src="images/'.$icon.'" width="16px" height="16px"><!--<div style="display: inline-block; width: 16px; height: 16px;"></div>--> <a href="' . removeDoubleSlash($fileInfo['dirname'] . '/' . $fileInfo['basename']) . '" title="'.$entry.'">'.$entry.'</a></div>';

          }
      }
    }
    closedir($folderHandle);

    $GLOBALS['subdir'] = $subDirectory;

    output($files, $folders, $GLOBALS['debug'], $readme);

  } else {
    $template = file_get_contents("./template.html");
    $template = str_replace("@LIST@", "$up<H1 class=\"error\">Failed to open Folder<BR><code style=\"overflow: hidden;\">$subDirectory</code></H1>", $template);
    $template = str_replace("@BREADCRUMB@", '<li><a href="?dir=">root</a></li>', $template);
    $template = str_replace("@README@", '', $template);


    echo $template;
  }

  function filePathParts($arg1) {
    $GLOBALS['debug'] .= "DIR: " . $arg1['dirname'] . "<BR>\n";
    $GLOBALS['debug'] .= "BASE: " . $arg1['basename'] . "<BR>\n";
    $GLOBALS['debug'] .= "EXT: " . $arg1['extension'] . "<BR>\n";
    $GLOBALS['debug'] .= "FN: " . $arg1['filename'] . "<BR><BR>\n";
  }


  function output($files, $folders, $debug, $readme) {
    $template = file_get_contents("./template.html");
    if(@$_GET['noheader'] == "true" || NOHEADER == true) {
      $template = str_replace("@HEADER@", '', $template);
    } else {
      $template = str_replace("@HEADER@", '<a href="."><img alt="'.APPNAME.'" src="'.BANNER.'" width="550px" height="auto" class="banner"></a>', $template);
    }
    $template = str_replace("@SITENAME@", SITENAME, $template);
    $template = str_replace("@APPNAME@", APPNAME, $template);
    $template = str_replace("@ATTRIBUTION@", ATTRIBUTION, $template);

    $template = str_replace("@LIST@", $folders . $files, $template);
    $template = str_replace("@BREADCRUMB@", makeBreadcrumbs(removeDoubleSlash($GLOBALS['subdir'])), $template);
    if($readme==false) $template = str_replace("@README@", '', $template);
    else $template = str_replace("@README@", '<div class="headertext">~ README ~</div><iframe class="readme" src="'.$readme.'"></iframe>', $template);

    echo $template;
  }

  function popPath($path) {
    $array = explode("/", $path);
    array_pop ($array);
    return implode("/", $array);
  }

  function makeBreadcrumbs($path) {
    if($path == "/" || $path == "/") {
      $output = '<li><a href="?dir=">root</a></li>';
    } else {
      $array = explode("/", $path);
      array_shift($array);

      $output = '<li><a href="?dir=">root</a></li>';

      $path = "/";

      foreach ($array as $key => $value) {
        $path .= $value;
        $output .= '<li><a href="?dir='.$path.'">'.$value.'</a></li>';
        $path .= "/";
      }
    }


    return $output;
  }

  function removeDoubleSlash($in) {
    $str = $in;
    $counter2 = 0;

    $running = true;

    while($running) {
      $str = str_replace("//", "/", $str, $counter2);
      if($counter2 > 0) {
        $running = true;
      } else {
        $running = false;
      }
    }


    return $str;
  }

  function getFileIcon($filepath) {
    $mime = mime_content_type($filepath);
    $array = explode("/", $mime);

    if(@$_GET['debug'] == "true") {echo($filepath . ": " . $mime . ": " . $array[0]."<BR>");}

    switch ($mime) {
      case 'text/html':
        return "page_html.gif";
        break;
      case 'application/x-shockwave-flash':
        return "application_flash.gif";
        break;
      case 'application/pdf':
        return "file_acrobat.gif";
        break;
      case 'text/x-php':
        return "page_php.gif";
        break;
    }
    switch ($array[0]) {
      case 'image':
        return "image.gif";
        break;
      case 'audio':
        return "page_sound.gif";
        break;
      case 'video':
        return "page_video.gif";
        break;
      case 'text':
        return "page_text.gif";
        break;
      default:
        return "page.gif";
        break;
    }
  }