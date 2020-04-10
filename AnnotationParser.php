<?php


namespace uzgent\StringUtils;


class AnnotationParser
{
    /**
     * @param $field_annotation string field annotation on the field.
     * @param $field array Information about the field.
     * @param $annotation string The annotation being parsed.
     * @param array $listeners the listeners so far.
     * @param array $warnings the warnings so far.
     * @param array $fieldNames An array of fieldnames that are currently available.
     * @return array list($warnings, $listeners)
     */
    public static function getListenersFromAnot($field_annotation, $field, $annotation, array $listeners, array $warnings, array $fieldNames)
    {
        $selectedPieces = explode("=", $field_annotation);

        if (count($selectedPieces) < 2) {
            $warnings []= "Field annotation is badly formatted " . $field_annotation;
            return [$warnings, $listeners]; //Complain if you can't even provide a proper fieldannotation.
        }
        $sourceField = explode("@", $selectedPieces[1])[0]; // Use @ as separator to avoid reading info of other annotations.
        $destinationField = $field["field_name"];
        switch ($annotation) {
            case "@TOLOWER":
                @$listeners[$sourceField] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $sourceField . '\']").val().toLowerCase()).change();';
                break;
            case "@TOUPPER":
                @$listeners[$sourceField] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $sourceField . '\']").val().toUpperCase()).change();';
                break;
            case "@LTRIM":
                @$listeners[$sourceField] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $sourceField . '\']").val().trimLeft()).change();';
                break;
            case "@RTRIM":
                @$listeners[$sourceField] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $sourceField . '\']").val().trimRight()).change();';
                break;
            case "@TRIM":
                @$listeners[$sourceField] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $sourceField . '\']").val().trim()).change();';
                break;
            case "@STRLEN":
                @$listeners[$sourceField] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $sourceField . '\']").val().length).change();';
                break;
            case "@SUBSTR":
                $selectedFields = explode(",", $sourceField);
                $badfield = false;
                if (count($selectedFields) != 3) {
                    $badfield = true;
                    $warnings [] = 'Substr for '.$sourceField.' is badly formatted.';
                } else {
                    $field0 = trim($selectedFields[0]);
                    $field1 = trim($selectedFields[1]);
                    $field2 = trim($selectedFields[2]);
                    if (!in_array($field0, $fieldNames)) {
                        $warnings [] = 'For field: ' . $destinationField . ' ' . $field0 . ' is not a field.';
                        $badfield  = true;
                    }
                    if (!is_numeric($field1)) {
                        $warnings [] = 'For field: ' . $destinationField . ' ' . $field1 . ' is not numeric';
                        $badfield  = true;
                    }
                    if (!is_numeric($field2)) {
                        $warnings [] = 'For field: ' . $destinationField . ' ' . $field2 . ' is not numeric';
                        $badfield  = true;
                    }
                }
                if (!$badfield){
                    @$listeners[$field0] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $field0 . '\']").val().substr(' . $field1 . ', ' . $field2. ')).change();';
                }
                break;
            case "@RIGHT":
                $selectedFields = explode(",", $sourceField);
                list($badfield, $warnings, $field0, $field1) = self::check2fields($warnings, $fieldNames, $selectedFields, $sourceField, $destinationField);
                if (!$badfield){
                    @$listeners[$selectedFields[0]] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $field0 . '\']").val().substr($("input[name=\'' . $field0 . '\']").val().length - ' .$field1. ', $("input[name=\'' . $field0 . '\']").val().length)).change();';
                }
                break;
            case "@LEFT":
                $selectedFields = explode(",", $sourceField);
                list($badfield, $warnings, $field0, $field1) = self::check2fields($warnings, $fieldNames, $selectedFields, $sourceField, $destinationField);
                if (!$badfield) {
                    @$listeners[$selectedFields[0]] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $field0 . '\']").val().substr(0, '.$field1.')).change();';
                }
                break;
            case "@CONCAT":
                $sourceFieldsConcat = [];
                $explodedSource = explode (",", $sourceField);
                $badField = false;
                foreach($explodedSource as $explodedPiece)
                {
                    $explodedPiece = trim($explodedPiece);
                    if (!in_array($explodedPiece, $fieldNames))
                    {
                        $warnings [] = 'For field: ' . $destinationField . ' ' . $explodedPiece . ' is not a known field.';
                        $badField = true;
                    }
                    $sourceFieldsConcat []= "$(\"input[name='$explodedPiece']\").val()";
                }
                if (!$badField)
                {
                    foreach($explodedSource as $explodedPiece)
                    {
                        $explodedPiece = trim($explodedPiece);
                        @$listeners[$explodedPiece] .= '$("input[name=\'' . $destinationField . '\']").val('.implode("+",$sourceFieldsConcat).').change();';
                    }
                }
                break;
            case "@REPLACE":
                $selectedFields = explode(",", $sourceField);
                $badfield = false;
                if (count($selectedFields) != 3) {
                    $badfield = true;
                    $warnings [] = 'REPLACE for '.$sourceField.' is badly formatted.';
                } else {
                    $field0 = trim($selectedFields[0]); // From
                    $field1 = trim($selectedFields[1]); // What to replace
                    $field2 = trim($selectedFields[2]); // replace with.
                    if (!in_array($field0, $fieldNames)) {
                        $warnings [] = 'For field: ' . $destinationField . ' ' . $field0 . ' is not a field.';
                        $badfield  = true;
                    }
                    if (!in_array($field1, $fieldNames)) {
                        $warnings [] = 'For field: ' . $destinationField . ' ' . $field1 . ' is not a field.';
                        $badfield  = true;
                    }
                    if (!in_array($field2, $fieldNames)) {
                        $warnings [] = 'For field: ' . $destinationField . ' ' . $field2 . ' is not a field.';
                        $badfield  = true;
                    }

                }
                if (!$badfield){
                    @$listeners[$field0] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $field0 . '\']").val().split($("input[name=\'' . $field1 . '\']").val()).join( $("input[name=\'' . $field2. '\']").val())).change();';
                }
                break;
            default:
                break;
        }
        return [$warnings, $listeners];
    }

    /**
     * @param array $warnings
     * @param array $fieldNames
     * @param array $selectedFields
     * @param $sourceField
     * @param $destinationField
     * @return array
     */
    public static function check2fields(array $warnings, array $fieldNames, array $selectedFields, $sourceField, $destinationField)
    {
        $field0 = null;
        $field1 = null;
        $badfield = false;
        if (count($selectedFields) != 2) {
            $badfield = true;
            $warnings [] = 'Instruction for ' . $destinationField . ' is badly formatted.';
        } else {
            $field0 = trim($selectedFields[0]);
            $field1 = trim($selectedFields[1]);
            if (!in_array($field0, $fieldNames)) {
                $badfield = true;
                $warnings [] = 'For field: ' . $destinationField . ' ' . $field0 . ' is not a field.';
            }
            if (!is_numeric($field1)) {
                $badfield = true;
                $warnings [] = 'For field: ' . $destinationField . ' ' . $field1 . ' is not numeric';
            }

        }
        return array($badfield, $warnings, $field0, $field1);
    }
}