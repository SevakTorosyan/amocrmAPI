## Для запуска нужно ##
<ol>
    <li>В терминале выполнить команду 'composer install'</li>
    <li>Зайти в /config/params.php и ввести данные своей интеграции (redirect_uri => 'https://<домен>/site/code')</li>
    <li>Открыть главную страницу https://<домен>/</li>
</ol>

#### Запросы ####
<ul>
    <li>contact/create - создаст 1000 контактов</li>
    <li>company/create - создаст 1000 компаний</li>
    <li>lead/create - создаст 1000 сделок</li>
    <li>contact - вывести все контакты</li>
    <li>company - вывести все компании</li>
    <li>lead - вывести все сделки</li>
    <li>contact/add-field - добавить доп. поле "Язык" и обновить все его значения</li>
    <li>contact/update - обновить значения в поле "Язык" во всех контактах</li>
    <li>lead/update - связать сделки с контактами и компаниями (долгий запрос)</li>
</ul>
