<?php
/*
 * Copyright (C) 2012 Sven Karsten Greiner
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

error_reporting(0);

session_start();

include('config.php');

function generateImageUrl($album, $image = null, $html = true) {
    $url = '?album=' . rawurlencode($album);
    if ($image != null) {
        $url .= ($html ? '&amp;' : '&') . 'image=' . rawurlencode($image);
    }
    return $url;
}

function directoryScanner($directory) {
    $result = array();
    $containsImage = false;
    $content = scandir($directory);
    
    foreach ($content as $entry) {
        if (strstr($entry, ".jpg")
                || strstr($entry, ".jpeg")
                || strstr($entry, ".png")
                || strstr($entry, ".gif")) {
            $containsImage = true;
        }
        
        if (is_dir($directory . '/' . $entry) && substr($entry, 0, 1) != ".") {
            $result = $result + directoryScanner($directory . '/' . $entry);
        }
    }
    
    if ($containsImage) {
        $title = substr(strrchr("/" . $directory, "/"), 1);
        $result[$title] = $directory;
    }
    
    return $result;
}

// Redirect to selected album
if (isset($_POST['album'])) {
    header('Location: ' . generateImageUrl($_POST['album']));
    die();
}

$directories = $config['autodetect'] ? directoryScanner($config['autodetectroot']) : $config['albums'];

$album = $_GET['album'];
$image = $_GET['image'];

$random = isset($_GET['random']) || ($album == null && $image == null);
$startpage = !isset($_GET['random']) && $album == null && $image == null;

$smallSize = (boolean) $_SESSION['smallsize'];

// Redirect to same image after changing size
// Case album == null is handled by next GET
if (isset($_GET['togglesize'])) {
    $_SESSION['smallsize'] = !$smallSize;
    header('Location: ' . generateImageUrl($album, $image, false));
    die();
}

// Validate given album or use random one
if ($random) {
    $album = array_rand($directories);
} elseif (!array_key_exists($album, $directories)) {
    $random = true;
    $album = array_rand($directories);
}

// Scan directory for image files
$files = scandir($directories[$album], $config['sortdescending'] ? 1 : 0);
$images = array();
foreach ($files as $file) {
    $f = strtolower($file);
    if (!is_dir($directories[$album] . '/' . $file)
            || strstr($f, ".jpg")
            || strstr($f, ".jpeg")
            || strstr($f, ".png")
            || strstr($f, ".gif")) {
        $images[] = $file;
    }
}

// Validate given image or use first or random one in album
if ($random) {
    $image = $images[array_rand($images)];
} elseif ($image == null) {
    $image = $images[0];
} elseif (!in_array($image, $images)) {
    $random = true;
    $image = $images[array_rand($images)];
}

// Redirect to new image
if ($random && !$startpage) {
    header('Location: ' . generateImageUrl($album, $image, false));
    die();
}

// Calculate paging boundaries
$currentIndex = array_search($image, $images);
$pagingStart = floor($currentIndex / $config['paginginterval']) * $config['paginginterval'];
$neighbors = array_slice($images, $pagingStart, $config['paginginterval']);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <?php
    echo '<title>';
    echo $config['sitetitle'];
    if (isset($_GET['info'])) {
        echo ' :: Info';
    } elseif (!$startpage) {
        echo ' :: ' . htmlspecialchars($image);
    }
    echo '</title>';
    
    if ($config['metadescription'] != "") {
        echo '<meta name="description" content="' . $config['metadescription'] . '" />';
    }
    
    if ($config['metakeywords'] != "") {
        echo '<meta name="keywords" content="' . $config['metakeywords'] . '" />';
    }
    ?>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <link rel="stylesheet" type="text/css" href="style.css" />
    <?php if ($smallSize)  echo '<link rel="stylesheet" type="text/css" href="style_small.css" />'; ?>
</head>
<body>
<?php
if (isset($_GET['info'])) {
    echo '<div id="infobox"><a href="./" title="Home" class="home"><img src="home.png" /></a>' . $config['info'] . '</div>';
} else {
?>
    <table border="0" cellspacing="0" cellpadding="0" id="sitecontainer">
        <tr>
            <td id="sidebarcontainer">
                <div id="sidebar">
                    <form action="" method="post" id="albumnav">
                        <input type="submit" name="albumselect" value="" />
                        <div class="select">
                            <select name="album" size="1" onchange="this.form.submit()">
                                <?php
                                foreach ($directories as $dir => $path) {
                                    $selected = ($dir == $album) ? 'selected="selected"' : '';
                                    $dir = htmlspecialchars($dir);
                                    echo "<option $selected>$dir</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </form>
                    <div id="thumbnav">
                        <?php
                        $albumUrl = str_replace('%2F', '/', rawurlencode($directories[$album]));
                        foreach ($neighbors as $neighbor) {
                            $href = generateImageUrl($album, $neighbor);
                            $src = $albumUrl . '/' . rawurlencode($neighbor);
                            $current = ($image == $neighbor) ? 'class="current"' : '';
                            echo "<a href=\"$href\"><img src=\"$src\" alt=\"\" $current /></a>";
                        }
                        ?>
                    </div>
                    <div id="thumbnav-paging">
                        <?php
                        if ($pagingStart > 0) {
                            $href = generateImageUrl($album, $images[$pagingStart - 1]);
                            echo "<a href=\"$href\" class=\"back\"><img src=\"arrow-left.png\" alt=\"prev\" /></a>";
                        }
                        if ($pagingStart + $config['paginginterval'] < count($images)) {
                            $href = generateImageUrl($album, $images[$pagingStart + $config['paginginterval']]);
                            echo "<a href=\"$href\" class=\"next\"><img src=\"arrow-right.png\" alt=\"next\" /></a>";
                        }
                        
                        $currentPageIndex = floor($currentIndex / $config['paginginterval']);
                        $pageCount = floor(count($images) / $config['paginginterval']) + 1;
                        echo "<div>";
                        for ($i = 0; $i < $pageCount; $i++) {
                            if ($i == $currentPageIndex) {
                                echo '<img src="bullet-light.png" alt="O" /> ';
                            } elseif ($i < $currentPageIndex) {
                                $index = $i * $config['paginginterval'] + $config['paginginterval'] - 1;
                                echo '<a href="' . generateImageUrl($album, $images[$index]) . '"><img src="bullet-dark.png" alt="O" /></a> ';
                            } else {
                                echo '<a href="' . generateImageUrl($album, $images[$i * $config['paginginterval']]) . '"><img src="bullet-dark.png" alt="O" /></a> ';
                            }
                        }
                        echo "</div>";
                        ?>
                    </div>
                    <div id="info">
                        <a href="?random" title="Random"><img src="random.png" alt="Random" /></a>
                        <a href="<?php echo generateImageUrl($album, $image); ?>&amp;togglesize" title="Toggle size"><img src="size.png" alt="Toggle size" /></a>
                        <?php
                        if ($config['homeurl'] != "") {
                            echo '<a href="' . $config['homeurl'] . '" title="Home"><img src="home.png" alt="Home" /></a>';
                        }
                        ?>
                        <a href="?info" title="Info"><img src="info.png" alt="Info" /></a>
                    </div>
                </div>
            </td>
            <td id="imagecontainer">
                <div style="position: relative;">
                    <?php
                    if ($currentIndex > 0) {
                        $href = generateImageUrl($album, $images[$currentIndex - 1]);
                        echo "<a href=\"$href\" class=\"navi back\"></a>";
                    }
                    if ($currentIndex + 1 < count($images)) {
                        $href = generateImageUrl($album, $images[$currentIndex + 1]);
                        echo "<a href=\"$href\" class=\"navi next\"></a>";
                    }
                    ?>
                    <img src="<?php echo str_replace('%2F', '/', rawurlencode($directories[$album])) . '/' . rawurlencode($image); ?>" alt="Image" <?php if ($config['imageborder']) echo 'class="imageborder"'; ?> />
                </div>
            </td>
        </tr>
    </table>
    <div style="display: none;">
        <?php
        foreach ($directories as $dir => $path) {
            $href = generateImageUrl($dir);
            $title = htmlspecialchars($dir);
            echo "<a href=\"$href\">$title</a>\n";
        }
        ?>
    </div>
<?php
}
?>
</body>
</html>