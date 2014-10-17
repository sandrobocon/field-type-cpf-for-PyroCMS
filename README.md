Field-Type-Codice-Fiscale
=========================

CPF (Cadastro de Pessoa Fisica) Field Type for PyroCMS

## How to install

Download the cpf field type and place it in addons/shared_addons/field_types or addons/[site_ref]/field_types and make sure that the folder you will copy is named "cpf" without quotes.

## How it works

This FieldType will check if the input is 11 numeric long (removing '.', '-' and '/' automatically) and if it is a valid format for an brazilian CPF, make sure to set the field type as unique, if necessary, when using it via Streams module or with Streams API.
It will save as bigint, but accept and will return in CPF format '000.000.000-00'

Based on (https://github.com/ChristianGiupponi/Field-Type-Codice-Fiscale)