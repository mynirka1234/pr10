<?php
    session_start();
    require_once("../settings/connect_datebase.php");
    require_once("../libs/autoload.php");
    
    $login = $_POST['login'];
    if(isset($_POST["g-recaptcha-response"]) == false){
        echo "Нет пройденной проверки";
        exit;
    }
    
    $Secret = "6Lev6nYsAAAAAFoTu6HYarMONEmMi53zKIWMHoLH";
    $Recaptcha = new \ReCaptcha\ReCaptcha($Secret);

    $Response = $Recaptcha->verify($_POST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);

    if($Response->isSuccess()){
        // ищем пользователя
        $query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."';");
        
        $id = -1;
        if($user_read = $query_user->fetch_row()) {
            // создаём новый пароль
            $id = $user_read[0];
        }
        
        function PasswordGeneration() {
            // создаём пароль
            $chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP"; // матрица
            $max=10; // количество
            $size=StrLen($chars)-1;
            $password="";
            
            while($max--) {
                $password.=$chars[rand(0,$size)];
            }
            
            return $password;
        }
        
        if($id != 0) {
            $password = PasswordGeneration();;
            $query_password = $mysqli->query("SELECT * FROM `users` WHERE `password`= '".md5($password)."';");
            while($password_read = $query_password->fetch_row()) {
                $password = PasswordGeneration();
            }
            $mysqli->query("UPDATE `users` SET `password`='".md5($password)."' WHERE `login` = '".$login."'");
            //mail($login, 'Безопасность web-приложений КГАПОУ "Авиатехникум"', "Ваш пароль был только что изменён. Новый пароль: ".$password);
        }
        
        echo $id;
    } else {
        echo "Пользователь не распознан";
        exit;
    }
?>