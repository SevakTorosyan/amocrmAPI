<?php

/* @var $contactsArray array */

$this->title = 'Контакты';
echo '<style>
table {
font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;
font-size: 14px;
border-radius: 10px;
border-spacing: 0;
text-align: center;
}
th {
background: #BCEBDD;
color: white;
text-shadow: 0 1px 1px #2D2020;
padding: 10px 20px;
}
th, td {
border-style: solid;
border-width: 0 1px 1px 0;
border-color: white;
}
th:first-child, td:first-child {
text-align: left;
}
th:first-child {
border-top-left-radius: 10px;
}
th:last-child {
border-top-right-radius: 10px;
border-right: none;
}
td {
padding: 10px 20px;
background: #F8E391;
}
tr:last-child td:first-child {
border-radius: 0 0 0 10px;
}
tr:last-child td:last-child {
border-radius: 0 0 10px 0;
}
tr td:last-child {
border-right: none;
}
</style>';

if (count($contactsArray) === 0) {
    echo '<h1>Похоже, что контактов еще нет!</h1>';
    echo '<a class="btn btn-primary" href="contact/create">Создать 1000 контактов</a>';
} else {
    echo '<h1>Все контакты</h1>';
    echo
    '<table>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Отвественный за контакт</th>
            <th>Создатель контакта</th>
            <th>Языки</th>
      </tr>
    ';
    foreach ($contactsArray as $contact) {
        echo '<tr>';
        echo "<td>" . $contact['id'] . "</td>";
        echo "<td>" . $contact['name'] . "</td>";
        echo "<td>" . $contact['responsible_user'] . "</td>";
        echo "<td>" . $contact['created_by'] . "</td>";
        echo "<td>";
        if (isset($contact['custom_field_values'])) {
            foreach ($contact['custom_field_values'] as $fieldValue) {
                echo $fieldValue . ", ";
            }
        } else {
            echo "Не указано";
        }
        echo "</td>";
        echo '</tr>';
    }
    echo '</table>';
}
?>

