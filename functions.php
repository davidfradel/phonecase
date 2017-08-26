<?php

$pdo = new PDO('mysql:host=localhost;dbname=iphone', 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

function debug($variable){
    echo '<pre>' . print_r($variable, true) . '</pre>';
}

function getListArticle(){
    global $pdo;

    $aTab = array();

    $query = $pdo->prepare(
        'SELECT image, description, prix FROM presentation ORDER BY prix DESC');

    if ($query->execute()) {
        $aTab = $query->fetchAll(PDO::FETCH_ASSOC);
    }

    return $aTab;
}

function logged_only(){


}

function reconnect_from_cookie(){

    if (session_status() == PHP_SESSION_NONE){
        session_start();
    }


    if (isset($_COOKIE['remember']) && !isset($_SESSION['auth'])){
        $remember_token = $_COOKIE['remember'];
        $parts = explode('==', $remember_token);
        $user_id =$parts[0];
        $req = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $req->execute([$user_id]);
        $user = $req->fetch();

        if ($user){
            $expected = $user_id . '==' . $user->remember_token . sha1($user_id . 'parisisburning');
            if ($expected == $remember_token){
                session_start();
                $_SESSION['auth'] = $user;
                setcookie('remember', $remember_token, time() + 60 * 60 * 24 * 7);
            } else {
                setcookie('remember', NULL, -1);
            }
        } else{
            setcookie('remember', NULL, -1);
        }
    }else{

    }
}


function addUser(){
    global $pdo;

    if(isset($_POST['inscription']))
    {
        $_POST['password'] = md5('+@toast' + $_POST['password']);

        $tab = array(
            'civility' => $_POST['civility'],
            'lastname' => $_POST['lastname'],
            'firstname' => $_POST['firstname'],
            'adress' => $_POST['adress'],
            'zipcode' => $_POST['zipcode'],
            'city' => $_POST['city'],
            'country' => $_POST['country'],
            'email' => $_POST['email'],
            'password' => $_POST['password']
        );

        $var = $pdo->prepare('INSERT INTO `users` (`civility`, `lastname`, `firstname`, `adress`, `zipcode`, `city`, `country`, `email`, `password`) 
VALUES (:civility, :lastname, :firstname, :adress, :zipcode, :city, :country, :email, :password)');
        $var2 = $var->execute($tab);

        echo ('Votre inscription a réussie !');
    }
}

function suppArticle(){
    global $pdo;
//Si l'action de validation a été faite
    if(isset($_POST['supprimer']))
    {


        if(isset($_POST['id_article'])){
            $delete = $pdo->prepare('DELETE FROM article WHERE id_article='.$_POST['id_article'].'');
            $var3 = $delete->execute();
        }else{
            echo('Echec de la suppression');
        }
    }
}

// Fonction a tester après raccordement + appel
function updateArticle(){
    global $pdo;
//Si l'action de validation a été faite
    if(isset($_POST['enregistrer']))
    {

        $update = $pdo->prepare('UPDATE article SET titre = "'.$_POST['title'].'", contenu ="'.$_POST['contenu'].'", categorie ="'.$_POST['categorie'].'" WHERE id_article='.$_POST['idpost'].'');
        $var4 = $update->execute();
    }
}

function fullArticle(){
    global $pdo;

    $idurl = $_GET["code"];

    $aTab2 = array();

    $query = $pdo->prepare(
        'SELECT titre, contenu, categorie, mydate, id_article FROM article WHERE id_article ="'.$idurl.'"');

    if ($query->execute()) {
        $aTab2 = $query->fetchAll(PDO::FETCH_ASSOC);
    }

    return $aTab2;
}


function getcountComment($iIdArticle) {
    global $pdo;

    $query = $pdo->prepare('SELECT id_comment FROM commentaire WHERE id_article=?');

    if ($query->execute([$iIdArticle])) {
        return $query->rowCount();
    }

    return 0;
}

function getListComment(){
    global $pdo;

    $idurl = $_GET["code"];

    $aTab3 = array();

    $query = $pdo->prepare(
        'SELECT pseudo, mydate, comment, id_article FROM commentaire WHERE id_article ="'.$idurl.'"');

    if ($query->execute()) {
        $aTab3 = $query->fetchAll(PDO::FETCH_ASSOC);
    }
    return $aTab3;
}

function addComment(){
    global $pdo;
    $idurl = $_GET["code"];
    //Si l'action de validation a été faite
    if(isset($_POST['commenter']))
    {
        // On récupère les valeurs du formulaire dans un tableau
        $tab4 = array(
            'pseudo' => $_POST['pseudo'],
            'comment' => $_POST['comment'],
            'id_article' => $idurl
        );

// J'indique a ma base de données que je souhaite écrire dedans
        // Les deux points correspondent aux en-tete du tableau
        $var6 = $pdo->prepare('INSERT INTO commentaire (pseudo, comment, id_article, mydate) VALUES (:pseudo, :comment, :id_article, NOW())');
        // Ici est appelé le tableau
        $var7 = $var6->execute($tab4);
    }
}