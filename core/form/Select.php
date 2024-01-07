<?php

namespace Fckin\core\form;

use Fckin\core\db\Model;

class Select
{
    public Model $model;
    public string $name;
    public array $options;
    public string|null $label;
    public string|array $classes;

    public function __construct(Model $model, string $name, array $options = [], string|null $label = null, string|array $classes = '')
    {
        $this->model = $model;
        $this->name = $name;
        $this->options = $options;
        $this->label = $label;
        $this->classes = $classes;
    }

    public function __toString()
    {
        $html = sprintf('
            <label class="form-control w-full">
                <div class="label">
                    <span class="label-text">%s</span>
                </div>
                <select name="%s" class="select select-bordered %s">
                    <option value="" selected>- Pick one -</option>',
            $this->label,
            text_alt_formatter($this->name),
            $this->name,
            \twMerge($this->model->hasError($this->name) ? 'seclect-error' : '', $this->classes),
        );

        foreach ($this->options as $option) {
            $html .= sprintf('<option>%s</option>', htmlspecialchars($option));
        }

        $html .= sprintf('
                </select>
                <div class="label">
                    <span class="label-text-alt">%s</span>
                </div>
            </label>',
            $this->model->getFirstError($this->name)
        );

        return $html;
    }
}
