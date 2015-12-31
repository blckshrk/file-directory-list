<?php
/*

Free PHP File Directory Listing Script - Version 1.6

The MIT License (MIT)

Copyright (c) 2015 Hal Gatewood

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.


*** OPTIONS ***/

// TITLE OF PAGE
$title = "Meetchr nightly builds";

// SORT BY
$sort_by = "name_desc"; // options: name_asc, name_desc, date_asc, date_desc

// ICON URL
$icon_url = "https://dl.dropbox.com/s/3iiyccl0qo2hx6h/icons.png";

// TOGGLE SUB FOLDERS, SET TO false IF YOU WANT OFF
$toggle_sub_folders = true;


// SET TITLE BASED ON FOLDER NAME, IF NOT SET ABOVE
if (!$title) {
    $title = cleanTitle(basename(dirname(__FILE__)));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo $title; ?></title>
    <link href="https://fonts.googleapis.com/css?family=Lato:700,400,300,300italic,700italic" rel="stylesheet" type="text/css" />
    <style>
        *,
        *:before,
        *:after {
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
        }
        body {
            font-family: "Lato", "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
            font-weight: 400;
            font-size: 1.8em;
            line-height: 1em;
            padding: 0;
            margin: 0;
            background: #f5f5f5;
        }
        .wrap {
            margin: 60px 80px;
            background: white;
            box-shadow: 0 0 2px #ccc;
        }
        .title-icon {
            max-width: 200px;
            margin: 20px auto 10px auto;
            display: block;
        }
        h1 {
            text-align: center;
            margin: 5px 0 40px 0;
            font-size: 2em;
            font-weight: bold;
            color: #6C51C3;
        }
        a {
            color: #399ae5;
            text-decoration: none;
        }
        a:hover {
            color: #206ba4;
            text-decoration: none;
        }
        .block {
            clear: both;
            min-height: 70px;
            border-top: solid 1px #ECE9E9;
        }
        .block:first-child {
            border: none;
        }
        .block .img {
            width: 70px;
            height: 70px;
            display: block;
            float: left;
            margin: 10px 20px 10px auto;
            background: transparent url(<?php echo $icon_url; ?>) no-repeat 0 0;
        }
        .block .date {
            margin-top: 4px;
            font-size: 0.7em;
            color: #666;
        }
        .block a {
            display: block;
            padding: 25px 35px;
            transition: all 0.35s;
        }
        .block a:hover {
            text-decoration: none;
            background: #efefef;
        }
        .dir {
            background-position: -70px 0 !important;
        }
        .apk {
            background-position: -140px 0 !important;
        }
        .sub {
            margin-left: 20px;
            border-left: solid 1px #ECE9E9;
            display: none;
        }

        @media only screen and (min-width: 1024px) {
            body {
                font-size: 1.4em;
            }

            .wrap {
                max-width: 800px;
                margin-left: auto;
                margin-right: auto;
            }

            .block .img {
                margin: auto 20px auto auto;
            }
        }
    </style>
</head>
<body>
<img src="https://dl.dropbox.com/s/zzfvk7377fdpdcn/meetch_icone-transp.png" class="title-icon">
<h1><?php echo $title ?></h1>
<div class="wrap">
<?php

// FUNCTIONS TO MAKE THE MAGIC HAPPEN, BEST TO LEAVE THESE ALONE
function cleanTitle($title) {
    return ucwords(str_replace(array("-", "_"), " ", $title));
}

function getFileExt($filename) {
    return substr(strrchr($filename, '.'), 1);
}

function format_size($file) {
    $bytes = filesize($file);

    if ($bytes < 1024) {
        return $bytes.'b';
    } elseif ($bytes < 1048576) {
        return round($bytes / 1024, 2).'kb';
    } elseif ($bytes < 1073741824) {
        return round($bytes / 1048576, 2).'mb';
    } elseif ($bytes < 1099511627776) {
        return round($bytes / 1073741824, 2).'gb';
    } else {
        return round($bytes / 1099511627776, 2).'tb';
    }
}


// SHOW THE MEDIA BLOCK
function display_block($file) {
    global $ignore_file_list, $ignore_ext_list;

    $file_ext = getFileExt($file);
    if (!$file_ext && is_dir($file)) {
        $file_ext = "dir";
    }

    $file_name = basename($file);
    $file_size = format_size($file);
    $file_date = date("D. F jS, Y - H\hi", filemtime($file));

    echo <<<EOT
<div class="block">
    <a href="./{$file}" class="{$file_ext}">
        <div class="img {$file_ext}">&nbsp;</div>
        <div class="name">
            <div class="file"> {$file_name} </div>
            <div class="date">Size: {$file_size} <br>
            Last modified: {$file_date}</div>
        </div>
    </a>
</div>
EOT;
}


// RECURSIVE FUNCTION TO BUILD THE BLOCKS
function build_blocks($items, $folder) {
    global $ignore_file_list, $ignore_ext_list, $sort_by, $toggle_sub_folders;

    $objects = array();
    $objects['directories'] = array();
    $objects['files'] = array();

    foreach ($items as $c => $item) {
        if ($item == ".." || $item == ".") {
            continue;
        }

        if ($folder) {
            $item = "$folder/$item";
        }

        $file_ext = getFileExt($item);

        // IGNORE EXT
        if ($file_ext != "apk") {
            continue;
        }

        // DIRECTORIES
        if (is_dir($item)) {
            $objects['directories'][] = $item;
            continue;
        }

        // FILE DATE
        $file_time = date("U", filemtime($item));

        // FILES
        $objects['files'][$file_time . "-" . $item] = $item;
    }

    foreach ($objects['directories'] as $c => $file) {
        display_block( $file );

        if ($toggle_sub_folders) {
            $sub_items = (array) scandir( $file );
            if ($sub_items) {
                echo "<div class='sub' data-folder=\"$file\">";
                build_blocks( $sub_items, $file );
                echo "</div>";
            }
        }
    }

    // SORT BEFORE LOOP
    if ($sort_by == "date_asc") {
        ksort($objects['files']);
    }
    elseif ($sort_by == "date_desc") {
        krsort($objects['files']);
    }
    elseif ($sort_by == "name_asc") {
        natsort($objects['files']);
    }
    elseif ($sort_by == "name_desc") {
        arsort($objects['files']);
    }

    foreach ($objects['files'] as $t => $file) {
        $fileExt = getFileExt($file);

        display_block($file);
    }
}

// GET THE BLOCKS STARTED, FALSE TO INDICATE MAIN FOLDER
$items = scandir(dirname(__FILE__));
build_blocks( $items, false );
?>

<?php if ($toggle_sub_folders) { ?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $('a.dir').on('click', function (e) {
            e.preventDefault();
            $('.sub[data-folder="' + $(this).attr('href') + '"]').slideToggle();
        });
    });
</script>
<?php } ?>
</div>
</body>
</html>