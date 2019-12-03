<?php

namespace uzgent\StringUtils;

// Declare your module class, which must extend AbstractExternalModule
use REDCap;


class StringUtils extends \ExternalModules\AbstractExternalModule {

    const annotation = ["@TOLOWER", "@TOUPPER", "@SUBSTR", "@LTRIM", "@RTRIM", "@TRIM", "@STRLEN", "@INDEXOF", "@CONCAT", "@RIGHT", "@LEFT"];


    public function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance)
    {
        $listeners = [];
        $warnings = [];
        $fieldNames = $this->getFieldNames($project_id);

        foreach ($this->getMetadata($project_id) as $field) {
            $field_annotation = $field['field_annotation'];
            foreach (self::annotation as $annotation)
            {
                if (strpos($field_annotation, $annotation) !== false) {
                    list($warnings, $listeners) = $this->getListenersFromAnot($field_annotation, $field, $annotation, $listeners, $warnings, $fieldNames);
                }
            }
        }
        $this->showWarnings($listeners, $fieldNames, $warnings);
        $this->writeListeners($listeners);

    }
    public function redcap_survey_page($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance)
    {
            $this->redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance);

    }

    /**
     * @param $project_id
     * @param array $fieldNames
     * @return array
     */
    public function getFieldNames($project_id)
    {
        $fieldNames = [];
        foreach ($this->getMetadata($project_id) as $field) {
            $fieldNames [] = $field["field_name"];
        }
        return $fieldNames;
    }

    /**
     * @param array $listeners
     * @param array $fieldNames
     * @param array $warnings
     */
    public function showWarnings(array $listeners, array $fieldNames, array $warnings)
    {
        foreach ($listeners as $sourceField => $code) {
            if (!in_array($sourceField, $fieldNames))  {
                echo '<div class="alert">The following field is used by cannot be found: ' . $sourceField . '</div>';
            }
        }
        foreach ($warnings as $warning) {
            echo '<div class="alert">' . $warning . '</div>';
        }
    }

    /**
     * @param array $listeners
     */
    public function writeListeners(array $listeners)
    {
        echo '<script>';
        foreach ($listeners as $sourceField => $code) {
            echo '$("input[name=\'' . $sourceField . '\']").keyup(function(){';
            echo $code;
            echo '} );';
        }
        echo '</script>';
    }

    /**
     * @param $field_annotation
     * @param $field
     * @param $annotation
     * @param array $listeners
     * @param array $warnings
     * @param array $fieldNames
     * @return array
     */
    public function getListenersFromAnot($field_annotation, $field, $annotation, array $listeners, array $warnings, array $fieldNames)
    {
        $selectedPieces = explode("=", $field_annotation);
        $sourceField = explode(" ", $selectedPieces[1])[0];
        $destinationField = $field["field_name"];
        switch ($annotation) {
            case "@TOLOWER":
                $listeners[$sourceField] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $sourceField . '\']").val().toLowerCase());';
                break;
            case "@TOUPPER":
                $listeners[$sourceField] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $sourceField . '\']").val().toUpperCase());';
                break;
            case "@LTRIM":
                $listeners[$sourceField] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $sourceField . '\']").val().trimLeft());';
                break;
            case "@RTRIM":
                $listeners[$sourceField] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $sourceField . '\']").val().trimRight());';
                break;
            case "@TRIM":
                $listeners[$sourceField] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $sourceField . '\']").val().trim());';
                break;
            case "@STRLEN":
                $listeners[$sourceField] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $sourceField . '\']").val().length);';
                break;
            case "@SUBSTR":
                $selectedFields = explode(",", $sourceField);
                if (!is_numeric($selectedFields[1])) {
                    $warnings [] = 'For field: ' . $destinationField . ' ' . $selectedFields[1] . ' is not numeric';
                }
                if (!is_numeric($selectedFields[2])) {
                    $warnings [] = 'For field: ' . $destinationField . ' ' . $selectedFields[2] . ' is not numeric';
                }
                if (!in_array($selectedFields[0], $fieldNames)) {
                    $warnings [] = 'For field: ' . $destinationField . ' ' . $selectedFields[0] . ' is not a field.';
                }
                $listeners[$selectedFields[0]] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $selectedFields[0] . '\']").val().substr(' . $selectedFields[1] . ', ' . $selectedFields[2] . '));';
                break;
            case "@RIGHT":
                $selectedFields = explode(",", $sourceField);
                if (!is_numeric($selectedFields[1])) {
                    $warnings [] = 'For field: ' . $destinationField . ' ' . $selectedFields[1] . ' is not numeric';
                }
                if (!in_array($selectedFields[0], $fieldNames)) {
                    $warnings [] = 'For field: ' . $destinationField . ' ' . $selectedFields[0] . ' is not a field.';
                }
                $listeners[$selectedFields[0]] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $selectedFields[0] . '\']").val().substr($("input[name=\'' . $selectedFields[0] . '\']").val().length - ' . $selectedFields[1] . ', $("input[name=\'' . $selectedFields[0] . '\']").val().length));';
                break;
            case "@LEFT":
                $selectedFields = explode(",", $sourceField);
                if (!is_numeric($selectedFields[1])) {
                    $warnings [] = 'For field: ' . $destinationField . ' ' . $selectedFields[1] . ' is not numeric';
                }
                if (!in_array($selectedFields[0], $fieldNames)) {
                    $warnings [] = 'For field: ' . $destinationField . ' ' . $selectedFields[0] . ' is not a field.';
                }
                $listeners[$selectedFields[0]] .= '$("input[name=\'' . $destinationField . '\']").val($("input[name=\'' . $selectedFields[0] . '\']").val().substr(0, '.$selectedFields[1].'));';
                break;
            case "@CONCAT":
                $sourceFieldsConcat = [];
                $explodedSource = explode (",", $sourceField);
                foreach($explodedSource as $explodedPiece)
                    {
                        $sourceFieldsConcat []= "$(\"input[name='$explodedPiece']\").val()";
                    }
                foreach($explodedSource as $explodedPiece)
                {
                    $listeners[$explodedPiece] .= '$("input[name=\'' . $destinationField . '\']").val('.implode("+",$sourceFieldsConcat).');';
                }
                break;
            default:
                break;
        }
        return [$warnings, $listeners];
    }

}
