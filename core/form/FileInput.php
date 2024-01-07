<?php

namespace Fckin\core\form;

use Fckin\core\db\Model;

class FileInput
{
    public Model $model;
    public string $name;
    public string|null $label;
    public string|array $classes;

    public function __construct(Model $model, string $name, string|null $label = null, string|array $classes = '')
    {
        $this->model = $model;
        $this->name = $name;
        $this->label = $label;
        $this->classes = $classes;
    }

    public function __toString()
    {
        return sprintf(
            '
            <label class="form-control w-full">
                <div class="label">
                    <span class="label-text">%s</span>
                </div>
                <input type="file" name="%s" value="%s" class="%s %s" />
                <div class="label">
                    <span class="label-text-alt">%s</span>
                </div>
            </label>
        ',
            text_alt_formatter($this->label ?? $this->name),
            $this->name,
            $this->model->{$this->name},
            \twMerge('file-input file-input-bordered w-full'. $this->classes),
            $this->model->hasError($this->name) ? 'checkbox-error' : '',
            $this->model->getFirstError($this->name)
        );
    }
}
