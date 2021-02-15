<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>proofix</title>
    <style>
    table, th, td {border: 1px solid black;}
    </style>
    <script>
    
    function updateSchedule(region, dispatchDate, courier, submit) {
        document.getElementById("updateValidation").disabled = true;
        region = region.value;
        dispatchDate = dispatchDate.value;
        courier = courier.value;
        if (!region || !dispatchDate || !courier) {
            return;
        }
        let xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                if (/^\d\d\d\d-\d\d-\d\d$/.test(this.responseText)) {
                    setTimeout(() => document.getElementById("updateValidation").disabled = false, 0);
                }
                document.getElementById("deliveryDate").innerHTML = this.responseText;
                let d1 = document.getElementById("date1");
                let d2 = document.getElementById("date2");
                if (submit && d1 && d2) {
                    loadSchedule(date1, date2);
                }
            }
        };
        xhttp.open("POST", "database.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("region="+region+"&dispatchDate="+dispatchDate+"&courier="+courier+"&submit="+submit);
    }

    function loadSchedule(date1, date2) {
        let d1 = date1.value;
        let d2 = date2.value;
        if(d1 && d2) {
            let xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("schedule_info_table").innerHTML = this.responseText;
                }
            };
            xhttp.open("POST", "database.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("date1="+d1+"&date2="+d2);
        }
    }
    
    function fillDataBase() {
        let date = new Date('2015-01-01');
        let end = new Date('2021-02-11');
        let couriers = [["Иванов", 0] , ["Петров", 0] , ["Сидоров", 0] , 
            ["Фродо", 0] , ["Тинькофф", 0], ["Бармалей", 0] , ["Жуков", 0] , 
            ["Павлов", 0] , ["Трамп", 0] , ["Обама", 0]];
        let regions = [["Санкт-Петербург", 2] , ["Уфа", 4] , ["Нижний Новгород", 2] , 
            ["Владимир", 1] , ["Кострома", 2] , ["Екатеринбург", 4] , ["Ковров", 2] , 
            ["Воронеж", 2] , ["Самара", 3] , ["Астрахань", 4]];
        for (let r = 0, c = 0; date < end; date.setDate(date.getDate() + 1)) {
            for (let j = 0; j < 10; j++) { //счетчик регионов
                if (Math.random() < 0.33) continue;
                for (let i = 0; i < 10; i++) { // счетчик курьеров
                    if (couriers[(c + i) % 10][1] > 0) continue;
                    let xhttp = new XMLHttpRequest();
                    xhttp.open("POST", "database.php", 0);
                    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhttp.send("region="+regions[(r + j) % 10][0]+"&dispatchDate="+date.toISOString().slice(0, 10)+"&courier="+couriers[(c + i) % 10][0]+"&submit=1");
                    couriers[(c + i) % 10][1] = regions[(r + j) % 10][1];
                    break;
                }
                c++;
            }
        r++;
        couriers.forEach((courier) => courier[1] > 0 ? --courier[1] : courier[1] = 0);
        }
    }
    
    </script>
</head>
<body>

     <h3>
        Форма для ввода данных расписания.
    </h3>
    <form name="scheduleUpdateForm"
          action=""
          method="">
        <span onchange="updateSchedule(region, dispatchDate, courier, 0)">
            <label for="region">Регион:</label>
            <input type="text" id="region" name="region" value="">
            <label for="dispatchDate">Дата выезда из Москвы:</label>
            <input type="date" id="dispatchDate" name="dispatchDate" value="">
            <label for="courier">ФИО курьера:</label>
            <input type="text" id="courier" name="courier" value="">
        </span>
        <button type="button" 
                id="updateValidation"
                onclick="updateSchedule(region, dispatchDate, courier, 1)"
                disabled>
            Принять
        </button>
    </form>
    <h4>
        Дата прибытия в регион: <span id="deliveryDate"></span>
    </h4>

    <hr>

    <h3>
        Введите даты для вывода расписания за период.
    </h3>
    <form 
        name="schedule_info_request_form" 
        action="" 
        onchange="loadSchedule(date1, date2)" 
        method="">
        <label for="date1">Начальная дата:</label>
        <input type="date" id="date1" name="date1" value="">
        <label for="date2">Конечная дата:</label>
        <input type="date" id="date2" name="date2" value="">
    </form>
    <h4 id="schedule_info_table">
        Здесь будет отображено расписание поездок за выбранный период.
    </h4>

    <hr>
    <h3>
        Запуск "fillDataBase()" из консоли заполняет БД расписанием с 2015.
    </h3>


</body>