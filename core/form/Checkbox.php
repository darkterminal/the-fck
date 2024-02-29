<?php

namespace Fckin\core\form;

use Fckin\core\db\Model;

class Checkbox
{
    public Model $model;
    public string $name;
    public mixed $value;
    public bool $checked;
    public string|null $label;
    public bool $useLabel;

    public function __construct(Model $model, string $name, mixed $value, bool $checked = false, string|null $label = null, bool $useLabel = false)
    {
        $this->model = $model;
        $this->name = $name;
        $this->value = $value;
        $this->checked = $checked;
        $this->label = $label;
        $this->useLabel = $useLabel;
    }

    public function __toString()
    {
        $checkedAttribute = $this->valueMatchesModel() ? 'checked' : '';

        return sprintf(
            '
            <div class="form-control">
                <label class="cursor-pointer label">
                    <div class="flex gap-2">
                        <input type="checkbox" name="%s" value="%s" %s class="checkbox %s" />
                        %s
                    </div>
                </label>
                <div class="label">
                    <span class="label-text-alt">%s</span>
                </div>
            </div>
        ',
            $this->name,
            $this->value,
            $checkedAttribute,
            $this->model->hasError($this->name) ? 'checkbox-error' : '',
            $this->label($this->useLabel),
            $this->model->getFirstError($this->name)
        );
    }

    private function valueMatchesModel(): bool
    {
        $modelValue = is_array($this->model->{$this->name}) ? in_array($this->value, $this->model->{$this->name}) : $this->model->{$this->name};
        return $this->checked ? $modelValue : !$modelValue;
    }

    private function label(bool $useLabel)
    {
        return $useLabel ? \sprintf('<span class="label-text">%s</span>', $this->label ?? text_alt_formatter($this->name)) : '';
    }
}
