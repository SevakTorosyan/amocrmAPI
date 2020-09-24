<?php

/* @var $this yii\web\View */

$this->title = 'amoCRM';
?>
<div class="create">
  <a class="btn btn-primary" href='company/create'>Создать 1000 компании</a>
  <a class="btn btn-primary" href='contact/create'>Создать 1000 контактов</a>
  <a class="btn btn-primary" href='lead/create'>Создать 1000 сделок</a>
</div>
<div class="get" style="margin-top: 5rem">
  <a class="btn btn-info" href='company'>Посмотреть все компании</a>
  <a class="btn btn-info" href='contact'>Посмотреть все контакты</a>
  <a class="btn btn-info" href='lead'>Посмотреть все сделки</a>
</div>
<div class="update" style="margin-top: 5rem">
  <a class="btn btn-warning" href='lead/update'>Связать сделки с компаниями и контактами</a>
  <a class="btn btn-warning" href='contact/add-field'>Добавить поле к контактам и заполнить</a>
</div>
<div class="delete" style="margin-top: 30rem;">
  <a class="btn btn-danger" href='site/destroy'>Удалить сессию</a>
</div>

