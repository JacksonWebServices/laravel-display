<?php 
namespace JWS\Display;

use View;
use Illuminate\Foundation\Exceptions\Handler;

class Formatter
{
    public $data;
    public $type;
    public $errors = array();
    private $nested = false;

    public function __construct($data)
    {
        $this->type = gettype($data);
        $this->data = $this->format($data);
    }

    /**
     *
     * @param $data
     * @return mixed|string
     */
    private function format($data)
    {
        if ($this->check_type($data)) {

            if ($this->type == "string") {
                return json_decode($data);
            } elseif ($this->type == "object" and method_exists($data, "toArray")) {
                return $this->arrayify($data->toArray());
            } else {
                $data = (array) $data;
                return $this->arrayify($data);
            }
        } else {
            return $data;
        }
    }

    /**
     * Recursively loops through objects and arrays and changes
     * nested objects into arrays.
     *
     * @param $data
     * @return array|object
     */
    private function arrayify($data)
    {
        $data = (array) $data;

        $error_messages = array(
            "NumberedKeyError" => "The keys in this array must be numbered and not string keys.",
            "NestedError" => "This array is too nested"
        );

        $new = array();
        $count = 0;
        foreach($data as $key => $value) {
            if (is_string($key) or $key != $count) {
                $this->errors[] = $error_messages["NumberedKeyError"];
            } else {
                $value = (array) $value;
                foreach($value as $k => $v) {
                    $type = gettype($v);
                    if ($type == "array" or $type == "object") {
                        $this->errors[] = $error_messages["NestedError"];
                    } else {
                        $new[$key][$k] = $v;
                    }
                }
            }

            $count++;
        }

        if (!empty($new)) {
            $data = (array) $new;
            return $data;
        } else {
            return (array) $data;
        }
    }


    /**
     * Checks to see if it's a type of variable that we can work with.
     *
     * Only json encoded strings, objects, and arrays are allowed.
     *
     * @param $data
     * @return bool
     */
    private function check_type($data)
    {

        $type = gettype($data);

        // Error messages
        $error_messages = array(
            "TypeError" => "Variable of type {$type} is not allowed.",
            "EmptyError" => "There doesn't seem to be any data."
        );

        // List of disallowed variable types
        $disallowed_types = array(
            "boolean",
            "integer",
            "double",
            "resource",
            "NULL"
        );

        // List of allowed variable types
        $allowed_types = array(
            "string",
            "array",
            "object"
        );

        // Do nothing if the variable is empty.
        if (empty($data)) {
            if (!$this->nested) {
                $this->errors[] = $error_messages["EmptyError"];
            }
            return false;
        }

        // Check if the variable is allowed or not.
        if (in_array($type, $disallowed_types)) {
            if (!$this->nested) {
                $this->errors[] = $error_messages["TypeError"];
            }
            return false;
        } elseif(in_array($type, $allowed_types)) {
            if ($type == "string" and json_decode($data) == null) {
                if (!$this->nested) {
                    $this->errors[] = $error_messages["TypeError"];
                }
                return false;
            } else {
                return true;
            }
        }

        // If we've reached here then just return false.
        return false;
    }


}