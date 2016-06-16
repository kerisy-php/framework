<?php
namespace Kerisy\Database;

class Dsn
{

    /** Properties */
    protected $attributes = [];
    protected $prefix = null;

    /**
     * Dsn constructor.
     * @param null|string $prefix
     */
    public function __construct($prefix = null)
    {
        $this->setPrefix($prefix);
    }

    /**
     * Get attributes
     * @return mixed
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set attributes
     * @param mixed $attributes
     * @return Dsn
     */
    public function setAttributes($attributes): Dsn
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Set attribute
     * @param string|null $key
     * @param mixed $value
     * @return Dsn
     */
    public function setAttribute($key, $value): Dsn
    {
        if (null === $key) {
            $this->attributes[] = $value;
        } else {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * Get prefix
     * @return null|string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set prefix
     * @param null $prefix
     * @return Dsn
     */
    public function setPrefix($prefix): Dsn
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get DSN
     * @return string
     */
    public function __toString()
    {
        $attributes = implode(';', array_map(function ($value, $key) {
            if (is_numeric($key)) {
                return sprintf('%s', $value);
            }

            return sprintf("%s=%s", $key, $value);
        }, $this->getAttributes(), array_keys($this->getAttributes())));

        return sprintf('%s:%s', $this->getPrefix(), $attributes);
    }
}