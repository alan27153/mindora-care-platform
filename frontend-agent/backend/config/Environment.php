<?php

namespace Config;

class Environment
{
    private static $instance = null;
    private $env_vars = [];

    private function __construct()
    {
        $this->loadEnv(dirname(__DIR__, 2) . '\\backend\\.env');
    }

    private function loadEnv($path)
    {
        if (!file_exists($path)) {
            throw new \Exception("El archivo .env no existe en: '" . $path . "'");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {

            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Permitir comillas si es necesario, pero quitarlas para el valor final.
            if (preg_match('/^"(.*)"$/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match('/^'(.*)'$/', $value, $matches)) {
                $value = $matches[1];
            }

            $this->env_vars[$name] = $value;
        }
    }

    public static function getInstance(): Environment
    {
        if (self::$instance === null) {
            self::$instance = new Environment();
        }
        return self::$instance;
    }

    public function get($key, $default = null)
    {
        if (!isset($this->env_vars[$key])) {
            if ($default !== null) {
                return $default;
            }
            throw new \Exception("La variable de entorno '{$key}' no está definida.");
        }
        return $this->env_vars[$key];
    }

    public function getRequired($key)
    {
        return $this->get($key);
    }
}
