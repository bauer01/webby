<?php

namespace Webby\Latte;

use Latte\Compiler;
use Latte\Macros\MacroSet;
use Nette\Bridges\FormsLatte\FormMacros;

class Macros extends MacroSet
{

    public static function install(Compiler $compiler)
    {
        $macroSet = new static($compiler);

        // Form macros
        FormMacros::install($compiler);


        // {container structure => [ .. ], wrap => [ .. ], size => ..}
        $macroSet->addMacro(
            'container',
            static::class . '::renderMacroContainerStart(%node.word, %node.array)',
            static::class . '::renderMacroContainerEnd(%node.word, %node.array)'
        );

        $macroLink = 'echo $presenter->link(%node.word, %node.array);';

        // n:href="link:to:page param1 => value, param2 => value"
        $macroSet->addMacro('href', null, null, ' ?> href="<?php ' . $macroLink . ' ?>"<?php ');

        // {link link:to:page param1 => value, param2 => value}
        $macroSet->addMacro('link', $macroLink);
    }

    private static function mergeStructureWithElement($type, array $args)
    {
        $element = [];
        if (!empty($args["structure"][$type])) {
            $element = $args["structure"][$type];
        }

        if (!empty($args["element"]["class"])) {

            if (!empty($element["class"])) {
                $element["class"] .= " " . $args["element"]["class"];
            } else {
                $element["class"] = $args["element"]["class"];
            }
        }

        if (!empty($args["element"]["tag"])) {
            $element["tag"] = $args["element"]["tag"];
        }

        if (!empty($args["element"]["background"])) {

            if (!empty($element["background"])) {
                $element["background"] = array_merge($element["background"], $args["element"]["background"]);
            } else {
                $element["background"] = $args["element"]["background"];
            }
        }

        if (!empty($args["element"]["size"])) {
            if (empty($element["size"])) {
                $element["size"] = $args["element"]["size"];
            } else {
                $element["size"] = array_merge($element["size"], $args["element"]["size"]);
            }
        }

        if (!empty($args["element"]["attributes"])) {

            if (empty($element["attributes"])) {
                $element["attributes"] = $args["element"]["attributes"];
            } else {
                $element["attributes"] = array_merge($element["attributes"], $args["element"]["attributes"]);
            }
        }

        $wrapOuter = [];
        if (!empty($args["wrap"]["outer"])) {
            $wrapOuter = $args["wrap"]["outer"];
        }

        $wrapInner = [];
        if (!empty($args["wrap"]["inner"])) {
            $wrapInner = $args["wrap"]["inner"];
        }

        return array_merge($wrapOuter, [$element], $wrapInner);
    }

    public static function renderMacroContainerStart($type, array $args)
    {
        foreach (self::mergeStructureWithElement($type, $args) as $options) {

            if (!empty($options["tag"])) {

                echo "<" . $options["tag"];

                // Attributes
                if (!empty($options['attributes'])) {
                    foreach ($options['attributes'] as $attrName => $attrValue) {
                        echo " " . $attrName;
                        if ($attrValue !== null) {
                            echo '="' . $attrValue . '"';
                        }
                    }
                }

                // Classes
                $classes = [];
                if (!empty($options['class'])) {
                    $classes = array_merge(
                        $classes,
                        explode(" ", $options["class"])
                    );
                }

                // Size class
                if (!empty($options['size']['value'])) {

                    $sizeClass = "";
                    if (!empty($options['size']['class'])) {
                        $sizeClass .= $options['size']['class'];
                    }
                    if (!empty($options['size']['delimiter'])) {
                        $sizeClass .= $options['size']['delimiter'];
                    }
                    if ($sizeClass) {
                        $classes[] = $sizeClass . $options['size']['value'];
                    }
                }

                if (!empty($classes)) {
                    echo ' class="' . implode(" ", $classes) . '"';
                }

                // Background
                if (!empty($options["background"])) {

                    $style = "";
                    foreach ($options["background"] as $key => $value) {
                        $style .= 'background-' . $key . ': ' . $value . ";";
                    }
                    echo ' style="' . $style . '"';
                }

                echo ">";
            }
        }
    }

    public static function renderMacroContainerEnd($type, array $args)
    {
        foreach (self::mergeStructureWithElement($type, $args) as $options) {

            if (!empty($options["tag"])) {
                echo "</" . $options["tag"] . ">";
            }
        }
    }

}