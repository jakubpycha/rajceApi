rajceApi
========

Jedná se o api k fotogalerii na webu rajce.idnes.cz
Api poskytuje základní operace získávání dat. 
O použití více napoví následující blok kódu.

    require_once  './includes/rajceApi.class.php';
    $rajceApi=new rajceApi("username", "password", RAJCE_API_URL);
    $nodes=$rajceApi->getAlbums(); 
    foreach ($nodes as $fotoalbum) {   
        echo "<h1>".$fotoalbum["name"]."  id:".$fotoalbum["albumId"]."</h1>";   
        $images=$rajceApi->getImagesInAlbums($fotoalbum["albumId"]);
        foreach ($images as $image) {
            echo "<div class='fotka'><a href='" . $image["imageUrl"] . "'><img src='" . $image["thumbUrlBest"] . "' /><br />" . $image["name"] . "</a></div>";
        }
    }
