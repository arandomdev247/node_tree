<?php

class Parameter
{
    /**
     * @var $parameter string
     * @var $value string
     * @var $level int
     * @var $header boolean
     * @var $selected boolean
     */

    private $parameter;
    private $value;
    private $level;
    private $header;
    private $selected;

    /**
     * @param $parameter string
     * @param $value string
     * @param $level int
     * @param $selected boolean
     */

    public function __construct(string $parameter, string $value, int $level,
                                bool $header=false, bool $selected=false)
    {
        $this->parameter = $parameter;
        $this->value = $value;
        $this->level = $level;
        $this->header = $header;
        $this->selected = $selected;
    }

    /**
     * @return string
     */
    public function getParameter(): string
    {
        return $this->parameter;
    }

    /**
     * @param string $parameter
     */
    public function setParameter(string $parameter): void
    {
        $this->parameter = $parameter;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return bool
     */
    public function isHeader(): bool
    {
        return $this->header;
    }

    /**
     * @param bool $header
     */
    public function setHeader(bool $header): void
    {
        $this->header = $header;
    }

    /**
     * @return bool
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }

    /**
     * @param bool $selected
     */
    public function setSelected(bool $selected): void
    {
        $this->selected = $selected;
    }

}