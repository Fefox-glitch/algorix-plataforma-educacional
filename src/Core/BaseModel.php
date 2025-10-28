<?php

namespace App\Core;

/**
 * Clase base para modelos
 */
class BaseModel
{
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * Encuentra un registro por su ID
     *
     * @param int $id ID del registro
     * @return array|null Registro encontrado o null
     */
    public function find($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Obtiene todos los registros
     *
     * @param string $orderBy Campo para ordenar
     * @param string $order Dirección de ordenamiento (ASC, DESC)
     * @return array Registros encontrados
     */
    public function all($orderBy = null, $order = 'ASC')
    {
        $query = "SELECT * FROM {$this->table}";

        if ($orderBy) {
            $query .= " ORDER BY {$orderBy} {$order}";
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo registro
     *
     * @param array $data Datos del registro
     * @return int|bool ID del registro creado o false
     */
    public function create(array $data)
    {
        $data = $this->filterFillable($data);

        if (empty($data)) {
            return false;
        }

        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $query = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                 VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->db->prepare($query);
        $result = $stmt->execute(array_values($data));

        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Actualiza un registro existente
     *
     * @param int $id ID del registro
     * @param array $data Datos a actualizar
     * @return bool Resultado de la operación
     */
    public function update($id, array $data)
    {
        $data = $this->filterFillable($data);

        if (empty($data)) {
            return false;
        }

        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "{$field} = ?";
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " 
                 WHERE {$this->primaryKey} = ?";

        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->db->prepare($query);
        return $stmt->execute($values);
    }

    /**
     * Elimina un registro
     *
     * @param int $id ID del registro
     * @return bool Resultado de la operación
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Busca registros por condiciones
     *
     * @param array $conditions Condiciones de búsqueda
     * @param string $operator Operador lógico entre condiciones (AND, OR)
     * @return array Registros encontrados
     */
    public function where(array $conditions, $operator = 'AND')
    {
        $fields = [];
        $values = [];

        foreach ($conditions as $field => $value) {
            $fields[] = "{$field} = ?";
            $values[] = $value;
        }

        $query = "SELECT * FROM {$this->table} WHERE " . implode(" {$operator} ", $fields);

        $stmt = $this->db->prepare($query);
        $stmt->execute($values);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Filtra los datos según los campos permitidos
     *
     * @param array $data Datos a filtrar
     * @return array Datos filtrados
     */
    protected function filterFillable(array $data)
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }
}
