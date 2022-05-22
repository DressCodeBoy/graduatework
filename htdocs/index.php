<?php
session_start();
include("logic/paths.php");
include("logic/users.php");
include("logic/database.php");

//соединение с бд
$database = new Database();
$db = $database->getConnection();

//создание экземпляра класса User
$user = new User($db);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>Регистрация / Авторизация</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@300&display=swap" rel="stylesheet" />
</head>

<body>
  
  <!-- Конец регистрации -->
  <!-- Авторизация -->
  <?php
  if (isset($_POST['btn_auth'])) {
    $login_auth = $_POST['login_auth'];
    $password_auth = $_POST['password_auth'];
    $query = $db->prepare("SELECT * FROM users WHERE email=:email");
    $query->bindParam("email", $login_auth, PDO::PARAM_STR);
    $query->execute();
    $result_auth = $query->fetch(PDO::FETCH_ASSOC);
    if (empty($result_auth)) {
      echo '<div class="alert alert-warning text-center">Неверные пароль или имя пользователя!</div>';
    } else {
      if (password_verify($password_auth, $result_auth['password'])) {
        $_SESSION['user_id'] = $result_auth['user_id'];
        $_SESSION['user_name'] = $result_auth['user_name'];
        $dep_id = $result_auth['user_department'];
        $get_department = $db->prepare("SELECT department_desc FROM departments WHERE department_id = :department_id");
        $get_department->bindParam("department_id", $dep_id, PDO::PARAM_INT);
        $get_department->execute();
        $department = $get_department->fetch(PDO::FETCH_ASSOC);
        $_SESSION['user_department_id'] = $result_auth['user_department'];
        $_SESSION['user_department'] = $department['department_desc'];
        $_SESSION['department_head'] = $result_auth['department_head'];
        $_SESSION['is_admin'] = $result_auth['is_admin'];
        $_SESSION['is_vice_rector'] = $result_auth['is_vice_rector'];
        $_SESSION['is_rector'] = $result_auth['is_rector'];
        echo '<div class="alert alert-success text-center">Поздравляем, вы прошли авторизацию!</div>';
        if($_SESSION['is_admin']) {header('Location: main_page.php');}
        elseif($_SESSION['department_head'] || $_SESSION['vice_rector'] || $_SESSION['is_rector'])
        {header('Location: application_approval.php');}
        else {header('Location: application_submission.php');}
      } else {
        echo '<div class="alert alert-warning text-center"> Неверные имя пользователя или пароль!</div>';
        header("Location: ".$_SERVER["REQUEST_URI"]);
        exit();
      }
    }
  }
  ?>
  <!-- Конец авторизации -->
  <table align="center">
    <tr>
      <td>
        <div class="container mt-4" id="auth-bloc">
          <h1>Авторизация</h1>
          <form name="signin-form" action="" method="post">
            <input type="text" class="form-control" name="login_auth" id="login" placeholder="Введите логин (почта)" required /><br />
            <input type="password" class="form-control" name="password_auth" id="password_auth" placeholder="Введите пароль" required /><br />
            <button name="btn_auth" class="btn btn-success" type="submit">Войти</button>
          </form>
        </div>
      </td>
    </tr>
  </table>
</body>

</html>