<?php 
namespace JWS\Display;

use Illuminate\Support\Facades\View;

class Display
{
    static public $data;
    static public $type;
    static public $errors;

    static public $args;


    public static function __callStatic($method, $args)
    {
        $args = $args[0];
        $string = str_replace('view', '', $method);
        $method = Blade::fromCamelCase($string);

        // Top Level processing of the arguments array
        $args["data"] = (array) self::format($args["data"]);
        $args["blade"] = Blade::blades($args["blade"], $method);
        $args["viewType"] = $method;

        return in_array($method, Blade::types()) ? self::view($args) : false;
    }


    public static function format($data)
    {
        $formatter = new Formatter($data);

        $class = new \stdClass;
        $class->data = $formatter->data;
        $class->type = $formatter->type;
        $class->errors = $formatter->errors;
        return $class;
    }

    public static function view($input)
    {
        /**
         * View Type Specific Processing
         */
        if ($input["viewType"] == "table") {
            $input["table"] = self::tableData($input);
        } elseif ($input["viewType"] == "pie_chart") {
            $input["pie_chart"] = self::pieChartData($input);
        } elseif ($input["viewType"] == "docs") {
            $input = self::docsData($input);
        }

        return View::make($input["blade"]["base"])->with("input", $input);
    }

    public static function tableData($input)
    {
        if (!empty($input["data"]["errors"])) {
            return $input["table"];
        }

        $data = $input["data"]["data"];
        $newData = array();
        foreach ($data as $value) {
            $tempData = array();
            foreach ($input["table"]["td"] as $v) {
                $tempData[] = $value[$v];
            }
            $newData[] = $tempData;
        }
        $input["table"]["td"] = $newData;
        return $input["table"];
    }

    public static function pieChartData($input)
    {
        $input["pie_chart"]["id"] = self::generateRandomString(5);

        $labels = "[";
        $numbers = "[";
        foreach ($input["data"]["data"] as $data) {
            $label = $data[$input["pie_chart"]["label"]];
            $number = (double) str_replace(",", "", $data[$input["pie_chart"]["number"]]);
            $labels .= "\"{$label}\", ";
            $numbers .= "{$number}, ";
        }

        $labels = trim(trim($labels), ",") . "]";
        $numbers = trim(trim($numbers), ",") . "]";

        $input["pie_chart"]["label"] = $labels;
        $input["pie_chart"]["number"] = $numbers;

        return $input["pie_chart"];
    }

    public static function docsData($input)
    {
        if (!empty($input["data"]["errors"])) {
            $input["data"]["errors"] = array();
        }

        $docs_path = base_path('resources/docs/');

        try {
            $file = file_get_contents($docs_path . $input["docs"] . ".md", true);
        } catch (\ErrorException $e) {
            $file = "";
            array_push($input["data"]["errors"], "File is most likely missing!");
        }

        $input["data"]["data"] = \Markdown::convertToHtml($file);

        return $input;
    }

    /**
     * Make an array of all the titles
     *
     * @param $positions
     * @return array
     */
    public static function titles($positions)
    {
        $titles = array();

        foreach ($positions as $key => $value) {
            if (empty($titles) or !key_exists($key, $titles)) {
                $titles[] = $key;
            }
        }

        return $titles;
    }

    private static function generateRandomString($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}