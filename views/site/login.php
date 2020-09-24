<?php

$this->title = 'Создание токена';
?>
<?php
echo '<div>
  <script
    class="amocrm_oauth"
    charset="utf-8"
    data-client-id="' . $provider->getClientId() . '"
    data-title="Установить интеграцию"
    data-compact="false"
    data-class-name="className"
    data-color="default"
    data-state="' . $_SESSION['oauth2state'] . '"
    data-error-callback="handleOauthError"
    src="https://www.amocrm.ru/auth/button.min.js"
  ></script>
</div>'; ?>
<script>
    handleOauthError = function(event) {
        alert('ID клиента - ' + event.client_id + ' Ошибка - ' + event.error);
    }
</script>
