<?php
session_start();
include("logic/paths.php");
include("logic/database.php");
include("logic/statement.php");
if (!isset($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
} elseif ($_SESSION['department_head'] == 1 || $_SESSION['is_vice_rector'] == 1 || $_SESSION['is_rector'] == 1 || $_SESSION['is_admin'] == 1) {
} else header('Location: application_submission.php');
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
  <title>Заявки на утверждение</title>
</head>

<body>
  <?php
  include("navbar.php");
  $feu = 1;
  if ($_SESSION['department_head'] && !($_SESSION['user_department'] == "ФЭУ" || $_SESSION['user_department'] == "Бухгалтерия" || $_SESSION['user_department'] == "УКЗ")) {
    $approval_step = 1;
    $array_statements = $statement->showStatementsForDepartmentHead($_SESSION['user_department']);
    $array_data = $array_statements->fetchAll(PDO::FETCH_ASSOC);
    if($array_data) {$array_data = $array_data[0];}
  } elseif ($_SESSION['is_vice_rector']) {
    $approval_step = 2;
    $array_statements = $statement->showStatementsForViceRector($_SESSION['user_department']);
    $array_data = $array_statements->fetchAll(PDO::FETCH_ASSOC);
    if($array_data) {$array_data = $array_data[0];}
  } elseif ($_SESSION['department_head'] && $_SESSION['user_department'] == "ФЭУ") {
    $approval_step = 3;
    $array_statements = $statement->showStatementsForFeuHead($_SESSION['user_department']);
    $array_data = $array_statements->fetchAll(PDO::FETCH_ASSOC);
    if($array_data) {$array_data = $array_data[0];}
  } elseif ($_SESSION['department_head'] && $_SESSION['user_department'] == "Бухгалтерия") {
    $approval_step = 4;
    $array_statements = $statement->showStatementsForAccountantHead($_SESSION['user_department']);
    $array_data = $array_statements->fetchAll(PDO::FETCH_ASSOC);
    if($array_data) {$array_data = $array_data[0];}
  } elseif ($_SESSION['department_head'] && $_SESSION['user_department'] == "УКЗ") {
    $approval_step = 5;
    $array_statements = $statement->showStatementsForUKZHead($_SESSION['user_department']);
    $array_data = $array_statements->fetchAll(PDO::FETCH_ASSOC);
    if($array_data) {$array_data = $array_data[0];}
  } else {
    $approval_step = 6;
    $array_statements = $statement->showStatementsForRector($_SESSION['user_department']);
    $array_data = $array_statements->fetchAll(PDO::FETCH_ASSOC);
    if($array_data) {$array_data = $array_data[0];}
  }
  ?>
  <?php if ($array_data) {

    $nmp_path = $array_data['attach_path1'];
    $tz_path = $array_data['attach_path2'];
    $statement_id = $array_data['statement_id'];
    if (isset($_POST['btn_send_approval'])) {
      if (isset($_POST['approval_check'])) {
        $is_approval = 1;
      } else $is_approval = 0;
      $comment = $_POST['comment'];
      $statement->addApprovalAndComment($approval_step, $is_approval, $comment, $statement_id);
      $user_email = $statement->searchWorkerEmail($statement_id);
      mail(to:"$user_email", subject:'Статус заявки', message:'Статус вашей заявки обновился, зайдите на сайт, чтобы узнать подробнее');
      $all_statement_info = $statement->getAllStatementInfo($statement_id);
      $all_statement_info = $all_statement_info->fetch(PDO::FETCH_ASSOC);
      file_put_contents("../files/statement_data_$statement_id.json", json_encode($all_statement_info, JSON_UNESCAPED_UNICODE));
      header("Location: ".$_SERVER["REQUEST_URI"]);
    }
  ?>
    <form action="" method="post" name="application_changes">
      <h2 class="text-center">Завка на размещение заказа</h2>
      <div class="row">
        <p class="text-start">Дата заявки: <?php echo $array_data['statement_date'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Наименование структурного подразделения: <?php echo $array_data['department_desc'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Предмет договора: <?php echo $array_data['subject_description'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Цель проведения закупки: <?php echo $array_data['purchase_purpose'] ?></p>
      </div>
      <h4 class="text-center">Описание закупки</h4>
      <div class="row">
        <p class="text-start">Название товара: <?php echo $array_data['product_name'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">ОКПД2 ТРУ: <?php echo $array_data['okpd2'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Количество: <?php echo $array_data['product_count'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Единицы измерения: <?php echo $array_data['unit_name'] ?></p>
      </div>
      <h5 class="text-start">Коммерческие предложения</h5>
      <h5 class="text-start">1.</h5>
      <div class="row">
        <p class="text-start">Цена товара за единицу ТРУ с учетом НДС, руб.: <?php echo $array_data['price1_for1'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Общая сумма ТРУ с учетом НДС, руб.: <?php echo $array_data['price1'] ?></p>
      </div>
      <h5 class="text-start">2.</h5>
      <div class="row">
        <p class="text-start">Цена товара за единицу ТРУ с учетом НДС, руб.: <?php echo $array_data['price2_for1'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Общая сумма ТРУ с учетом НДС, руб.: <?php echo $array_data['price2'] ?></p>
      </div>
      <h5 class="text-start">3.</h5>
      <div class="row">
        <p class="text-start">Цена товара за единицу ТРУ с учетом НДС, руб.: <?php echo $array_data['price3_for1'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Общая сумма ТРУ с учетом НДС, руб.: <?php echo $array_data['price3'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Срок гарантии / гарантия качества ТРУ: <?php echo $array_data['warranty_period'] ?></p>
      </div>
      <hr align="center" size="5" width="50%">
      <div class="row">
        <p class="text-start">Итого (первое предложение): <?php echo $array_data['final_price1'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Итого (второе предложение): <?php echo $array_data['final_price2'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Итого (третье предложение): <?php echo $array_data['final_price3'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Начальная максимальная цена договора составляет: <?php echo $array_data['NMP'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">и включает в себя <?php echo $array_data['nmp_var_desc'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Метод определения начальной (максимальной) цены договора: <?php echo $array_data['NMP_method'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Источник финансирования: <?php echo $array_data['source_finance'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Код вида расходов (КВР): <?php echo $array_data['EXT'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Сроки и условия оплаты: <?php echo $array_data['purch_var_desc'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Желаемый срок поставки ТРУ (точное количество дней): <?php echo $array_data['delivery_period'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Место поставки ТРУ: <?php echo $array_data['deliv_place_desc'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Способ определения поставщика: <?php echo $array_data['supplier_determ'] ?></p>
      </div>
      <div class="row">
        <p class="text-start">Обоснование начальной максимальной цены договора: </p>
        <a href="#" target="_blank" download="<?php echo $nmp_path ?>">Загрузить</a>
      </div>
      </br>
      <div class="row">
        <p class="text-start">Техническое задание:</p>
        <a href="#" target="_blank" download="<?php echo $tz_path ?>">Загрузить</a>
      </div>
      </br>
      <div class="row">
        <label class="form-check-label text-start" for="flexCheckDefault">
          Утвердить <input class="form-check-input" name="approval_check" type="checkbox" value="" id="flexCheckRector">
        </label>
      </div>
      <div class="row">
        <p class="text-start">Добавить комментарий:</p>
        <input type="text" name="comment" style="width: 900px;">
      </div>
      <div class="text-center">
        <button name="btn_send_approval" class="btn btn-success" type="submit" style="margin-top: 50px;">Отправить</button>
      </div>
    </form>
  <?php } else echo "На текущий момент нет заявок для рассмотрения";
  ?>

  <!-- Option 1: Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

  <!-- Option 2: Separate Popper and Bootstrap JS -->
  <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
    -->
</body>

</html>