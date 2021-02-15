<?php

$region = clean($_POST["region"] ?? false);
$dispatchDate = clean($_POST["dispatchDate"] ?? false);
$courier = clean($_POST["courier"] ?? false);
$submit = clean($_POST["submit"] ?? false);
$date1 = clean($_POST["date1"] ?? false);
$date2 = clean($_POST["date2"] ?? false);

function clean($d) {
    $d = trim($d);
    $d = stripslashes($d);
    $d = htmlspecialchars($d);
    return $d;
}

$server = "localhost";
$user = "id16146314_maxim";
$pass = "b+03U(tt|NSqh^Y[";
$db = "id16146314_proofix_test";

if ($region && $dispatchDate && $courier && $submit) {
    $conn = mysqli_connect($server, $user, $pass, $db);
    if (!$conn) {
        die("Ошибка при подключении: " . mysqli_connect_error());
    }

    $sql = "
    INSERT INTO `travels` (`region_id`, `dispatch_date`, `courier_id`) 
    SELECT r.`id`, '" . $dispatchDate . "', c.`id` 
    FROM `regions` r, `couriers` c 
    WHERE 
        r.`name`='" . $region . "' 
        AND c.`name`='" . $courier . "' 
        AND c.`id` NOT IN (
            SELECT `couriers`.`id` 
            FROM `regions`, `travels`, `couriers`, 
                (SELECT `regions`.`travel_time` 
                FROM `regions` 
                WHERE `regions`.`name`='" . $region . "') r 
            WHERE `regions`.`id`=`travels`.`region_id` 
                AND `travels`.`courier_id`=`couriers`.`id` 
                AND `travels`.`dispatch_date`<DATE_ADD('" . $dispatchDate . "', INTERVAL r.`travel_time` DAY) 
                AND DATE_ADD(`travels`.`dispatch_date`, INTERVAL `regions`.`travel_time` DAY)>'" . $dispatchDate . "' 
                AND `couriers`.`name`='" . $courier . "')";

	if (!mysqli_query($conn, $sql)) {
        echo "Ошибка: <br>" . mysqli_error($conn);
	} else {
	    echo 'OK';
	}
	mysqli_close($conn);
}

if ($region && $dispatchDate && $courier && !$submit) {
    $conn = mysqli_connect($server, $user, $pass, $db);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    $sql = "
    SELECT IF(COUNT(*)>0, 0, DATE_ADD('" . $dispatchDate . "', INTERVAL CEIL((r.`travel_time`)/2-1) DAY)) AS delivery_date 
    FROM 
        `regions`, `travels`, `couriers`, 
        (SELECT `regions`.`travel_time` 
        FROM `regions` 
        WHERE `regions`.`name`='" . $region . "') r 
    WHERE `regions`.`id`=`travels`.`region_id` 
        AND `travels`.`courier_id`=`couriers`.`id` 
        AND `travels`.`dispatch_date`<DATE_ADD('" . $dispatchDate . "', INTERVAL r.`travel_time` DAY) 
        AND DATE_ADD(`travels`.`dispatch_date`, INTERVAL `regions`.`travel_time` DAY)>'" . $dispatchDate . "' 
        AND `couriers`.`name`='" . $courier . "'";
    
    $result = mysqli_query($conn, $sql);
    
    while($row = mysqli_fetch_assoc($result)) {
        if($row['delivery_date'] == 0) {
            echo 'Невозможно отправить этого курьера в эту дату в этом направлении!';
        } else {
            echo $row['delivery_date'];
        }
    }
    mysqli_close($conn);
}

if ($date1 && $date2) {
    $conn = mysqli_connect($server, $user, $pass, $db);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    $sql = "
    SELECT
        `regions`.`name` AS region, 
        `travels`.`dispatch_date` AS date, 
        `couriers`.`name` AS courier, 
        DATE_ADD(`travels`.`dispatch_date`, INTERVAL CEIL((`regions`.`travel_time`-1)/2) DAY) AS delivery_date 
    FROM `regions`, `travels`, `couriers` 
    WHERE `regions`.`id`=`travels`.`region_id` 
        AND `travels`.`courier_id`=`couriers`.`id` 
        AND `travels`.`dispatch_date` >= '" . $date1 . "' 
        AND `travels`.`dispatch_date` <= '" . $date2 . "'
    ORDER BY courier, date, region";

    $result = mysqli_query($conn, $sql);
    echo "
    <table>
        <tr>
        <th>Регион</h>
        <th>Дата выезда из Москвы</th>
        <th>ФИО курьера</th>
        <th>Дата прибытия в регион</th>
        </tr>
    ";
    if (mysqli_num_rows($result) == 0) {
        echo "</table>";
        echo "<p>Нет поездок между выбранными датами.</p>";
    } else {
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['region'] . "</td>";
            echo "<td>" . $row['date'] . "</td>";
            echo "<td>" . $row['courier'] . "</td>";
            echo "<td>" . $row['delivery_date'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    mysqli_close($conn);
}

?>
