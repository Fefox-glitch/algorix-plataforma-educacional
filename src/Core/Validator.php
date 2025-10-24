<?php
namespace App\Core;

/**
 * Clase para validación de formularios
 */
class Validator {
    private $errors = [];
    private $data = [];
    private $rules = [];
    
    /**
     * Constructor
     * 
     * @param array $data Datos a validar
     * @param array $rules Reglas de validación
     */
    public function __construct(array $data, array $rules = []) {
        $this->data = $data;
        $this->rules = $rules;
    }
    
    /**
     * Añade reglas de validación
     * 
     * @param array $rules Reglas de validación
     * @return self
     */
    public function rules(array $rules) {
        $this->rules = array_merge($this->rules, $rules);
        return $this;
    }
    
    /**
     * Ejecuta la validación
     * 
     * @return bool True si la validación es exitosa
     */
    public function validate() {
        $this->errors = [];
        
        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            
            foreach ($fieldRules as $rule) {
                $ruleName = $rule;
                $ruleParams = [];
                
                // Procesar reglas con parámetros (ej: min:3)
                if (strpos($rule, ':') !== false) {
                    list($ruleName, $ruleParamsStr) = explode(':', $rule, 2);
                    $ruleParams = explode(',', $ruleParamsStr);
                }
                
                $method = 'validate' . ucfirst($ruleName);
                
                if (method_exists($this, $method)) {
                    $valid = $this->$method($value, $ruleParams);
                    
                    if (!$valid) {
                        $this->addError($field, $ruleName, $ruleParams);
                        break; // Pasar a la siguiente regla
                    }
                }
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Obtiene los errores de validación
     * 
     * @return array Errores de validación
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Obtiene los errores de validación como string HTML
     * 
     * @return string Errores de validación en formato HTML
     */
    public function errorsHtml() {
        if (empty($this->errors)) {
            return '';
        }
        
        $html = '<div class="validation-errors">';
        $html .= '<ul>';
        
        foreach ($this->errors as $field => $errors) {
            foreach ($errors as $error) {
                $html .= '<li>' . htmlspecialchars($error) . '</li>';
            }
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Añade un error de validación
     * 
     * @param string $field Campo con error
     * @param string $rule Regla que falló
     * @param array $params Parámetros de la regla
     */
    private function addError($field, $rule, $params = []) {
        $fieldLabel = ucfirst(str_replace('_', ' ', $field));
        
        $messages = [
            'required' => 'El campo ' . $fieldLabel . ' es obligatorio.',
            'email' => 'El campo ' . $fieldLabel . ' debe ser un email válido.',
            'min' => 'El campo ' . $fieldLabel . ' debe tener al menos ' . $params[0] . ' caracteres.',
            'max' => 'El campo ' . $fieldLabel . ' no debe tener más de ' . $params[0] . ' caracteres.',
            'numeric' => 'El campo ' . $fieldLabel . ' debe ser un número.',
            'alpha' => 'El campo ' . $fieldLabel . ' solo debe contener letras.',
            'alphanumeric' => 'El campo ' . $fieldLabel . ' solo debe contener letras y números.',
            'url' => 'El campo ' . $fieldLabel . ' debe ser una URL válida.',
            'date' => 'El campo ' . $fieldLabel . ' debe ser una fecha válida.',
            'matches' => 'El campo ' . $fieldLabel . ' debe coincidir con ' . ucfirst(str_replace('_', ' ', $params[0])) . '.',
            'unique' => 'El valor del campo ' . $fieldLabel . ' ya está en uso.'
        ];
        
        $message = $messages[$rule] ?? 'El campo ' . $fieldLabel . ' es inválido.';
        
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }
    
    // Métodos de validación
    
    private function validateRequired($value) {
        return !empty($value);
    }
    
    private function validateEmail($value) {
        return empty($value) || filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    private function validateMin($value, $params) {
        $min = (int) $params[0];
        return empty($value) || mb_strlen($value) >= $min;
    }
    
    private function validateMax($value, $params) {
        $max = (int) $params[0];
        return empty($value) || mb_strlen($value) <= $max;
    }
    
    private function validateNumeric($value) {
        return empty($value) || is_numeric($value);
    }
    
    private function validateAlpha($value) {
        return empty($value) || preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $value);
    }
    
    private function validateAlphanumeric($value) {
        return empty($value) || preg_match('/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]+$/', $value);
    }
    
    private function validateUrl($value) {
        return empty($value) || filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
    
    private function validateDate($value) {
        if (empty($value)) {
            return true;
        }
        
        $date = date_parse($value);
        return $date['error_count'] === 0 && $date['warning_count'] === 0;
    }
    
    private function validateMatches($value, $params) {
        $otherField = $params[0];
        $otherValue = $this->data[$otherField] ?? null;
        
        return $value === $otherValue;
    }
}