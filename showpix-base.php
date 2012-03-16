<?php
  /* Display an image, with comment and links to the previous/next
   * image.
   * Copyright 2004 Akkana Peck.
   * You are welcome to use, modify and share this software
   * under the terms of the GPL.
   */
  $exifpic = $_GET['exif'];
  if ($exifpic) { showexif($exifpic); return; }

  $pic = $_GET['pic'];

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

    if ($i > 0) $lastpic = $pixlist[$i-1][0];
    if ($i < sizeof($pixlist)-1)
      $nextpic = $pixlist[$i+1][0];
  }

  function navbar($last, $next) {
    print "<ul class=\"picnav\">\n";
    if ($last != "")
      print("<li><a href=\"showpix.php?pic=$last\">Prev</a></li>\n");
    else print("<li>No Prev</li>\n");

    print("<li><a href=\"./\">Index</a></li>\n");

    if ($next != "")
      print("<li><a href=\"showpix.php?pic=$next\">Next</a></li>\n");
    else print("<li>[No Next]</li>\n");
    print "</ul>";
  }

  function showexif($pic) {
    $thumb = str_replace(".jpg", "T.jpg", $pic);
    print("<img align=right src=\"$thumb\" alt=\"[EXIF]\">\n");
    print("<h2>EXIF for $pic</h2>\n");
    print("<p><a href=\"showpix.php?pic=$pic\">Back to $pic</a>\n");

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
    print("<p><a href=\"showpix.php?pic=$pic\">Back to $pic</a>\n");
  }

echo <<<EOHDR
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<link rel="stylesheet" type="text/css" href="/css/gallerypic.css" />
EOHDR;

  if (isset($banner)) {
    print $banner;
  }

  if (isset($title)) {
    print "<title>$title</title>\n";
  } else {
    print "<title>$thispic</title>\n";
  }

  print "<head>\n<body>\n\n";

  if (isset($title)) {
    print "<h1>$title</h1>\n";
  }

 navbar($lastpic, $nextpic);
 print("<p>$comment\n<p>\n");
 print("<img src=\"$thispic\" $imagesize alt=\"[$thispic]\">\n");
 navbar($lastpic, $nextpic);

 print("<p>&nbsp; &nbsp; &nbsp; &nbsp; ");
 print("<a href=\"showpix.php?exif=$thispic\">EXIF</a>\n");

 if (file_exists("fullsize/$thispic")) {
  print("<p>&nbsp; &nbsp; &nbsp; &nbsp; ");
  print("<a href=\"fullsize/$thispic\">(full size)</a>\n");
 }

echo <<<EOTAIL
<p>

<hr>

<p>
Copyright &copy; Akkana Peck.
<p>
<a href="/photo.html">Akkana's Photo Page</a><br>
<a href="/">Shallow Sky Home</a><br>
</body>
</html>
EOTAIL;
?>
