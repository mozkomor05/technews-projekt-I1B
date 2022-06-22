<?php

// Config singleton definition
class Config
{
    private array $config;

    private function __construct(array $data)
    {
        $this->config = $data;
    }

    /**
     * @throws Exception
     */
    public static function loadFromFile(string $file): Config
    {
        $data = json_decode(file_get_contents($file), true);

        if ($data === null) {
            throw new Exception("Error while parsing config file");
        }

        return new Config($data);
    }

    public static function fromData(array $data): Config
    {
        return new Config($data);
    }

    /**
     * @throws Exception
     */
    public function get($key, $default = null)
    {
        if (is_string($key)) {
            if (isset($this->config[$key])) {
                return $this->config[$key];
            }

            $parts = explode('.', $key);
        }

        if (is_array($key)) {
            $parts = $key;
        }

        if (isset($parts)) {
            $value = $this->config;
            foreach ($parts as $part) {
                if (isset($value[$part])) {
                    $value = $value[$part];
                } else {
                    return $default;
                }
            }

            return $value;
        }
    }

    public function getAll(): array
    {
        return $this->config;
    }
}