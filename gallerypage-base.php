<?php

/* Display a gallery of images.
 * Copyright 2009 Akkana Peck, http://shallowsky.com/software/
 * You are welcome to use, modify and share this software
 * under the terms of the GPL v2 or later.
 */

   /********************************************************************
   * Customize your banner here -- banner images, links or whatever:
   ********************************************************************/
  $banner = '';

  /************************************
   * Customize your bottom links here:
   ************************************/
  $linklist = array(
    "<a href=\"/photo.html\">Akkana's Photo Page</a>",
    "<a href=\"/\">Shallow Sky home</a>",
  );

  /**********************************************************
   * Preamble: if you have any text that might need to vary
   * based on the current filename or other variables,
   * you can build it up here.
   * Better, you can pass it in from index.php.
   **********************************************************/
   /* $preamble = ""; */

   /**********************************************************
   * Next and Prev Gallery: is there another gallery to go to
   * after the last image in this one, or was there one before
   * the first image here? This should be a link, e.g.
   * $nextgallery = "<a href=\"foo.php\">Foo</a>;
   **********************************************************/
   $prevgallery = "";
   $nextgallery = "";

  /**************************************************************
   * Maximum length for a caption length on the thumbnail page.
   * Typically you probably want this to be about a line of text.
   **************************************************************/
  $maxcaptionlen = 29;

  /************************************
   * End of typical customizations.
   ************************************/

  /*
   * decide whether this is a gallery page or an individual image:
   */
  if (isset($_GET['pic'])) {
    showpic($_GET['pic'], $banner, $title, $pixlist, $linklist,
            $prevgallery, $nextgallery);
  } elseif (isset($_GET['exif'])) {
    showexif($_GET['exif']);
  } else {
    gallerypage($banner, $title, $preamble, $pixlist, $linklist,
                $prevgallery, $nextgallery);
  }

function nextprevgallery($prevgallery, $nextgallery) {
  if ($prevgallery || $nextgallery) {
    print "<ul class=\"picnav\">\n";

    if ($prevgallery) {
      print "<li>Prev gallery: $prevgallery\n";
    }

    if ($nextgallery) {
      print "<li>Next gallery: $nextgallery\n";
    }

    print "</ul>\n";
  }
}

function pageheader($banner, $title, $caption) {
  if ($caption != "")
    $pagetitle = strip_tags($caption);
  else
    $pagetitle = strip_tags($title);
  echo <<<EOHDR
<html>
<head>
<title>$pagetitle</title>
<link rel="stylesheet" type="text/css" href="/css/gallery.css" />
<link rel="stylesheet" type="text/css" href="/css/gallerypic.css" />
<!--[if lt IE 9]>
<link rel="stylesheet" type="text/css" href="/css/gallery-ie.css" />
<![endif]-->
<!-- put any additional stylesheets here -->
</head>

<body>
$banner
<h1>$title</h1>

EOHDR;
}

function pagefooter($linklist, $prevgallery, $nextgallery) {
  print "<br clear=\"all\">\n\n<p>\n";

  nextprevgallery($prevgallery, $nextgallery);

  for ($i=0; $i<sizeof($linklist); ++$i) {
    print "$linklist[$i] |\n";
  }

  echo <<<EOFOOTER
<a href="/mailme.html">Mail Comments</a>
</p>

</body>
</html>
EOFOOTER;
}

function picnavbar($last, $title, $next, $prevgallery, $nextgallery) {
  $title = strip_tags($title);
  print "<ul class=\"picnav\">\n";
  if ($last != "")
    print("<li><a href=\"?pic=$last\">Prev</a></li>\n");
  elseif ($prevgallery != "")
    print("<li>Prev. Gallery: $prevgallery</li>\n");
  else print("<li>No Prev</li>\n");

  print("<li><a href=\"./\">$title</a></li>\n");

  if ($next != "")
    print("<li><a href=\"?pic=$next\">Next</a></li>\n");
  elseif ($nextgallery != "")
    print("<li>Next Gallery: $nextgallery</li>\n");
  else print("<li>[No Next]</li>\n");
  print "</ul>";
}

function showexif($pic) {
  $thumb = str_replace(".jpg", "T.jpg", $pic);
  print("<img align=right src=\"$thumb\" alt=\"[EXIF]\">\n");
  print("<h2>EXIF for $pic</h2>\n");
  print("<p><a href=\"?pic=$pic\">Back to $pic</a>\n");

  $exif = exif_read_data("$pic", 0, true);
  print("<table padding=5>");
  foreach ($exif as $key => $section) {
    foreach ($section as $name => $val) {
      if ($name != "Undefined") {
        echo "<tr><th align='right'>$name <td>$val </tr>\n";
      }
    }
  }
  print("</table>\n");
  print("<p><a href=\"?pic=$pic\">Back to $pic</a>\n");
}

/*************************************
 * Display a page for a single image
 *************************************/
function showpic($pic, $banner, $title, $pixlist, $linklist,
                 $prevgallery, $nextgallery) {
  global $maxcaptionlen;

  /* Find the given picture in the list */
  for ($i=0; $i<sizeof($pixlist); ++$i) {
    if ($pixlist[$i][0] == $pic)
      break;
  }
  $lastpic=""; $nextpic=""; $comment="";
  if ($i == sizeof($pixlist)) {
    $thispic=$pic;
  } else {
    $thispic = $pixlist[$i][0];
    $comment = $pixlist[$i][1];
    $imagesize = getimagesize( $thispic );  // extracts array, not size
    $imagesize = $imagesize[3]; // actual size string is part of array

    /* Find the next and previous images in the list, skipping headers */
    $j = $i-1;
    while ($j >= 0) {
      if (is_array($pixlist[$j])) {
        $lastpic = $pixlist[$j][0];
        break;
      }
      --$j;
    }
    $j = $i+1;
    while ($j < sizeof($pixlist)) {
      if (is_array($pixlist[$j])) {
        $nextpic = $pixlist[$j][0];
        break;
      }
      ++$j;
    }

    /*
    if ($i > 0) $lastpic = $pixlist[$i-1][0];
    if ($i < sizeof($pixlist)-1)
      $nextpic = $pixlist[$i+1][0];
     */
  }

  if ($comment != "")
    $alt = maxtrim($comment);
  else
    $alt = maxtrim($title);

  pageheader($banner, $title, $alt);
  picnavbar($lastpic, $title, $nextpic, $prevgallery, $nextgallery);
  print("<p>$comment\n<p>\n");

  print("<img src=\"$thispic\" $imagesize alt=\"[$alt]\">\n");
  picnavbar($lastpic, $title, $nextpic, $prevgallery, $nextgallery);

  print("<p>&nbsp; &nbsp; &nbsp; &nbsp; ");
  print("<a href=\"?exif=$thispic\">EXIF</a>\n");

  if (file_exists("fullsize/$thispic")) {
    print("<p>&nbsp; &nbsp; &nbsp; &nbsp; ");
    print("<a href=\"fullsize/$thispic\">(full size)</a>\n");
  }
  pagefooter($linklist, $prevgallery, $nextgallery);
}

function maxtrim($s) {
  global $maxcaptionlen;
  $slen = strlen($s);
    if ($slen > $maxcaptionlen) {
      return substr(strip_tags($s), 0, $maxcaptionlen-4) . " ...";
    }
    return $s;
}

/****************************************
 * Display the master page of thumbnails.
 ****************************************/
function gallerypage($banner, $title, $preamble, $pixlist, $linklist,
                     $prevgallery, $nextgallery)
{
  pageheader($banner, $title, $title);

  if ($preamble != "") {
    print "<p>\n$preamble\n<p>\n";
  }

  nextprevgallery($prevgallery, $nextgallery);

  $is_open = false;

  echo <<<EOHDR
EOHDR;

  for ($i=0; $i<sizeof($pixlist); ++$i) {
    // If it's a string, use it as a separator and print a title
    if (is_string($pixlist[$i])) {
      print "</ul>\n\n";
      print "<h2>$pixlist[$i]</h2>\n";
      $is_open = false;
      continue;
    }

    if (!$is_open) {
      print "<ul class=\"thumbwrap\">\n\n";
      $is_open = true;
    }
    $pic = $pixlist[$i][0];
    $thumbpic = str_replace(".jpg", "T.jpg", $pic);
    $caption = maxtrim($pixlist[$i][1]);
    if (count($pixlist[$i]) == 4) {
      $imgsizestr = " width=" . $pixlist[$i][2] . " height=" . $pixlist[$i][3];
    }
    else {
      $imgsizestr = "";
    }
    if ($caption != "")
      $alt = $caption;
    else
      $alt = maxtrim($title);
    echo <<<EOIMG
<li>
<a href="?pic=$pic">
<img src="$thumbpic" $imgsizestr
 border=0  alt="[ $alt ]">
<span class='caption'>$caption</span>
</a>
</li>

EOIMG;
  }
  print "</ul>\n\n";

  pagefooter($linklist, $prevgallery, $nextgallery);
}

?>
