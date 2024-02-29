<?php

namespace Fckin\core\form;

use Fckin\core\db\Model;

class Form
{
    public static function begin($action, $method = 'post', $otherFormAttributes = [])
    {
        $formAttributes = self::buildFormAttributes($otherFormAttributes);

        echo "<form action=\"$action\" method=\"$method\" $formAttributes>";
        return new Form();
    }

    public static function end()
    {
        echo '</form>';
    }

    private static function buildFormAttributes($attributes)
    {
        $attributeString = '';

        foreach ($attributes as $key => $value) {
            $attributeString .= " $key=\"$value\"";
        }

        return $attributeString;
    }

    public static function input(Model $model, string $type, string $name, string|null $label = null, bool $useLabel = true, string|null $placeholder = null, string|array $classes = '')
    {
        echo new Input($model, $type, $name, $label, $useLabel, $placeholder, $classes);
    }

    public static function textarea(Model $model, string $name, string|null $label = null, bool $useLabel = true, string|null $placeholder = null, string $classes = '')
    {
        echo new Textarea($model, $name, $label, $useLabel, $placeholder, $classes);
    }

    public static function checkbox(Model $model, string $name, mixed $value, bool $checked = false, string|null $label = null, bool $useLabel = true)
    {
        echo new Checkbox($model, $name, $value, $checked, $label, $useLabel);
    }

    public static function select(Model $model, string $name, array $options = [], string|null $label = null, string|array $classes = '')
    {
        echo new Select($model, $name, $options, $label, $classes);
    }

    public static function radio(Model $model, string $name, bool $checked = false, string|null $label = null)
    {
        echo new Radio($model, $name, $checked, $label);
    }

    public static function fileinput(Model $model, string $name, string $classes = '')
    {
        echo new FileInput($model, $name, $classes);
    }

    public static function range(Model $model, string $name, string|null $label = null, int $min = 0, int $max = 100, int $step = 1, string $classes = '')
    {
        echo new Range($model, $name, $label, $min, $max, $step, $classes);
    }

    public static function rating(Model $model, string $name, int $stars, string $ratingType = 'star', string $classes = '')
    {
        echo new Rating($model, $name, $stars, $ratingType, $classes);
    }

    public static function submit(string $text, string $class = 'btn btn-outline btn-block my-3')
    {
        echo sprintf('<button class="%s" type="submit">%s</button>', \twMerge('btn btn-outline btn-block my-3', $class), $text);
    }
}
