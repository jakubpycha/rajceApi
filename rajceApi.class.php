<?php

/*

 */

/**
 * Description of rajceApi
 *
 * @author Jakub Pycha <jakub.pycha@seznam.cz>
 */
class rajceApi {

    private $username;
    private $password;
    private $apiUrl;
    private $sessionToken;

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getApiUrl() {
        return $this->apiUrl;
    }

    public function setApiUrl($apiUrl) {
        $this->apiUrl = $apiUrl;
    }

    function __construct($username, $password, $apiUrl) {
        $this->username = $username;
        $this->password = md5($password);
        $this->apiUrl = $apiUrl;
        $this->login();
    }
    
    private static function xsort(&$nodes, $child_name, $order = SORT_ASC) {
      $sort_proxy = array();
      foreach ($nodes as $k => $node) {
        $sort_proxy[$k] = (string) $node->$child_name;
      }
      array_multisort($sort_proxy, $order, $nodes);
    }

    public function getAlbums() {
        
        $albax = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        $albax.="<request>";
        $albax.="<command>getAlbumList</command>";
        $albax.="<parameters>";
        $albax.="<token>" . $this->sessionToken. "</token>";
        $albax.="<columns>";
        $albax.="</columns>";
        $albax.="</parameters>";
        $albax.="</request>";
        
        $request=array('data'=>$albax);
        $albums = $this->sendRequest($request);
        
        $nodes=$albums->xpath('/response/albums/album');
        rajceApi::xsort($nodes, 'startDateInterval', SORT_DESC);
        
        $output=array();
        foreach ($nodes as $album) {
            $albumId=$album->attributes();
            
            $output[]=array("albumId"=>$albumId["id"],"name"=>$album->albumName, "imageUrl"=>$album->url,"thumbUrlBest"=>$album->thumbUrlBest
                    ,"date"=>$album->startDateInterval, "description"=>$album->description);
        }
        
        return $output;
        
        
    }
    
    public function getImagesInAlbums($albumId){
        
        
        $albax = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        $albax.="<request>";
        $albax.="<command>getPhotoList</command>";
        $albax.="<parameters>";
        $albax.="<token>" . $this->sessionToken. "</token>";
        $albax.="<albumID>".html_entity_decode($albumId)."</albumID>";
        $albax.="<columns>";
        $albax.="<column>date</column>";
        $albax.="<column>name</column>";
        $albax.="<column>description</column>";
        $albax.="<column>imageUrl</column>";
        $albax.="<column>thumbUrlBest</column>";
        $albax.="</columns>";
        $albax.="</parameters>";
        $albax.="</request>";
        
        $request=array('data'=>$albax);
        $responseXml=  $this->sendRequest($request);
        
        $nodes=$responseXml->xpath('/response/photos/photo');
        rajceApi::xsort($nodes, 'date', SORT_DESC);
        
        $output=array();
        
        foreach ($nodes as $photos) {
            $output[]=array("date"=>$photos->date, "name"=>$photos->name, "description"=>$photos->description
                    , "imageUrl"=>$photos->imageUrl, "thumbUrlBest"=>$photos->thumbUrlBest);
        }
        
        return $output;
        
    }

    private function sendRequest($fields) {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $response = curl_exec($ch);
        curl_close($ch);

        return simplexml_load_string($response);
    }

    private function login() {

        $x = "";
        $x.="<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        $x.="<request>";
        $x.="<command>login</command>";
        $x.="<parameters>";
        $x.="<login>" . $this->username . "</login> ";
        $x.="<password>" . $this->password . "</password> ";
        $x.="</parameters>";
        $x.="</request>";

        $loginInfo = array('data' => $x);


        $xml = $this->sendRequest($loginInfo);
        $this->sessionToken = $xml->sessionToken;
        $this->sessionToken = htmlspecialchars($this->sessionToken);
    }

}

?>
