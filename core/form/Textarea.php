<?php

namespace Fckin\core\form;

use Fckin\core\db\Model;

class Textarea
{
    public Model $model;
    public string $name;
    public string|null $label;
    public bool $useLabel;
    public string|null $placeholder;
    public string|array $classes;

    public function __construct(Model $model, string $name, string|null $label = null, bool $useLabel = true, string|null $placeholder = null, string|array $classes = [])
    {
        $this->model = $model;
        $this->name = $name;
        $this->label = $label;
        $this->useLabel = $useLabel;
        $this->placeholder = $placeholder;
        $this->classes = $classes;
    }

    public function __toString()
    {
        return sprintf('
            <label class="form-control w-full">
                %s
                <textarea name="%s" placeholder="Type %s here" class="%s %s">%s</textarea>
                <div className="label">
                    <span className="label-text-alt">%s</span>
                </div>
            </label>
        ',
            $this->label($this->useLabel),
            $this->name,
            text_alt_formatter($this->placeholder ?? $this->name),
            \twMerge('textarea textarea-bordered w-full', $this->classes),
            $this->model->hasError($this->name) ? 'input-error' : '',
            $this->model->{$this->name},
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
