<?php 
namespace JWS\Display;

class Blade
{

    static $viewPath;
    static $baseBlades;

    public static function blades($blades, $type)
    {
        if (gettype($blades) == "string") {
            $bladeTypes = array("base", "body", "title", "errors");

            $blades = explode(".", $blades);

            count($blades);
            $count = 0;
            $a = array();
            while ($count < count($blades)) {
                $a[$bladeTypes[$count]] = $blades[$count];
                $count++;
            }

            $blades = $a;
        }

        if (array_key_exists("title", $blades) and $blades["title"] == "false") {
            $title = false;
        } else {
            $baseTitle = "display.partials." . $blades["base"] . ".";
            $title = array_key_exists("title", $blades) ? $baseTitle . $blades["title"] : $baseTitle . "title";
        }

        $baseBody = "display." . $type . ".";
        $body = array_key_exists("body", $blades) ? $baseBody . $blades["body"] : $baseBody . "basic";

        $errorsDir = "display.partials.errors.";
        $errors = array_key_exists("errors", $blades) ? $errorsDir . $blades["errors"] : $errorsDir . "basic";

        return array(
            "base" => "display." . $blades["base"],
            "body" => $body,
            "title" => $title,
            "errors" => $errors
        );
    }

    public static function types()
    {
        $viewPath = base_path('resources/views/display/');
        return self::getDirectories($viewPath, ["partials"]);
    }

    private static function getDirectories($path, $excludes = array())
    {
        $contents = scandir($path);

        array_push($excludes, ".", "..");

        $directories = array();
        foreach ($contents as $content) {
            if (!in_array($content, $excludes) and is_dir($path . $content)) {
                $directories[] = $content;
            }
        }

        return $directories;
    }

    public static function fromCamelCase($string)
    {
        $string[0] = strtolower($string[0]);
        $function = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $function, $string);
    }
}