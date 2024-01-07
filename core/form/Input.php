<?php

namespace Fckin\core\form;

use Exception;
use Fckin\core\db\Model;

class Input
{
    public Model $model;
    public string $type = 'text';
    public string $name;
    public bool $useLabel;
    public string|null $label;
    public string|null $placeholder;
    public string|array $classes;

    public array $types = ['text', 'password', 'email', 'date', 'time'];

    public function __construct(Model $model, string $type, string $name, string|null $label = null, bool $useLabel = true, string|null $placeholder = null, string|array $classes = [])
    {
        if (in_array($type, $this->types)) {
            $this->model = $model;
            $this->name = $name;
            $this->useLabel = $useLabel;
            $this->type = $type;
            $this->label = $label;
            $this->placeholder = $placeholder;
            $this->classes = $classes;
        } else {
            throw new Exception("Error undefined the types of input. The allowed input is ". implode(', ', $this->types) ."", 1);
        }
    }

    public function __toString()
    {
        return sprintf(
            '
            <label class="form-control w-full">
                %s
                <input type="%s" name="%s" placeholder="Type %s here" value="%s" class="%s %s" />
                <div className="label">
                    <span className="label-text-alt">%s</span>
                </div>
            </label>
        ',
            $this->label($this->useLabel),
            $this->type,
            $this->name,
            text_alt_formatter($this->placeholder ?? $this->name),
            $this->model->{$this->name},
            twMerge('input input-bordered w-full', $this->classes),
            $this->model->hasError($this->name) ? 'input-error' : '',
            $this->model->getFirstError($this->name)
        );
    }

    private function label(bool $useLabel)
    {
        $label = sprintf('
            <div class="label">
                <span class="label-text">%s</span>
            </div>
        ',
            $this->label ?? text_alt_formatter($this->name)
        );
        return $useLabel ? $label : '';
    }
}
