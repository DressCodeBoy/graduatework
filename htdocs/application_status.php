<?php
session_start();
include("logic/paths.php");
include("logic/database.php");
include("logic/statement.php");
if (!isset($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
} else {
}
$database = new Database();
$db = $database->getConnection();
$statement = new Statement($db);
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
  <title>Узнать статус заявки</title>
</head>

<body>
  <?php
  include("navbar.php");
  $array = $statement->searchUserStatements($_SESSION['user_id']);
  if (empty($array)) header('Location: application_submission.php');
  ?>
  <?php
  if (isset($_POST['sort_date_desc'])) {
    // По убыванию
    function cmp_function_desc($a, $b)
    {
      return ( strtotime($b['statement_date']) <=>  strtotime($a['statement_date']));
    }
    uasort($array, 'cmp_function_desc');
  } elseif (isset($_POST['sort_date'])) {
    // По возрастанию:
    function cmp_function($a, $b)
    {
      return ( strtotime($a['statement_date']) <=>  strtotime($b['statement_date']));
    }
    uasort($array, 'cmp_function');
  } elseif (isset($_POST['sort_non'])) {
    ksort($array);
  }
  ?>
  <div class="container-fluid" style=" padding: 50px;">
    <form action="" id="filter-form" method="post" style="margin: 2px; padding: 2px;" align="center">
      <button type="submit" name="sort_date_desc" class="btn btn-outline-secondary">Сорт. заявки по дате (по убыв.)</button>
      <button type="submit" name="sort_date" class="btn btn-outline-secondary">Сорт. заявки по дате (по возр.)</button>
      <button type="submit" name="sort_non" class="btn btn-outline-secondary">Вернуть в начальный вид</button>
    </form>
    <table class="table">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">Руководитель структурного подразделения</th>
          <th scope="col">Курирующий проректор</th>
          <th scope="col">Начальник ФЭУ</th>
          <th scope="col">Главный бухгалтер</th>
          <th scope="col">Начальник УКЗ</th>
          <th scope="col">Ректор</th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($array as $number => $row) {
        ?>
          <tr>
            <th scope="row"><?php echo $number + 1; ?></th>
            <td>
              <?php
              if ($row['step1']) echo "Утверждено";
              else echo "Неутверждено";
              echo "</br>";
              if ($row['step1_date']) echo $row['step1_date'];
              else echo "";
              echo "</br>";
              if ($row['step1_comment']) echo $row['step1_comment'];
              else echo "";
              ?>
            </td>
            <td>
              <?php
              if ($row['step2']) echo "Утверждено";
              else echo "Неутверждено";
              echo "</br>";
              if ($row['step2_date']) echo $row['step2_date'];
              else echo "";
              echo "</br>";
              if ($row['step2_comment']) echo $row['step2_comment'];
              else echo "";
              ?>
            </td>
            <td>
              <?php
              if ($row['step3']) echo "Утверждено";
              else echo "Неутверждено";
              echo "</br>";
              if ($row['step3_date']) echo $row['step3_date'];
              else echo "";
              echo "</br>";
              if ($row['step3_comment']) echo $row['step3_comment'];
              else echo "";
              ?>
            </td>
            <td>
              <?php
              if ($row['step4']) echo "Утверждено";
              else echo "Неутверждено";
              echo "</br>";
              if ($row['step4_date']) echo $row['step4_date'];
              else echo "";
              echo "</br>";
              if ($row['step4_comment']) echo $row['step4_comment'];
              else echo "";
              ?>
            </td>
            <td>
              <?php
              if ($row['step5']) echo "Утверждено";
              else echo "Неутверждено";
              echo "</br>";
              if ($row['step5_date']) echo $row['step5_date'];
              else echo "";
              echo "</br>";
              if ($row['step5_comment']) echo $row['step5_comment'];
              else echo "";
              ?>
            </td>
            <td>
              <?php
              if ($row['step6']) echo "Утверждено";
              else echo "Неутверждено";
              echo "</br>";
              if ($row['step6_date']) echo $row['step6_date'];
              else echo "";
              echo "</br>";
              if ($row['step6_comment']) echo $row['step6_comment'];
              else echo "";
              ?>
            </td>
          </tr>
        <?php
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- Option 1: Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

  <!-- Option 2: Separate Popper and Bootstrap JS -->
  <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
    -->
</body>

</html>