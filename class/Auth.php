<?php

class Auth{

    private $options = [

        'restriction_msg' => "Vous n'avez pas le droit d'accéder à cette page"
        ];

    private $session;

    public function __construct($session, $options = [])
    {
        $this->options = array_merge($this->options, $options);
        $this->session = $session;
    }

    public function hashPassword($password){
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function register($db, $civility, $lastname, $firstname, $adress, $zipcode, $city, $country, $email, $password){
        $password = $this->hashPassword($password);
        $token = Str::random(60);

        $db->query("INSERT INTO users SET civility = ?, lastname = ?, firstname = ?, adress = ?, zipcode = ?, city = ?, country = ?, email = ?, password = ?, confirmation_token = ?", [
            $civility,
            $lastname,
            $firstname,
            $adress,
            $zipcode,
            $city,
            $country,
            $email,
            $password,
            $token
        ]);

        $user_id = $db->lastInsertId();
        mail($email, "Confirmation de votre compte", "Afin de confirmer votre compte mais de cliquer sur ce lien \n\n http://localhost/coque/index.php?page=confirm&id=$user_id&token=$token");

    }

    public function confirm($db, $user_id, $token){

        $user = $db->query('SELECT * FROM users WHERE id = ?', [$user_id])->fetch();

        if($user && $user->confirmation_token == $token){

            $db->query('UPDATE users SET confirmation_token = NULL, confirmation_date = NOW() WHERE id = ?', [$user_id]);
            $this->session->write('auth', $user);
            return true;
        }

        return false;
    }

    public function restrict(){
        if (!$this->session->read('auth')){
            $this->session->setFlash('danger', $this->options['restrictions_msg']);
            header('Location: login.phtml');
            exit();
        }
    }

    public function user(){
        if ($this->session->read('auth')){
            return false;
        } else{
            return $this->session->read('auth');
        }
    }

    public function connect($user){
        $this->session->write('auth', $user);
    }

    public function connectFromCookie($db){

        if (isset($_COOKIE['remember']) && !$this->user()){
            $remember_token = $_COOKIE['remember'];
            $parts = explode('==', $remember_token);
            $user_id =$parts[0];
            $user = $db->query('SELECT * FROM users WHERE id = ?', [$user_id])->fetch();

            if ($user){
                $expected = $user_id . '==' . $user->remember_token . sha1($user_id . 'parisisburning');
                if ($expected == $remember_token){
                    $this->connect($user);
                    setcookie('remember', $remember_token, time() + 60 * 60 * 24 * 7);
                } else {
                    setcookie('remember', NULL, -1);
                }
            } else{
                setcookie('remember', NULL, -1);
            }
        }
    }

    public function login($db, $login, $password, $remember = false){
        $user = $db->query('SELECT * FROM users WHERE (email = :email) AND confirmation_date IS NOT NULL', ['email' => $login])->fetch();
            if (password_verify($password, $user->password)){
                $this->connect($user);

                    if ($remember){
                        $this->remember($db, $user->id);
                    }
                    return $user;

            } else {
                return false;
            }
    }

    public function remember($db, $user_id){
        $remember_token = Str::random(250);
        $db->query('UPDATE users SET remember_token = ? WHERE id = ?', [$remember_token, $user_id]);
        setcookie('remember', $user_id . '==' . $remember_token . sha1($user_id . 'parisisburning'), time() + 60 * 60 * 24 * 7);

    }

    public function logout(){
        setcookie('remember', NULL, -1);
        $this->session->delete('auth');

    }

    public function resetPassword($db, $email){
        $user = $db->query('SELECT * FROM users WHERE email = ? AND confirmation_date IS NOT NULL', [$email])->fetch();
            if ($user){
                $reset_token = Str::random(60);
                $db->query('UPDATE users SET reset_token = ?, reset_at = NOW() WHERE id = ?', [$reset_token, $user->id]);
                mail($_POST['email'], "Réinitialisation de votre mot de passe", "Afin de confirmer votre compte mais de cliquer sur ce lien \n\n http://localhost/coque/reset.phtml?id={$user->id}&token=$reset_token");
                return $user;

            }
        return false;
    }

    public function checkResetTocken($db, $user_id, $token){
        return $db->query('SELECT * FROM users WHERE id = ? AND reset_token = ? AND reset_at > DATE_SUB(NOW(), INTERVAL 60 MINUTE)', [$user_id, $token])->fetch();

    }

    function getListArticle($db){


        $aTab = $db->query('SELECT id, image, description, prix FROM presentation ORDER BY prix DESC')->fetchAll();



        return $aTab;
    }

    function logged_only(){


    }


    function reconnect_from_cookie($db){

        if (session_status() == PHP_SESSION_NONE){
            session_start();
        }


        if (isset($_COOKIE['remember']) && !isset($_SESSION['auth'])){
            $remember_token = $_COOKIE['remember'];
            $parts = explode('==', $remember_token);
            $user_id =$parts[0];
            $user = $db->query('SELECT * FROM users WHERE id = ?', [$user_id])->fetch();


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




}



