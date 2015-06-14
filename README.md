# ICZN-lists

[![Join the chat at https://gitter.im/gbif/iczn-lists](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/gbif/iczn-lists?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
This repository aims to create a valid Darwin Core archive of all names listed in the Official Lists and Indexes of Names in Zoology from the International Commission on Zoological Nomenclature (ICZN). The official source files are available at http://iczn.org/content/official-lists-indexes-1

The dataset has been registered with GBIF here:
http://www.gbif.org/dataset/80b4b440-eaca-4860-aadf-d0dfdd3e856e

## Files

 - **eml.xml** a simple metadata descriptor in the [EML format](https://knb.ecoinformatics.org/#external//emlparser/docs/eml-2.1.1/index.html).
 - **meta.xml** a darwin core archive descriptor file that explains the data file columns
 - **postgres.sql** a SQL schema defining 2 basic tables both sharing the same identifier column. *names* is the parsed result while *raw* contains intermediate parsing results.
 - **names.txt** all list names and their attributes parsed into Darwin Core compliant format together with a plain text version of the entire publication entry
 - **raw.txt** all list names in their raw format (column html) and various intermediate parsing results
 - **opinions.txt** a list of opinions with their references and links out. The same opinion can have multiple entries, i.e. references

## PostgreSQL Schema
Use the sql schema file *postgres.sql* to create a new tables for the names data.

#### import
\copy names from 'names.txt'
\copy raw from 'raw.txt'
\copy opinion from 'opinions.txt'

#### export
\copy (select * from names order by id) to 'names.txt'
\copy (select * from raw order by id) to 'raw.txt'
\copy (select * from opinion order by number) to 'opinions.txt'
