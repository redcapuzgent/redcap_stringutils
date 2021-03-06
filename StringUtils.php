<?php

namespace uzgent\StringUtils;

// Declare your module class, which must extend AbstractExternalModule
use REDCap;

require_once "AnnotationParser.php";

class StringUtils extends \ExternalModules\AbstractExternalModule {

    const annotation = ["@TOLOWER", "@TOUPPER", "@SUBSTR", "@LTRIM", "@RTRIM", "@TRIM", "@STRLEN", "@INDEXOF", "@CONCAT", "@RIGHT", "@LEFT", "@REPLACE"];


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
                    list($warnings, $listeners) = AnnotationParser::getListenersFromAnot($field_annotation, $field, $annotation, $listeners, $warnings, $fieldNames);
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
                echo '<div class="alert">The following field is used but cannot be found: ' . $sourceField . '</div>';
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
}
