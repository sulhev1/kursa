<?php
/**
 * Класс, помогающий выполнять запросы SELECT к таблице базы данных MySQL
 */
class SELECT {
	public $table = null;
	public $search = null;
	public $order = null;
	public $columns = [];
	public $where = [];
	public $all_pages = 0;
	public $page = 1;
	public $rows_on_page = 20;
	public $join_table = [];
	public $join_on = [];
	//
	private $allData;
	private $rowData;
	private $current_row = 0;
	private $all_rows = 0;
	private $db;
	private $calculated = false;
	private $requested = false;
	private $read = false;
	private $closed = false;
	private $combineSql = null;
	
    
	/**
	 * Коструктор класса SELECT. Создает объект, помогающий создавать SELECT запросы к таблицам MySQL
	 * @param object $db Ссылка на открытое соединение с базой данных MySQL
	 * @param string $table Имя таблицы MySQL, к которой будет выполняться запрос
	 * @return SQL Возвращает созданный объект класса SELECT */
	function __construct(object $db, string $table) {
		$this->db = $db;
		$this->table = $table;
	}

	//добавление столбца 
	function addColumn($caption, $name, $alias, $size, $align) {
		$column = new Column();
		$column->caption = $caption;
		$column->name = $name;
		$column->alias = $alias;
		if ($size) { $column->size = $size; }
		if (!is_null($align)) { $column->align = $align; }
		$this->columns[] = $column;
	}

    /**
    * Получить общее количество записей, удовлетворяющих запросу. Выполняется только после метода calculate()
    * */
	function getRows() {
        if ($this->calculated) { return $this->all_rows; }
        else { return false; }
    }

    /**
    * Получить общее количество страниц, удовлетворяющих запросу. Выполняется только после метода calculate()
    * */
    function getPages() { 
        if ($this->calculated) { return $this->all_pages; }
        else { return false; }
    }
    
	function getRow() { return $this->current_row; }

	/**
	* Прочитать очередную строку из ответа на SQL-запрос
	* @return bool Возвращает true, если прочитана очередная строка или false если строки кончились	*/
	function nextRow() {
		if (!$this->requested) { $this->query(); }
		if ($this->rowData = $this->allData->fetch_array()) {
			$this->read = true;
			return true;
		} else {
			$this->close();
		}
	}

	/**
	* Закрыть ответ от базы данных
	*/
	function close() {
        if (!$this->closed) {
            $this->closed = true;
            $this->allData->close();
        }
	}
    
	/**
	* Получить значение из указанного столбца таблицы MySQL текущей строки
	* @param string $column Название столбца в таблице
	*/
	function get(string $column) {
		if (!$this->requested) { throw new Exception("SQL-запрос ещё не выполнен"); }
		if ($this->closed) { throw new Exception("SQL-запрос уже выполнен и закрыт"); }
		if (!$this->read) { throw new Exception("Перед получением значения из столбца необходимо прочитать очередную строку из ответа"); }
		if (!isset($this->rowData[$column])) { throw new Exception("Столбца `$column` в ответе не существует"); }
		return $this->rowData[$column];
	}
	function column(string $column) {
		return $this->get($column);
	}

	/**
	* Добавить условие для выборки необходимых записей из таблицы MySQL
	* @param string $where Строка с условием, напр. "product_cost > 100" */
	function addWhere(string $where) { $this->where[] = $where; }

	/**
	* Задать поисковую строку для запроса
	* @param string $search Строка для поиска */
	function setSearch(string $search) { $this->search = [$search]; }
	
	/**
	* Задать нужную страницу для отображения. Выполняется перед запросом query
	* @param int $page Необходимая страница
	* */
	function setPage(int $page) { 
		if (!$this->calculated) { $this->calculate(); }
		if ($this->requested || $this->closed) { throw new Exception ("Назначить необходимую страницу для отображения можно только до выполнения метода query"); }
		$this->page = $page;
		$this->checkPage();
	}

	/**
	 * Выполнение запроса для получения данных из таблицы
	 */
	public function query() {
		if (!$this->calculated) { $this->calculate(); }
		if ($this->requested) { throw new Exception("SQL-запрос уже выполнен"); }
		if ($this->closed) { throw new Exception("SQL-запрос уже выполнен и закрыт"); }
		$sql = $this->getDataSql();
		if ($this->allData = $this->db->query($sql)) {
			$this->requested = true;
		} else {
			throw new Exception("SQL-запрос на получение данных не может быть выполнен");
		}
	}

	private function checkPage() {
		if ($this->rows_on_page <= 0) { throw new Exception ("Максимальное количество отображаемых записей на одной странице некорректно"); }
		$this->all_pages = ceil($this->all_rows / $this->rows_on_page);
		if ($this->all_rows - $this->all_pages * $this->rows_on_page > 0) { $this->all_pages++; }
		if ($this->page > $this->all_pages) { $this->page = $this->all_pages; }
		if ($this->page <= 0) { $this->page = 1; }
		$this->current_row = ($this->page - 1) * $this->rows_on_page;
		if ($this->current_row > $this->all_rows) { $this->current_row = $this->all_rows - 1; }
		if ($this->current_row < 0) { $this->current_row = 0; }
	}

	/**
	* Рассчёт общего количества записей в запросе
	* */
	private function calculate() {
		if ($this->calculated) return;
		if ($this->requested || $this->closed) { throw new Exception ("Рассчитать количество записей в запросе можно только до выполнения метода query"); }
		if ($this->combineSql == null) { $this->getCombineSql(); }
		$sql = $this->getCountSql();
		if ($query = $this->db->query($sql)) {
			if ($query->num_rows > 0) {
				$row = $query->fetch_array();
				$this->all_rows = $row[0];
			}
			$query->close();
			$this->calculated = true;
		}
	}

	/**
	 * Общая концовка 
	 */
	private function getCombineSql() {
		$first = true;
		$result = "";
		if (!is_null($this->join_table)) {
			for ($i = 0; $i < count($this->join_table); $i++) {
				$result .= " LEFT JOIN {$this->join_table[$i]} ON {$this->join_on[$i]}";
			}
		}
		if (!is_null($this->where)) {
			for ($i = 0; $i < count($this->where); $i++) {
				if ($first) { $first = false; $result .= " WHERE {$this->where[$i]}"; } else { $result .= " AND {$this->where[$i]}"; }
			}
		}
		if (!is_null($this->search) && !is_null($this->search_columns)) {
			if ($first) { $first = false; $result .= " WHERE "; } else { $result .= " AND "; }
			$result .= "{$this->search_columns} LIKE '%{$this->search}%'";
		}
		$this->combineSql = $result;
	}

	/**
	* Создание SQL-запроса для подсчёта количества записей
	*/
	private function getCountSql() {
		if (is_null($this->table)) { throw new Exception ("Имя таблицы не может быть NULL"); }
		$sql = "SELECT COUNT(*) FROM `{$this->table}`";
		if ($this->combineSql == null) { $this->getCombineSql(); }
		return $sql.$this->combineSql;
	}

	/**
	* Создание SQL-запроса для чтения записей из базы данных
	*/
	private function getDataSql() {
		if (is_null($this->table)) { throw new Exception ("Имя таблицы не может быть NULL"); }
		if (!$this->calculated) { $this->calculate(); }
		if ($this->requested) { throw new Exception ("SQL-запрос уже выполнен"); }
		if ($this->closed) { throw new Exception ("SQL-запрос уже выполнен и закрыт"); }
		if (is_null($this->combineSql)) { $this->getCombineSql(); }
		
		if (count($this->columns) > 0) {
			$columns = "";
			for ($i = 0; $i < count($this->columns); $i++) {
				if ($i > 0) { $columns .= ", "; }
				$columns .= $this->columns[$i]->name;
				if (!is_null($this->columns[$i]->alias)) { $columns .= " AS {$this->columns[$i]->alias}"; }
			}
		} else {
			$columns = "*";
		}
		$sql = "SELECT $columns FROM `{$this->table}`".$this->combineSql;
		if (!is_null($this->order)) { $sql .= " ORDER BY {$this->order} "; }
		$sql .= " LIMIT ".($this->page - 1) * $this->rows_on_page.", ".$this->rows_on_page;
		return $sql;
	}
}

class Column {
	public $caption = null;
	public $name = null;
	public $alias = null;
	public $type = TYPE_DEFAULT;
	public $size = null;
	public $align = null;
	public $visible = true;
}

class JoinTable {
	public $name = null;
	public $on = null;
}

class COMMAND {
    public $table = null;
    private $columns = [];
    private $values = [];
    private $where = [];
    private $db = null;
	
    /**
	 * Коструктор класса INSERT. Создает объект, помогающий создавать INSERT запросы к таблицам MySQL
	 * @param object $db Ссылка на открытое соединение с базой данных MySQL
	 * @param string $table Имя таблицы MySQL, к которой будет выполняться запрос
	 * @return INSERT Возвращает созданный объект класса INSERT */
	function __construct(object $db, string $table) {
		$this->db = $db;
		$this->table = $table;
	}
    
    
    function addColumns(...$columns) { $this->columns = $columns; }
    
    function addValues(...$values) {
        if (count($this->columns) != count($values)) { throw new Exception("Количество данных не совпадает с количеством столбцов"); }
        $this->values[] = $values;
    }
    
    function insert() {
        if (count($this->columns))
        $sql = "INSERT INTO `{$this->table}` (";
        $delimeter = "";
        foreach ($this->columns as $col) {
            $sql .= "$delimeter`$col`";
            if ($delimeter == "") { $delimeter = ","; }
        }
        $sql .= ") VALUES ";
        $delimeter = "";
        foreach ($this->values as $values) {
            $sql .= "$delimeter(";
            $del = "";
            foreach ($values as $val) {
                $sql .= $del;
                $sql .= "'$val'";
                if ($del == "") { $del = ","; }    
            }
            $sql .= ")";
            if ($delimeter == "") { $delimeter = ","; }
        }
        return $this->db->query($sql);
    }
    
	/**
	* Добавить условие для обновления необходимых записей из таблицы MySQL
	* @param string $where Строка с условием, напр. "product_cost > 100" */
	function addWhere(string $where) { $this->where[] = $where; }
    
    function update() {
        if (is_null($this->where)) { throw new Exception("Не задано условие, какие строчки необходимо обновить"); }
        $values = $this->values[0];
        $sql = "UPDATE `{$this->table}` SET ";
        $del = "";
        $values = $this->values[0];
        for ($i = 0; $i < count($this->columns); $i++) {
            $sql .= "$del`{$this->columns[$i]}`='{$values[$i]}'";
            if ($del == "") { $del = ","; }
        }
        $del = "WHERE";
        foreach ($this->where as $where) {
            $sql .= " $del ".$where;
            if ($del == "WHERE") { $del = "AND"; }
        }
        $this->db->query($sql);
    }
}

?>