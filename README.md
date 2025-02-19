# StringUtils
> [!CAUTION]
> This module has been deprecated since the introduction of special text functions in REDCap 10.5.0.

## Purpose
This module provides all kinds of stringutil functions.

## How to use the StringUtils external module
### What is it for?
This plugin allows you to manipulate text fields using action tags.
### Limitations
- The field using the StringUtils action tag MUST be on the same instrument as the source field.
- A field containing the result of a StringUtils function CANNOT be used as a source field.
- By default, the resulting value in a field using the StringUtils action tag can be manually overwritten. If this is not desirable, it is recommended to use the @READONLY action tag.

### Using the external module
Create a field with one of the following action tags:
| Action tag | Usage |
| ---------- | ----- |
| @TOLOWER=sourcefieldnamehere | Converts the field into its lowercase equivalent. |
| @TOUPPER=sourcefieldnamehere | Converts the field into its uppercase equivalent. |
| @STRLEN=sourcefieldnamehere | Calculates the length of a field. |
| @TRIM=sourcefieldnamehere | Trims the text of a field of whitespaces. |
| @LTRIM=sourcefieldnamehere | Trims the left side of text of a field of whitespaces. |
| @SUBSTR=sourcefieldnamehere,start,length | Takes a part of a string from `start`, returns the specified number of characters (`length`). `start` and `length` must be valid integers. |
| @LEFT=sourcefieldnamehere,length | Returns the `length` number of characters from the start of a text string. |
| @RIGHT=sourcefieldnamehere,length | Returns the `length` number of characters from the end of a text string. |
| @CONCAT=field1, field2, field3, ... | Concatenates other **text** fields into one field. The fields must be valid field names. The list can contain any amount of fields. |
| @REPLACE=sourceDataField, SearchTextField, ReplaceByTextField | Searches in the `sourceDataField` for a specified value defined in `SearchTextField` and returns a new string where **all** the specified values in `ReplaceByTextField` are replaced. All parameters are field names. The fields must be valid field names. |
