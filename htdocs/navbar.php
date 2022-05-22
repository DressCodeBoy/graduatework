<nav class="navbar navbar-expand-lg navbar-dark" style="background-color:#4461a8">
  <div class="container-fluid">
    <a class="navbar-brand dropdown-toggle " id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="fa-solid fa-user""></i> Кабинет</a>
        <ul class=" dropdown-menu" aria-labelledby="navbarDropdown">
        <li><a class="dropdown-item" href="<?php echo AUTHORIZATION_URL; ?>">Выход</a></li>
        </ul>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
          <div class="navbar-nav">
            <a class="nav-link" href="<?php echo MAIN_URL ?>">Админ-панель</a>
            <a class="nav-link" href="<?php echo APP_SUB_URL ?>">Подача заявки</a>
            <a class="nav-link" href="<?php echo APP_APP_URL ?>">Заявки на утверждение</a>
            <a class="nav-link" href="<?php echo APP_STAT_URL ?>">Узнать статус заявки</a>
          </div>
        </div>
  </div>
</nav>