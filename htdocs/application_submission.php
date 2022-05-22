<?php
session_start();
include("logic/paths.php");
include("logic/database.php");
include("logic/statement.php");
if (!isset($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
} elseif ($_SESSION['department_head']) {
  header('Location: ' . $_SERVER['HTTP_REFERER']);
}
$database = new Database();
$db = $database->getConnection();
$statement = new Statement($db);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once './PHPMailer/src/Exception.php';
require_once './PHPMailer/src/PHPMailer.php';
require_once './PHPMailer/src/SMTP.php';
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous" />
  <!-- шрифты от гугла -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@300&display=swap" rel="stylesheet" />
  <!-- My stylesheet-->
  <link rel="stylesheet" href="css/style.css" />
  <!--AwesomeFont Script-->
  <script src="https://kit.fontawesome.com/b3d038209e.js" crossorigin="anonymous"></script>
  <title>Подача заявки</title>
</head>

<body>
  <?php include("navbar.php");
  //отправка заявки
  if (isset($_POST['btn_send'])) {
    $user = $_SESSION['user_id'];
    $statement_date = $_POST['statement_date'];
    $statement_subject = $_POST['statement_subject'];
    $purchase_purpose = $_POST['purchase_purpose'];
    $product_name = $_POST['product_name'];
    $okpd2 = $_POST['okpd2'];
    $product_count = $_POST['product_count'];
    $unit = $_POST['unit'];
    $price1_for1 = $_POST['price1_for1'];
    $price1 = $_POST['price1'];
    $price2_for1 = $_POST['price2_for1'];
    $price2 = $_POST['price2'];
    $price3_for1 = $_POST['price3_for1'];
    $price3 = $_POST['price3'];
    $warranty_period = $_POST['warranty_period'];
    $final_price1 = $_POST['final_price1'];
    $final_price2 = $_POST['final_price2'];
    $final_price3 = $_POST['final_price3'];
    $nmp_price = $_POST['nmp_price'];
    $nmp_variants = $_POST['nmp_variants'];
    $nmp_method = $_POST['nmp_method'];
    $source_finance = $_POST['source_finance'];
    $ext = $_POST['ext'];
    $purchase_variants = $_POST['purchase_variants'];
    $delivery_period = $_POST['delivery_period'];
    $delivery_place = $_POST['delivery_place'];
    $supplier_determ = $_POST['supplier_determ'];
    // загрузка файлов на сервер
    if ($_FILES) {
      $target_dir = "../files/";
      $target_file_nmp = $target_dir . basename($_FILES["file_nmp"]["name"]);
      $target_file_spec = $target_dir . basename($_FILES["file_spec"]["name"]);
      $documentFileType = strtolower(pathinfo($target_file_nmp, PATHINFO_EXTENSION));
      $documentFileType = strtolower(pathinfo($target_file_spec, PATHINFO_EXTENSION));
      if (!file_exists($target_file_nmp) && !(file_exists($target_file_spec))) {
        move_uploaded_file($_FILES["file_nmp"]["tmp_name"], $target_file_nmp);
        move_uploaded_file($_FILES["file_spec"]["tmp_name"], $target_file_spec);
        $file_nmp = $target_file_nmp;
        $file_spec = $target_file_spec;
      }
    }

    $send_statement = $statement->sendStatement($user, $statement_date, $statement_subject, $purchase_purpose, $product_name, $okpd2, $product_count, $unit, $price1_for1, $price1, $price2_for1, $price2, $price3_for1, $price3, $warranty_period, $final_price1, $final_price2, $final_price3, $nmp_price, $nmp_variants, $nmp_method, $source_finance, $ext, $purchase_variants, $delivery_period, $delivery_place, $supplier_determ, $file_nmp, $file_spec);
    $get_email =  $statement->searchHeadEmail($_SESSION['user_department_id']);
    // $my_email = "171007030408fbd@gmail.com";


    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';

    // Настройки SMTP
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPDebug = 0;

    $mail->Host = 'ssl://smtp.yandex.ru';
    $mail->Port = 465;
    $mail->Username = 'death.Russia@yandex.ru';
    $mail->Password = '';//убрал пароль от своей почты
    $mail->SMTPOptions = array(
      'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
      )
    );

    // От кого
    $mail->setFrom('statements@isuct.ru', 'Заявки ТРУ');

    // Кому
    $mail->addAddress($get_email, '');

    // Тема письма
    $mail->Subject = 'Новая заявка';

    // Тело письма
    $body = 'У вас есть непроверенные заявки, самое время их просмотреть';
    $mail->msgHTML($body);


    $mail->send();
  }
  ?>
  </br>
  <h1 class="text-center"> Создание заявки</h1>
  <form action="" method="post" name="application_submission" enctype="multipart/form-data">
    </br>
    <div class="container-fluid ms-5">
      <div class="row">
        <div class="col-4">
          <p class="text-start">Дата заявки </p>
          <input type="date" name="statement_date" id="date" />
        </div>
        <div class="col-4">
          <p class="text-start">Наименование структурного подразделения</p>
          <select class="form-control" name="user_department" id="department" style="width:300px">
            <?php echo "<option value='{$_SESSION['user_department']}'>{$_SESSION['user_department']}</option>" ?>
          </select>
        </div>
        <div class="col">
          <p class="text-start">Предмет договора</p>
          <select class="form-control" name="statement_subject" id="subject" style="width:300px" required>
            <?php
            $sbjct = $statement->readAllStatementSubjects();
            while ($sbjct_row = $sbjct->fetch(PDO::FETCH_ASSOC)) {
              extract($sbjct_row);
              echo "<option value='{$sbjct_row['subject_description']}'>{$sbjct_row['subject_description']}</option>";
            }
            ?>
          </select>
        </div>
      </div>
      </br>
      <div class="row">
        <div class="col-2">Цель проведения закупки</div>
        <div class="col">
          <input type="text" name="purchase_purpose" style="width: 900px;" required>
        </div>
      </div>
      </br>
      <h3>Описание закупки</h3>
      <div class="row">
        <div class="col-1">
          <!-- echo $row_number; -->
          <h4>1.</h4>
        </div>
        <div class="col-3">
          <p class="text-start">Наименование ТРУ</p>
          <input type="text" name="product_name" id="product" style="width: 300px;" required />
        </div>
        <div class="col-2">
          <p class="text-start">ОКПД2</p>
          <input type="text" name="okpd2" id="okpd2_number" required />
        </div>
        <div class="col-2">
          <p class="text-start">Количество</p>
          <input type="text" name="product_count" id="product_count_id" required />
        </div>
        <div class="col-2">
          <p class="text-start">Единицы измерения</p>
          <select class="form-control" name="unit" id="measure_unit" style="width: 300px;" required>
            <?php
            $unit = $statement->readAllMeasureUnit();
            while ($unit_row = $unit->fetch(PDO::FETCH_ASSOC)) {
              extract($unit_row);
              echo "<option value='{$unit_row['unit_name']}'>{$unit_row['unit_name']}</option>";
            }
            ?>
          </select>
        </div>
      </div>
      </br>
      <div class="row">
        <h5 class="text-center">Коммерческие предложения</h5>
        <div class="col">
          <p class="text-start">Цена за ед. ТРУ с учетом НДС, руб.</p>
          <input type="text" name="price1_for1" id="price1_1" required />
          <p class="text-start">Общая сумма ТРУ с учетом НДС, руб.</p>
          <input type="text" name="price1" id="price1_id" required />
        </div>
        <div class="col">
          <p class="text-start">Цена за ед. ТРУ с учетом НДС, руб.</p>
          <input type="text" name="price2_for1" id="price2_1" required />
          <p class="text-start">Общая сумма ТРУ с учетом НДС, руб.</p>
          <input type="text" name="price2" id="price2_id" required />
        </div>
        <div class="col">
          <p class="text-start">Цена за ед. ТРУ с учетом НДС, руб.</p>
          <input type="text" name="price3_for1" id="price3_1" required />
          <p class="text-start">Общая сумма ТРУ с учетом НДС, руб.</p>
          <input type="text" name="price3" id="price3_id" required />
        </div>
      </div>
      <div class="row justify-content-md-center">
        <div class="col-lg-auto">
          <p class="text-center">Срок гарантии</p>
          <input type="date" name="warranty_period" id="warranty" />
        </div>
      </div>
      <div class="row">
        <div class="col">
          <p class="text-start">Итого</p>
          <input type="text" name="final_price1" id="finalPrice1" required />
        </div>
        <div class="col">
          <p class="text-start">Итого</p>
          <input type="text" name="final_price2" id="finalPrice2" required />
        </div>
        <div class="col">
          <p class="text-start">Итого</p>
          <input type="text" name="final_price3" id="finalPrice3" required />
        </div>
      </div>
      </br>
      </br>
      <div class="row">
        <div class="col-4">
          <p class="text-start">Начальная максимальная цена договора</p>
          <input type="text" name="nmp_price" id="nmp" title="при конкурентной закупке указывается средняя цена, при закупке у ЕП указывается минимальная цена договора" required />
        </div>
        <div class="col">
          <p class="text-start">НМЦ включает в себя</p>
          <select class="form-control" name="nmp_variants" id="variants" style="width:350px" required>
            <?php
            $variants = $statement->readAllnmpVariants();
            while ($variants_row = $variants->fetch(PDO::FETCH_ASSOC)) {
              extract($variants_row);
              echo "<option value='{$variants_row['nmp_var_desc']}'>{$variants_row['nmp_var_desc']}</option>";
            }
            ?>
          </select>
        </div>
        <div class="col">
          <p class="text-start">Метод определения НМЦ</p>
          <input type="text" name="nmp_method" id="nmp_method_id" style="width: 300px;" required>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <p class="text-start">Источник финансирования</p>
          <input type="text" name="source_finance" id="source_finance_id" required>
        </div>
        <div class="col">
          <p class="text-start">Код вида расходов</p>
          <input type="text" name="ext" id="ext_id" required>
        </div>
        <div class="col">
          <p class="text-start">Сроки и условия оплаты</p>
          <select class="form-control" name="purchase_variants" id="purch_var_id" style="width:300px" title="без аванса: оплата Заказчиком по договору производится разовым платежом на счет Поставщика  (Подрядчика, Исполнителя)в течение 15 рабочих  дней с момента поставки товара на основании счета, товарно-транспортной накладной и акта приема-передачи товара (без претензий со стороны Заказчика).
б) с авансом: в течение 5 рабочих дней с момента подписания договора и выставления Поставщиком (Исполнителем, Подрядчиком) счета на оплату, Заказчик перечисляет аванс в размере 30% от стоимости договора. Окончательный расчет по договору 70% производится в течение 15 рабочих  дней с момента поставки товара на основании счета, товарно-транспортной накладной и акта приема-передачи товара (без претензий со стороны Заказчика).">
            <?php
            $purchase_variants_choise = $statement->readAllpurchaseVariants();
            while ($purchase_variants_row = $purchase_variants_choise->fetch(PDO::FETCH_ASSOC)) {
              extract($purchase_variants_row);
              echo "<option value='{$purchase_variants_row['purch_var_desc']}'>{$purchase_variants_row['purch_var_desc']}</option>";
            }
            ?>
          </select>
        </div>
      </div>
      <div class="row">
        <div class="col-4">
          <p class="text-start">Желаемый срок поставки ТРУ (дни)</p>
          <input type="text" name="delivery_period" id="delivery_period_id" title="необходимо указать целые дни" required>
        </div>
        <div class="col">
          <p class="text-start">Место поставки товара</p>
          <input type="text" name="delivery_place" id="delivery_place_id">
        </div>
        <div class="col">
          <p class="text-start">Способ определения поставщика</p>
          <input type="text" name="supplier_determ" id="supplier_determ_id" required>
        </div>
      </div>
      </br>
      <div class="row" style="width: 500px;">
        <p class="text-start">Обоснование НМЦ</p>
        <input class="form-control form-control-sm" name="file_nmp" id="formFileSm" type="file" required>
      </div>
      <div class="row" style="width: 500px;">
        <p class="text-start">Техническое задание</p>
        <input class="form-control form-control-sm" name="file_spec" id="formFileSm" type="file" required>
      </div>
      <div class="text-center">
        <button name="btn_send" class="btn btn-success" type="submit" style="margin-top: 50px;">Отправить</button>
      </div>
    </div>
  </form>
  <!-- Option 1: Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

  <!-- Option 2: Separate Popper and Bootstrap JS -->
  <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
    -->
</body>

</html>