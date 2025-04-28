<?php
/**
* Получить целочисленное значение переменной, полученной методом GET
* @param string $name Значение атрибута `name` поля `input` из браузерного запроса
* @param int $default Значение которое будет возвращено, если нужной GET-переменной не существует
* @return int Значение GET-переменной с указанным именем или ответ по умолчанию 
*/
function GETI(string $name, $default = 0) {
	global $db;
	$result = (int)$default;
	if (isset($_GET[$name])) { $result = $db->real_escape_string($_GET[$name]); }
	return (int)$result;
}

/**
* Получить вещественное значение переменной, полученной методом GET
* @param string $name Значение атрибута `name` поля `input` из браузерного запроса
* @param float $default Значение которое будет возвращено, если нужной GET-переменной не существует
* @return float Значение GET-переменной с указанным именем или ответ по умолчанию 
*/
function GETF(string $name, $default = 0.0) {
	global $db;
	$result = (float)$default;
	if (isset($_GET[$name])) { $result = $db->real_escape_string($_GET[$name]); }
	return (float)$result;
}

/**
* Получить текстовое значение переменной, полученной методом GET
* @param string $name Значение атрибута `name` поля `input` из браузерного запроса
* @param string $default Значение которое будет возвращено, если нужной GET-переменной не существует
* @return string Значение GET-переменной с указанным именем или ответ по умолчанию 
*/
function GETS(string $name, $default = "") {
	global $db;
	$result = (string)$default;
	if (isset($_GET[$name])) { $result = $db->real_escape_string($_GET[$name]); }
	return $result;
}

/**
* Получить значение переменной с датой, полученной методом GET
* @param string $name Значение атрибута `name` поля `input` из браузерного запроса
* @return DateTime Значение GET-переменной с указанным именем или текущая дата 
*/
function GETD(string $name) {
	global $db;
	$result = date("Y-m-d");
	if (isset($_GET[$name])) { $result = $db->real_escape_string($_GET[$name]); }
	return $result;
}

/**
* Получить целочисленное значение переменной, полученной методом POST
* @param string $name Значение атрибута `name` поля `input` из браузерного запроса
* @param int $default Значение которое будет возвращено, если нужной POST-переменной не существует
* @return int Значение POST-переменной с указанным именем или ответ по умолчанию 
*/
function POSTI(string $name, $default = 0) {
	global $db;
	$result = (int)$default;
	if (isset($_POST[$name])) { $result = $db->real_escape_string($_POST[$name]); }
	return (int)$result;
}

/**
* Получить вещественное значение переменной, полученной методом POST
* 
* @param string $name Значение атрибута `name` поля `input` из браузерного запроса
* @param float $default Значение которое будет возвращено, если нужной POST-переменной не существует
* @return float Значение POST-переменной с указанным именем или ответ по умолчанию 
*/
function POSTF(string $name, $default = 0.0) {
	global $db;
	$result = (float)$default;
	if (isset($_POST[$name])) { $result = $db->real_escape_string($_POST[$name]); }
	return (float)$result;
}

/**
* Получить текстовое (string) значение переменной, полученной методом POST
* @param string $name Значение атрибута `name` поля `input` из браузерного запроса
* @param string $default Значение которое будет возвращено, если нужной POST-переменной не существует
* @return string Значение POST-переменной с указанным именем или ответ по умолчанию
*/
function POSTS(string $name, $default = "") {
	global $db;
	$result = (string)$default;
	if (isset($_POST[$name])) { $result = $db->real_escape_string($_POST[$name]); }
	return $result;
}

/**
* Получить значение переменной с датой, полученной методом POST
* @param string $name Значение атрибута `name` поля `input` из браузерного запроса
* @return DateTime Значение POST-переменной с указанным именем или текущая дата
*/
function POSTD(string $name) {
	global $db;
	$result = date("Y-m-d");
	if (isset($_POST[$name])) { $result = $db->real_escape_string($_POST[$name]); }
	return $result;
}

/**
* Класс, помогающий отобразить выпадающий список из базы данных MySQL
* */
class ListBox {
    public $values = null;
    public $titles = null;
    public $table = null;
    public $name = null;
    public $valueColumn = null;
    public $titleColumn = null;
    public $defaultValue = 0;
    public $defaultTitle = "Все";
    public $default = true;
    public $sortColumn = null;
    public $sortDir = "ASC";
    public $value = 0;
    public $class = null;
    public $onchange = "";
    private $mysql = true;
    private $sql = null;
    function __construct(string $name, string $table, string $valueColumn, string $titleColumn) {
        $this->name = $name;
        $this->table = $table;
        $this->valueColumn = $valueColumn;
        $this->titleColumn = $titleColumn;
        $this->sortColumn = $this->titleColumn;
        $this->mysql = true;
        $this->createSql();
    }
    
    function createSql() {
        if ($this->mysql) {
            if (is_null($this->table)) { throw new Exception("Не задано имя таблицы."); }
            if (is_null($this->valueColumn)) { throw new Exception("Не задан столбец со значениями."); }
            if (is_null($this->titleColumn)) { throw new Exception("Не задан столбец с отображаемым текстом."); }
            $this->sql = "SELECT `{$this->valueColumn}` AS id, `{$this->titleColumn}` AS title 
            FROM `{$this->table}` ORDER BY `{$this->sortColumn}` $this->sortDir";
        }
    }
    
    function addValues(...$values) {
        
    }
    
    function addTitles(...$titles) {
        
    }
    
    function draw($db) {
        if ($this->mysql) {
            if ($this->sql == null) { throw new Exception("MySQL запрос не создан."); }
            if ($query = $db->query($this->sql)) {
                print $this->getSelect();
                if ($this->default) {
                    print $this->getOption($this->defaultValue, $this->defaultTitle, $this->defaultValue == $this->value);
                }
                while ($row = $query->fetch_array()) {
                    print $this->getOption($row['id'], $row['title'], $row['id'] == $this->value);
                }
                $query->close();
            }
        } else {
            if (is_null($this->values)) { throw new Exception("Массив значений пуст."); }
            if (is_null($this->titles)) { throw new Exception("Массив строк пуст."); }
            if (count($this->values) != count($this->titles)) { throw new Exception("Количество данных не совпадает с количеством строк"); }
        }
    }
    
    function getSelect() {
        $result = '<select name="' . $this->name . '"';
        if (!is_null($this->class)) { $result .= ' class="' . $this->class . '"'; }
        if (!is_null($this->onchange)) { $result .= ' onchange="' . $this->onchange . '"'; }
        return $result.'>';
    }
    
    function getOption($value, $text, $selected) {
        $result = '<option value="' . $value . '"';
        if ($selected) { $result .= ' selected'; }
        $result .= '>' . $text . '</option>';
        return $result;
    }
}
?>