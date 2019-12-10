<?php

namespace uzgent\StringUtils;

require_once "AnnotationParser.php";

$stringUtils = new AnnotationParser();
list($warnings, $listeners) = $stringUtils->getListenersFromAnot("@TOLOWER", ["field_name" => "sourceField"], "@TOLOWER", [], [], []);
//Expect a warning because there is no "=" part.
assert(count($warnings) > 0);

list($warnings, $listeners) = $stringUtils->getListenersFromAnot("@TOLOWER=myupper", ["field_name" => "sourceField"], "@TOLOWER", [], [], []);
assert(count($listeners) > 0);

list($warnings, $listeners) = $stringUtils->getListenersFromAnot("@TOLOWER=myupper", ["field_name" => "sourceField"], "@TOLOWER", [], [], []);
assert(count($listeners) > 0);

list($warnings, $listeners) = $stringUtils->getListenersFromAnot("@SUBSTR=mysubstr, 1, 5", ["field_name" => "sourceField"], "@SUBSTR", [], [], ["mysubstr"]);
assert(count($warnings) == 0);
assert(count($listeners) > 0);

list($warnings, $listeners) = $stringUtils->getListenersFromAnot("@SUBSTR=mysubstr, 1, 5", ["field_name" => "sourceField"], "@SUBSTR", [], [], ["wrong"]);

assert(count($warnings) == 1);
assert(count($listeners) == 0);

list($warnings, $listeners) = $stringUtils->getListenersFromAnot("@SUBSTR=mysubstr, 1", ["field_name" => "sourceField"], "@SUBSTR", [], [], ["mysubstr"]);
assert(count($warnings) == 1);
assert(count($listeners) == 0);

list($warnings, $listeners) = $stringUtils->getListenersFromAnot("@RIGHT=mysubstr, 1", ["field_name" => "sourceField"], "@RIGHT", [], [], ["mysubstr"]);
assert(count($warnings) == 0);
assert(count($listeners) == 1);

list($warnings, $listeners) = $stringUtils->getListenersFromAnot("@RIGHT=mysubstr, 1,4", ["field_name" => "sourceField"], "@RIGHT", [], [], ["mysubstr"]);
assert(count($warnings) == 1);
assert(count($listeners) == 0);


list($warnings, $listeners) = $stringUtils->getListenersFromAnot("@LEFT=mysubstr, 1", ["field_name" => "sourceField"], "@LEFT", [], [], ["mysubstr"]);
assert(count($warnings) == 0);
assert(count($listeners) == 1);

list($warnings, $listeners) = $stringUtils->getListenersFromAnot("@LEFT=mysubstr", ["field_name" => "sourceField"], "@LEFT", [], [], ["mysubstr"]);
assert(count($warnings) == 1);
assert(count($listeners) == 0);

list($warnings, $listeners) = $stringUtils->getListenersFromAnot("@CONCAT=str1, str2, str3", ["field_name" => "sourceField"], "@CONCAT", [], [], ["mysubstr"]);
assert(count($warnings) == 3); // One error for each field.
assert(count($listeners) == 0);

list($warnings, $listeners) = $stringUtils->getListenersFromAnot("@CONCAT=str1, str2, str3", ["field_name" => "sourceField"], "@CONCAT", [], [], ["str1", "str2", "str3"]);
assert(count($warnings) == 0);
assert(count($listeners) == 3);

list($warnings, $listeners) = $stringUtils->getListenersFromAnot("@CONCAT=str1, str2, str3 @READONLY", ["field_name" => "sourceField"], "@CONCAT", [], [], ["str1", "str2", "str3"]);
assert(count($warnings) == 0);
assert(count($listeners) == 3);
