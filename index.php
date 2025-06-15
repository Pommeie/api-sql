<?php

    //Récupération de l'URL et de la méthode API
    $url = decoupe_URL();
    $methode = $_SERVER["REQUEST_METHOD"];

    //Récupération des informations de l'URL
            //À l'avenir, créer une protection s'il n'y a pas toutes les informations
    // URL TYPE = /BDD/TABLE/CHAMP/VALEUR
    //             [0]  [1]   [2]t     [3]v
    // URL TYPE = /BDD/TABLE/VALEUR
    //             [0]  [1]   [2]n
    
    $informations = explode("/",$url);
    $bdd =          $informations[0];
    $table =        $informations[1];
    
    //Connexion à la base de données
    $connexion = new PDO("mysql:host=localhost;dbname=".$bdd, "root", "");


    if (isset($informations[3])){
        if (!is_numeric($informations[3]) && !str_contains($informations[3],",")) {
            $param = "'" . $informations[3] . "'";
        }
        else
        {
            $param = $informations[3];
        }
        $nomId = $informations[2];
    }
    elseif (isset($informations[2]) && $informations[2] != null) {

        $param = $informations[2];

        $nomId = clef_Primaire($table);
    }
    elseif (isset(json_decode(file_get_contents('php://input'))->id)) {
        $param = json_decode(file_get_contents('php://input'))->id;
    }
    else{
        $param = null;
    }

    //Récupération du contenu du body POST
    $body = json_decode(file_get_contents('php://input'),true);
    
    $response = array();

    //Tests de la méthode utilisée
    if (strtoupper($methode) == "GET") {
        //Vérifie si plusieurs données sont demandées dans les paramètres
        if (str_contains($param,",")){
            $liste = explode(",", $param);
            foreach ($liste as $objet){
                GET($table, "'".$objet."'");
            }
        }
        else{
            GET($table, $param);
        }
    }
    elseif (strtoupper($methode) == "POST") {
        foreach ($body as $objet){
            POST($table, $objet);
        }
    }
    elseif (strtoupper($methode) == "PUT" || strtoupper($methode) == "PATCH") {
        foreach ($body as $objet) {
            PUT($table,$objet);
        }
    }
    elseif (strtoupper($methode) == "DELETE") {
        //Vérifie si plusieurs données sont demandées dans les paramètres
        if (str_contains($param,",")){
            $liste = explode(",", $param);
            foreach ($liste as $objet){
                DELETE($table, "'".$objet."'");
            }
        }
        else{
            DELETE($table, $param);
        }
    }
    else {
        header("HTTP/1.0 405 Method Not Allowed");
    }

    //Fonctions API
    function GET($table, $param = null){
        global $connexion, $response, $nomId;

        //Requête SQL
        $requete = "SELECT * FROM ". $table;
        if ($param != null) {
            $requete = $requete . " WHERE " . $nomId . " = " . $param;
        }
        $resultat = $connexion->query($requete);

        //Récupération des colonnes
        for ($i=0; $i < $resultat->columnCount(); $i++) { 
            $colonnes[] = $resultat->getColumnMeta($i)['name'];
        }

        //Création de la réponse JSON
        
        while ($lignes = $resultat->fetch(PDO::FETCH_ASSOC)) {
            $response[]= array_map( function($ligne) { global $colonnes; return $ligne .$colonnes;}, $lignes);
        }

        //Envoie de la réponse
        header('Content-Type: application/json; charset=utf-8');
        
    }
    
    function POST($table, $objet){
        global $connexion, $nomId;

        //Création des variables
        foreach ($objet as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }
        
        //Requête SQL
        $requete = "INSERT INTO ". $table .' '. parenthese($keys,'`') ." VALUES ". parenthese($values,"'");
        $requete = $connexion->query($requete);
        echo $requete;
        $id = $connexion->lastInsertId();        

        $nomId = clef_Primaire($table);

        echo $id;
        GET($table, "".$id."");
        header('Content-Type: application/json');
    }

    function DELETE($table, $param){
        global $connexion, $nomId;

        GET($table,$param);
        $requete = "DELETE FROM ". $table . " WHERE ". $nomId ." = " . $param;
        $resultat = $connexion->query($requete);

        if ($resultat){

        }
        else {
            
        }
        //Envoie de la réponse
        header('Content-Type: application/json; charset=utf-8');
    }

    function PUT($table, $objet){
        global $connexion;

        $clef = clef_Primaire($table);
        $update = "";
        foreach ($objet as $key => $value) {
            $update = $update . '`' . $key . '`="' . $value . '", ';
            if ($key == $clef) {
                $identifiant = $value;
            }
        }
        $update = substr($update, 0, strlen($update) - 2);
        
        $requete = "UPDATE ". $table. " SET ". $update ." WHERE ". $clef ." = ". $identifiant;
        $resultat = $connexion->query($requete);

        GET($table,$identifiant);

    }


    //Fonctions
    function parenthese($liste, $separateur){
        //Renvoie une chaîne de caractères utilisable pour lister des éléments dans une requête SQL
        $string = "(";
        for ($i=0; $i < count($liste)-1; $i++) { 
            $string = $string.$separateur. $liste[$i].$separateur.',';
        }
        $string = $string .$separateur.$liste[count($liste)-1].$separateur.")";

        return $string;
    }
    
    function decoupe_URL(){
        //Renvoie l'URL à partir du de la racine du fichier
        $decoupe = explode("/", $_SERVER['SCRIPT_NAME']);
        $ur = "";
        for ($i=0; $i < count($decoupe) - 1; $i++) { 
            $ur = $ur . $decoupe[$i] . "/";
        }

        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return substr($url, -(strlen($url) - strlen($ur)));
    }

    function clef_Primaire($table){
        global $connexion;

        $requete = $connexion->query("DESCRIBE " . $table);

        while ($row = $requete->fetch()) {
            if (!empty($row['Key']) && ($row['Extra'] === 'auto_increment' ||  $row['Key'] === 'PRI')) {
                $clef = $row['Field'];
                break;
            }
        }

        return $clef;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
