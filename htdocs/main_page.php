<?php
session_start();
include("logic/paths.php");
include("logic/users.php");
include("logic/database.php");
if (!isset($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
} elseif (!$_SESSION['is_admin']) {
  header('Location: ' . $_SERVER['HTTP_REFERER']);
}
//соединение с бд
$database = new Database();
$db = $database->getConnection();

//создание экземпляра класса User
$user = new User($db);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous" />
  <!-- My stylesheet-->
  <link rel="stylesheet" href="css/style.css" />
  <!-- шрифты от гугла -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@300&display=swap" rel="stylesheet" />
  <!--AwesomeFont Script-->
  <script src="https://kit.fontawesome.com/b3d038209e.js" crossorigin="anonymous"></script>
  <title>Основная страница</title>
</head>

<body>
  <?php include("navbar.php") ?>
  
  <?php
  // Создание аккаунта
  if (isset($_POST['btn_reg'])) {
    $user_name = $_POST['name'];
    $email = $_POST['email'];
    $user_department = $_POST['user_department'];
    if (isset($_POST['department_head'])) {
      $department_head = 1;
    } else {
      $department_head = 0;
    }
    if (isset($_POST['admin_check'])) {
      $is_admin = 1;
    } else {
      $is_admin = 0;
    }
    if (isset($_POST['vice_rector_check'])) {
      $is_vice_rector = 1;
    } else {
      $is_vice_rector = 0;
    }
    if (isset($_POST['rector_check'])) {
      $is_rector = 1;
    } else {
      $is_rector = 0;
    }
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $query = $db->prepare("SELECT * FROM users WHERE email=:email");
    $query->bindParam("email", $email, PDO::PARAM_STR);
    $query->execute();
    if ($query->rowCount() > 0) {
      echo '<div class="alert alert-danger text-center" role="alert">Этот email адрес уже зарегистрирован!</div>';
    } elseif ($query->rowCount() == 0) {
      $query = $db->prepare("INSERT INTO users(user_name, email, password, user_department, department_head, is_vice_rector, is_rector, is_admin) VALUES (:user_name, :email, :password_hash,:user_department, :department_head, :is_vice_rector, :is_rector, :is_admin)");
      $query->bindParam("user_name", $user_name, PDO::PARAM_STR);
      $query->bindParam("email", $email, PDO::PARAM_STR);
      $query->bindParam("password_hash", $password_hash, PDO::PARAM_STR);
      $query->bindParam("user_department", $user_department, PDO::PARAM_INT);
      $query->bindParam("department_head", $department_head, PDO::PARAM_BOOL);
      $query->bindParam("is_admin", $is_admin, PDO::PARAM_BOOL);
      $query->bindParam("is_rector", $is_rector, PDO::PARAM_BOOL);
      $query->bindParam("is_vice_rector", $is_vice_rector, PDO::PARAM_BOOL);
      $result = $query->execute();
      if ($result) {
        echo '<div class="alert alert-success text-center">Регистрация прошла успешно!</div>';
      } else {
        echo '<div class="alert alert-warning text-center">Неверные данные!</div>';
      }
    }
    header("Location: ".$_SERVER["REQUEST_URI"]);
  }
  // Удаление аккаунта
  if (isset($_POST['btn_del'])) {
    $email = $_POST['email'];
    $query = $db->prepare("SELECT * FROM users WHERE email=:email");
    $query->bindParam("email", $email, PDO::PARAM_STR);
    $query->execute();
    if($query->rowCount() == 0){
      echo '<div class="alert alert-warning text-center">Пользователя с такой почтой не существует</div>';
    }
    else {
      $delete_user = $db->prepare("DELETE FROM users WHERE email =:email");
      $delete_user->bindParam('email', $email, PDO::PARAM_STR);
      $delete_user->execute();
      echo '<div class="alert alert-success text-center">Пользователь удален</div>';
    }
    header("Location: ".$_SERVER["REQUEST_URI"]);
  }
  ?>
  <table align="center">
    <tr>
      <td>
        <div class="container mt-4" id="auth-bloc">
          <h2>Создать нового пользователя</h2>
          <form name="signup-form" action="" method="post">
            <input type="email" class="form-control" name="email" id="email" placeholder="Введите почту" required /><br />
            <input type="password" class="form-control" name="password" id="password" placeholder="Введите пароль" pattern="[a-zA-Z0-9]+" minlength="5" maxlength="32" required />
            <span id="passwordHelpInline" class="form-text">
              Пароль должен быть от 5 до 32 символов из [a-z,A-Z,0-9]. </span><br />
            <input type="text" class="form-control" name="name" id="user_name" placeholder="Введите имя" required /><br />
            <!-- Выпадающий список с выбором отдела -->
            <select class='form-control' name='user_department'>
              <?php
              //чтение названия отдела из БД
              $stmt = $user->read_department();
              //помещение в выпадающий список
              echo "<option disabled selected>Выберите название отдела</option>";

              while ($row_department = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row_department);
                echo "<option value='{$row_department['department_id']}'>{$row_department['department_desc']}</option>";
              }
              ?>
            </select>
            <input class="form-check-input" name="department_head" type="checkbox" value="" id="flexCheckHead">
            <label class="form-check-label text-start" for="flexCheckDefault">
              Глава отдела
            </label>
            <input class="form-check-input" name="admin_check" type="checkbox" value="" id="flexCheckAdmin">
            <label class="form-check-label text-start" for="flexCheckDefault">
              Админ
            </label>
            <input class="form-check-input" name="vice_rector_check" type="checkbox" value="" id="flexCheckViceRector">
            <label class="form-check-label text-start" for="flexCheckDefault">
              Проректор
            </label>
            <input class="form-check-input" name="rector_check" type="checkbox" value="" id="flexCheckRector">
            <label class="form-check-label text-start" for="flexCheckDefault">
              Ректор
            </label>
            </br></br>
            <button name="btn_reg" class="btn btn-success" type="submit">
              Зарегистрировать
            </button>
          </form>
        </div>
      </td>
      <td>
        <div class="container mt-4" id="del-bloc">
          <h2>Удалить пользователя</h2>
          <form name="delete-form" action="" method="post">
            <div class="col text-center">
              <input type="email" class="form-control" name="email" id="email" placeholder="Введите почту" required /><br />
              <button name="btn_del" class="btn btn-success" type="submit">
                Удалить
              </button>
            </div>
          </form>
        </div>
      </td>
    </tr>
  </table>
  <!-- Option 1: Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

  <!-- Option 2: Separate Popper and Bootstrap JS -->
  <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
    -->
</body>

</html>