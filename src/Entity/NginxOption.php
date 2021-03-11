<?php

namespace App\Entity;

class NginxOption
{
    private $option;

    private $value;

    public function getOption(): string
    {
        return $this->option;
    }

    public function setOption(string $option): void
    {
        $this->option = $option;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

}