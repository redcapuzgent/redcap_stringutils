<?php

namespace uzgent\StringUtils;

// Declare your module class, which must extend AbstractExternalModule
use REDCap;


class StringUtils extends \ExternalModules\AbstractExternalModule {

    const annotation = ["@TOLOWER", "@TOUPPER", "@SUBSTR", "@MD5", "@LTRIM", "@RTRIM", "@TRIM", "@STRLEN", "@STRPOS"];
    public function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance)
    {
        $fieldValues = [];
        $currentFields = [];
        $values = $this->getData($project_id, $record);
        foreach($values as $event => $eventVars)
        {
            foreach($eventVars as $eventVar)
            {
                $currentFields = array_merge($eventVar, $currentFields);
            }
        }
        foreach ($this->getMetadata($project_id) as $field) {
            $field_annotation = $field['field_annotation'];
            foreach (self::annotation as $annotation)
            {
                if (strpos($field_annotation, $annotation) !== false) {
                    $selectedPieces = explode("=", $field_annotation);
                    $selectedField = explode(" ", $selectedPieces[1])[0];

                    switch($annotation)
                    {
                            case "@TOLOWER":
                                $fieldValues[$field["field_name"]] = mb_strtolower($currentFields[$selectedField]);
                                break;
                            case "@TOUPPER":
                                $fieldValues[$field["field_name"]] = mb_strtoupper($currentFields[$selectedField]);
                                break;
                            case "@SUBSTR":
                                break;
                            case "@MD5":
                                $fieldValues[$field["field_name"]] = md5($currentFields[$selectedField]);
                                break;
                            case "@LTRIM":
                                $fieldValues[$field["field_name"]] = ltrim($currentFields[$selectedField]);
                                break;
                            case "@RTRIM":
                                $fieldValues[$field["field_name"]] = rtrim($currentFields[$selectedField]);
                                break;
                            case "@TRIM":
                                $fieldValues[$field["field_name"]] = trim($currentFields[$selectedField]);
                                break;
                            case "@STRLEN":
                                $fieldValues[$field["field_name"]] = strlen($currentFields[$selectedField]);
                                break;
                            case "@STRPOS":
                                break;
                        default: break;

                    }

                }
            }
        }
        echo '<script>';
        foreach ($fieldValues as $fieldName => $fieldValue)
        {
            echo '$("input[name=\''.$fieldName.'\']").val("'.$fieldValue.'");';
        }
        echo '</script>';

    }
    public function redcap_survey_page($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance)
    {
            $this->redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance);

    }

}
